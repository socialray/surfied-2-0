<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
* Hooking activity updates recording (regular or group ones)
* to store geodata if user allowed us to do so..
*/
add_action( 'bp_activity_posted_update', 'bp_checkins_record_geoloc_meta', 9, 3 );
add_action( 'bp_groups_posted_update', 'bp_checkins_record_group_geoloc_meta', 9, 4);

?>