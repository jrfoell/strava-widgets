<?php
/*
Plugin Name: Strava Widgets
Description: Widgets for displaying info from strava.com
Author: justin@foell.org
Version: 1.0
Author URI: http://www.foell.org/justin
*/

define( 'STRAVA_BASE_DIR', dirname( __FILE__ ) . '/' );


class Strava_Widgets {

	public function onWidgetInit() {
		require_once STRAVA_BASE_DIR . 'ActivityWidget.php';
		require_once STRAVA_BASE_DIR . 'SummaryWidget.php';
		require_once STRAVA_BASE_DIR . 'LatestRideWidget.php';

		register_widget( 'Strava_ActivityWidget' );
		register_widget( 'Strava_SummaryWidget' );
		register_widget( 'Strava_LatestRideWidget' );
	}
	
}

$strava_widgets = new Strava_Widgets();

add_action( 'widgets_init', array( $strava_widgets, 'onWidgetInit' ) );
