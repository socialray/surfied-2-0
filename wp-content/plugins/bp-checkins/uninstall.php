<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Deleting options
delete_option( 'bp-checkins-version' );
delete_option( 'bp-checkins-disable-activity-checkins' );
delete_option( 'bp-checkins-disable-geo-friends' );
delete_option( 'bp-checkins-activate-component' );
delete_option( 'bp-checkins-enable-image-uploads' );
delete_option( 'bp-checkins-enable-place-uploads' );
delete_option( 'bp-checkins-enable-comment-image-uploads' );
delete_option( 'bp-checkins-enable-box-checkedin-friends' );
delete_option( 'bp-checkins-live-places-timer' );
delete_option( 'bp-checkins-disable-timer' );
delete_option( 'bp-checkins-max-upload-size' );
delete_option( 'bp-checkins-deactivate-foursquare' );
delete_option( 'bp-checkins-max-width-image' );
delete_option( 'foursquare-client-id' );
delete_option( 'foursquare-client-secret' );
delete_option( 'foursquare-cron-schedule' );
delete_option( 'foursquare-user-import' );

//deleting tables
global $wpdb;

$fs_table = $wpdb->base_prefix . 'bp_checkins_foursquare_logs';
$category_meta_table = $wpdb->base_prefix . 'places_categorymeta';

$wpdb->query( "DROP TABLE IF EXISTS `$fs_table`" );
$wpdb->query( "DROP TABLE IF EXISTS `$category_meta_table`" );

// deleting places !
$places_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_type = %s", 'places' ) );

foreach( $places_id as $place ){
	wp_delete_post( $place, true );
}
/* leaving taxonomy places_category because no function to delete (future release of WP).. i don't want to put a mess ! */
/* see http://core.trac.wordpress.org/ticket/12629 */

//deleting users meta
delete_metadata( 'user', false, 'foursquare_token', '', true );
delete_metadata( 'user', false, 'foursquare_latest_timestamp', '', true );
delete_metadata( 'user', false, 'bpci_latest_lat', '', true );
delete_metadata( 'user', false, 'bpci_latest_lng', '', true );
delete_metadata( 'user', false, 'bpci_latest_address', '', true );

//deleting groupmetas
if( function_exists( 'bp_init' ) ) {

	$group_ids = $wpdb->get_col("SELECT id FROM {$wpdb->base_prefix}bp_groups");

	if( count( $group_ids ) >= 1) {
		foreach( $group_ids as $group_id ){
			groups_delete_groupmeta( $group_id, 'checkins_ok');
		}
	}

}
?>