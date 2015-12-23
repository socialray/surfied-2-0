<?php
/* 
Plugin Name: BP checkins
Plugin URI: http://imathi.eu/tag/bp-checkins/
Description: BuddyPress component to share checkins and places
Version: 1.2.2
Author: imath
Author URI: http://imathi.eu
License: GPLv2
Network: true
Text Domain: bp-checkins
Domain Path: /languages/
*/

/* définition des constantes */
define ( 'BP_CHECKINS_SLUG', 'checkins' );
define ( 'BP_CHECKINS_PLUGIN_NAME', 'bp-checkins' );
define ( 'BP_CHECKINS_PLUGIN_URL',  plugins_url('' , __FILE__) );
define ( 'BP_CHECKINS_PLUGIN_URL_JS',  plugins_url('js' , __FILE__) );
define ( 'BP_CHECKINS_PLUGIN_URL_CSS',  plugins_url('css' , __FILE__) );
define ( 'BP_CHECKINS_PLUGIN_URL_IMG',  plugins_url('images' , __FILE__) );
define ( 'BP_CHECKINS_PLUGIN_DIR',  WP_PLUGIN_DIR . '/' . BP_CHECKINS_PLUGIN_NAME );
define ( 'BP_CHECKINS_PLUGIN_VERSION', '1.2.2');

add_action('bp_include', 'bp_checkins_init');

function bp_checkins_init() {
	global $bp;
	
	/* we don't take any risk at all !!! */
	if( !bp_is_active( 'activity' ) )
		return;
	
	require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-shared.php' );
	
	if ( !(int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) || '' == bp_get_option( 'bp-checkins-disable-activity-checkins' ) )
		require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-activity.php' );
	
	if ( (int)bp_get_option( 'bp-checkins-activate-component' ) )
		require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-component.php' );
	
	if( is_admin() )
		require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-admin.php' );
		
}

/**
* bp_checkins_load_textdomain
* translation!
* 
*/
function bp_checkins_load_textdomain() {

	// try to get locale
	$locale = apply_filters( 'bp_checkins_load_textdomain_get_locale', get_locale() );

	// if we found a locale, try to load .mo file
	if ( !empty( $locale ) ) {
		// default .mo file path
		$mofile_default = sprintf( '%s/languages/%s-%s.mo', BP_CHECKINS_PLUGIN_DIR, BP_CHECKINS_PLUGIN_NAME, $locale );
		// final filtered file path
		$mofile = apply_filters( 'bp_checkins_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_CHECKINS_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'init', 'bp_checkins_load_textdomain', 8 );


function bp_checkins_needs_activity() {
        
	if( !bp_is_active( 'activity' ) ) {
		$buddy_settings_page = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
		?>
		<div id="message" class="updated">
			<p><?php printf( __('If you want to use BP Checkins, you need to activate the Activity Stream BuddyPress component, to do so, please activate it in <a href="%s">BuddyPress settings</a>', 'bp-checkins'), bp_get_admin_url( add_query_arg( array( 'page' => 'bp-components' ), $buddy_settings_page ) ) );?></p>
		</div>
		<?php
	}
}
 
add_action('admin_notices', 'bp_checkins_needs_activity' );


function bp_checkins_install(){
	if( !get_option( 'bp-checkins-version' ) || "" == get_option( 'bp-checkins-version' ) || BP_CHECKINS_PLUGIN_VERSION != get_option( 'bp-checkins-version' ) ){
		
		require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-install.php' );
		
		// let's add some tables.
		bp_checkins_install_db();

		update_option( 'bp-checkins-version', BP_CHECKINS_PLUGIN_VERSION );
	}
}

register_activation_hook( __FILE__, 'bp_checkins_install' );


function bp_checkins_deactivate() {
	// 1 suppress the checkins page if it exists
	$pages = get_option( 'bp-pages' );
	$active_components = get_option('bp-active-components');
	
	if( !empty( $pages[BP_CHECKINS_SLUG] ) ){
		wp_delete_post($pages[BP_CHECKINS_SLUG], true);
		unset($pages[BP_CHECKINS_SLUG]);
		update_option('bp-pages', $pages );
	}
	if( !empty( $active_components[BP_CHECKINS_SLUG] ) ){
		unset($active_components[BP_CHECKINS_SLUG]);
		update_option('bp-active-components', $active_components );
	}
	
	// 2 suppress cron job if it exists !
	$timestamp = wp_next_scheduled( 'bp_checkins_cron_job' );
	
	if( $timestamp )
		wp_unschedule_event( $timestamp, 'bp_checkins_cron_job' );
		
	// 3 suppress activities
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'new_place' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_comment' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_checkin' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'activity_checkin' ) );
	bp_activity_delete( array( 'component' => 'places' ) );
	bp_activity_delete( array( 'component' => 'checkins' ) );
	
	// 4 restore plugin options
	update_option('bp-checkins-disable-activity-checkins', 0);
	update_option('bp-checkins-disable-geo-friends', 0);
	update_option('bp-checkins-activate-component', 0);
}

register_deactivation_hook( __FILE__, 'bp_checkins_deactivate' );

?>