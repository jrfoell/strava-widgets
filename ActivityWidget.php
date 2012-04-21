<?php

require_once STRAVA_BASE_DIR . 'BaseWidget.php';

class Strava_ActivityWidget extends Strava_BaseWidget {

	public function __construct() {
		parent::__construct(
	 		false, 
			'Strava Activity', // Name
			array( 'description' => __( 'Strava Activity Widget 300x454', 'strava' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$athlete_number = $instance['athlete_number'];
		$athlete_hash = $instance['athlete_hash'];

		if ( $athlete_number && $athlete_hash ):
		echo $before_widget;
        ?>
		<iframe height='454' width='300' frameborder='0' allowtransparency='true' scrolling='no'
			 src='http://app.strava.com/athletes/<?php echo $athlete_number ?>/latest-rides/<?php echo $athlete_hash ?>'></iframe>
        <?php
		echo $after_widget;
		endif;
	}
}