<?php

class Strava_LatestRideWidget extends WP_Widget {

	const STRAVA_V1_API = 'http://www.strava.com/api/v1/'; //rides?athleteId=134698
	const STRAVA_V2_API = 'http://www.strava.com/api/v2/'; //rides/:ride_id/map_details

	private $ch;
	
	public function __construct() {
		parent::__construct(
	 		false,
			'Strava Latest Ride', // Name
			array( 'description' => __( 'Strava latest ride using static google map image', 'strava' ), ) // Args
		);
	}
	
	public function form( $instance ) {
		// outputs the options form on admin
        $strava_email = isset( $instance['strava_email'] ) ? esc_attr( $instance['strava_email'] ) : '';
        $strava_pass = isset( $instance['strava_pass'] ) ? esc_attr( $instance['strava_pass'] ) : '';
        $auth_token = isset( $instance['auth_token'] ) ? esc_attr( $instance['auth_token'] ) : '';
        $distance_min = isset( $instance['distance_min'] ) ? esc_attr( $instance['distance_min'] ) : '';
        $ride_index_params = isset( $instance['ride_index_params'] ) ? esc_attr( $instance['ride_index_params'] ) : '';

		//provide some defaults
        //$ride_index_params = $ride_index_params ? $ride_index_params : 'athleteId=21';

		if ( ! $auth_token ):
		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'strava_email' ); ?>"><?php _e( 'Strava Email:' ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'strava_email' ); ?>" name="<?php echo $this->get_field_name( 'strava_email' ); ?>" type="text" value="<?php echo $strava_email; ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'strava_pass' ); ?>"><?php _e( 'Strava Password:' ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'strava_pass' ); ?>" name="<?php echo $this->get_field_name( 'strava_pass' ); ?>" type="password" value="<?php echo $strava_pass; ?>" />
        </p>
		<?php
		else:
		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'auth_token' ); ?>"><?php _e( 'Auth Token:' ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'auth_token' ); ?>" name="<?php echo $this->get_field_name( 'auth_token' ); ?>" type="text" value="<?php echo $auth_token; ?>" />
        </p>
		<?php
		endif;
		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'distance_min' ); ?>"><?php _e( 'Min. Distance:' ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'distance_min' ); ?>" name="<?php echo $this->get_field_name( 'distance_min' ); ?>" type="text" value="<?php echo $distance_min; ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'ride_index_params' ); ?>"><?php _e( 'Ride Search Parameters (one per line): ' ); ?>
			<a href="https://stravasite-main.pbworks.com/w/page/51754146/Strava%20REST%20API%20Method%3A%20rides%20index" target="_blank"><?php _e( 'help' ); ?></a></label>
			<textarea name="<?php echo $this->get_field_name( 'ride_index_params' ); ?>" id="<?php echo $this->get_field_id( 'ride_index_params' ); ?>" cols="10" rows="5" class="widefat"><?php echo $ride_index_params; ?></textarea>
        </p>
        <?php		
	}
	
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved from the admin
		$instance = $old_instance;
		$instance['ride_index_params'] = strip_tags( $new_instance['ride_index_params'] );
		$instance['distance_min'] = strip_tags( $new_instance['distance_min'] );

		if ( ! empty( $new_instance['strava_email'] ) && ! empty( $new_instance['strava_pass'] ) ) {
			$auth = $this->getAuth( $new_instance['strava_email'], $new_instance['strava_pass'] );
			if ( isset( $auth->token ) ) {
				$instance['auth_token'] = $auth->token;
				if ( empty( $instance['ride_index_params'] ) ) {
					$instance['ride_index_params'] = "athleteId={$auth->athlete->id}";
				}
			} else {
				//print an error about password
				$instance['strava_email'] = strip_tags( $new_instance['strava_email'] );
			}
			
		}

		//$instance['athlete_hash'] = strip_tags( $new_instance['athlete_hash'] );

        return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$ride_index_params = $instance['ride_index_params'];
		$distance_min = $instance['distance_min'];

		//die(print_r($ride_index_params, true));
		$rides = $this->getRides( $ride_index_params );

		if ( ! empty( $rides ) ):

			if ( ! empty( $distance_min ) )
				$rides = $this->getRidesLongerThan( $rides, $distance_min );
		
			$ride = current($rides);
			
			echo $before_widget;
			echo '<pre>';
			print_r($ride);
			echo '</pre>';
			echo $after_widget;
		endif;
	}
	
	private function initCurl() {
		if ( ! $this->ch ) {
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
		}
		//set to GET by default
		curl_setopt($this->ch, CURLOPT_POST, false);
	}

	private function getRides( $params ) {
		$data = $this->getCurlJson( self::STRAVA_V1_API . 'rides?' . implode( '&', explode( "\n", $params ) ) );
		if ( isset( $data->rides ) )
			return $data->rides;
		return array();
	}

	private function getRideInfo( $ride_id ) {
		return $this->getCurlJson( self::STRAVA_V2_API . "rides/{$ride_id}" );
	}

	private function milesToMeters( $miles ) {
		return $miles * 1609.344;
	}
	
	private function getRidesLongerThan( $rides, $miles ) {
		$this->initCurl();
		$meters = $this->milesToMeters( $miles );
		$long_rides = array();
		foreach ( $rides as $ride ) {
			$ride_info = $this->getRideInfo( $ride->id );
			if ( $ride_info->ride->distance > $meters ) {
				$long_rides[] = $ride_info;
			}
		}
		return $long_rides;
	}
	
	private function getAuth( $email, $password ) {
		$this->initCurl();
		curl_setopt($this->ch, CURLOPT_URL, self::STRAVA_V2_API . 'authentication/login' );
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, array( 'email' => $email, 'password' => $password ) );
		$json = curl_exec( $this->ch );
		$data = json_decode( $json );
		if ( isset( $data->token ) )
			return $data;
		return new stdClass();
	}

	private function getCurlJson( $url ) {
		$this->initCurl();
		curl_setopt($this->ch, CURLOPT_URL, $url );
		$json = curl_exec( $this->ch );
		$data = json_decode( $json );
		return $data;
	}
	
	private function getMapDetails( $ride_id, $token ) {
		//currently broken @strava
		$data = $this->getCurlJson( self::STRAVA_V2_API . "rides/{$ride_id}/map_details?token={$token}" );
		die(print_r($data, true));
	}
}