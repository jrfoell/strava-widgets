<?php

abstract class Strava_BaseWidget extends WP_Widget {
	
	public function form( $instance ) {
		// outputs the options form on admin
        $athlete_number = isset( $instance['athlete_number'] ) ? esc_attr( $instance['athlete_number'] ) : '';
        $athlete_hash = isset( $instance['athlete_hash'] ) ? esc_attr( $instance['athlete_hash'] ) : '';

		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'athlete_number' ); ?>"><?php _e( 'Athlete Number:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'athlete_number' ); ?>" name="<?php echo $this->get_field_name( 'athlete_number' ); ?>" type="text" value="<?php echo $athlete_number; ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'athlete_hash' ); ?>"><?php _e( 'Athlete Hash:' ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'athlete_hash' ); ?>" name="<?php echo $this->get_field_name( 'athlete_hash' ); ?>" type="text" value="<?php echo $athlete_hash; ?>" />
        </p>
        <?php		
	}
	
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved from the admin
		$instance = $old_instance;
		$instance['athlete_number'] = strip_tags( $new_instance['athlete_number'] );
		$instance['athlete_hash'] = strip_tags( $new_instance['athlete_hash'] );
        return $instance;
	}
	
}

