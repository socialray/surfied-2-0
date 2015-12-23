<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function bp_checkins_foursquare_cron_job() {
	
	if( !bp_checkins_is_foursquare_ready() )
		return false;
		
	$limit = @ini_get('max_execution_time');
	set_time_limit(900);
	ini_set('max_execution_time', 900);
	
	$checkins_cron = new Bp_Checkins_Foursquare_Import;

	$checkins_cron->wpcron_import();
	
	set_time_limit( intval($limit) );
	ini_set('max_execution_time', intval($limit) );
	
}

add_action('bp_checkins_foursquare_syncing', 'bp_checkins_foursquare_cron_job');