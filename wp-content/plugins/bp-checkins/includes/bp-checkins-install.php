<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* create a log table for foursquare imports and a table to handle places category meta */

function bp_checkins_install_db() {
	global $wpdb;
	
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		
	$sql = false;
		
	if( $wpdb->get_var( "SHOW TABLES LIKE {$wpdb->base_prefix}bp_checkins_foursquare_logs" ) != "{$wpdb->base_prefix}bp_checkins_foursquare_logs" ) {
		
		$sql[] = "CREATE TABLE {$wpdb->base_prefix}bp_checkins_foursquare_logs (
		          id bigint(20) NOT NULL AUTO_INCREMENT,
		  		  user_id bigint(20) NOT NULL,
				  type varchar(255) DEFAULT NULL,
				  log longtext,
		  		  PRIMARY KEY (id),
				  KEY user_id (user_id),
				  KEY type (type)
		          ){$charset_collate};";
		
	}
		
	if( $wpdb->get_var( "SHOW TABLES LIKE {$wpdb->base_prefix}places_categorymeta" ) != "{$wpdb->base_prefix}places_categorymeta" ) {
		
		$sql[] = "CREATE TABLE {$wpdb->base_prefix}places_categorymeta (
			   	  meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			  	  places_category_id bigint(20) NOT NULL DEFAULT 0,
			   	  meta_key varchar(255) DEFAULT NULL,
				  meta_value longtext,
				  PRIMARY KEY (meta_id),
				  KEY places_category_id (places_category_id),
				  KEY meta_key (meta_key)
				  ){$charset_collate};";
		
	}

	if( !empty( $sql ) ) {
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		/* set some default options */
		update_option('bp-checkins-disable-activity-checkins', 0);
		update_option('bp-checkins-disable-geo-friends', 0);
		update_option('bp-checkins-activate-component', 0);

		dbDelta($sql);
		
	}
}