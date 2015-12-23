<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


add_filter( 'bp_get_template_stack', 'bp_checkins_get_template_part', 10, 1 );

function bp_checkins_get_template_part( $templates ) {
	
	if ( ( bp_is_current_component( 'checkins' ) && !bp_checkins_is_bp_default() ) || ( bp_checkins_is_group_checkins_area() && !bp_checkins_is_bp_default() ) ) {
		
		$templates[] = BP_CHECKINS_PLUGIN_DIR . '/templates/';
	}
	
	return $templates;
}

/**
* bp_checkins_load_template_filter
* loads template filter
*/
function bp_checkins_load_template_filter( $found_template, $templates ) {
	global $bp,$bp_deactivated;
	
	if ( !bp_checkins_is_bp_default() )
		return $found_template;

	//Only filter the template location when we're on the example component pages.
	if ( !bp_is_current_component( 'checkins' ) )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates[] = BP_CHECKINS_PLUGIN_DIR . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'bp_checkins_load_template_filter', $found_template );
}

add_filter( 'bp_located_template', 'bp_checkins_load_template_filter', 10, 2 );


function bp_checkins_screen_index() {
	
	if ( !bp_displayed_user_id() && bp_is_current_component( 'checkins' ) && !bp_current_action() ) {
		bp_update_is_directory( true, 'checkins' );

		do_action( 'bp_checkins_screen_index' );

		bp_core_load_template( apply_filters( 'bp_checkins_screen_index', 'bp-checkins' ) );
	}
}
add_action( 'bp_screens', 'bp_checkins_screen_index' );


function bp_checkins_is_single_place_screen() {

	if ( bp_checkins_is_place_home() ) {
		
		if( bp_checkins_is_single_place() ) {
			global $wp_query;
			$wp_query->is_singular = true;
			
			do_action( 'bp_checkins_screen_is_single_place' );

			bp_core_load_template( apply_filters( 'bp_checkins_is_single_place', 'bp-checkins-place-single' ) );
		}
		
		else if( bp_checkins_is_category_place() ) {
			global $wp_query;
			$wp_query->is_singular = true;
			
			do_action( 'bp_checkins_screen_is_category_place' );
			
			bp_core_load_template( apply_filters( 'bp_checkins_is_category_place', 'bp-checkins-place-category' ) );
			
		}
		
		else {
			
			do_action( 'bp_checkins_screen_is_home' );
			
			bp_core_load_template( apply_filters( 'bp_checkins_is_home_place', 'bp-checkins-place-home' ) );
			
		}
	}
}


add_action('bp_screens', 'bp_checkins_is_single_place_screen');


function bp_checkins_screen_settings_menu() {
	add_action( 'bp_template_content_header', 'bp_checkins_screen_settings_menu_header' );
	add_action( 'bp_template_title', 'bp_checkins_screen_settings_menu_title' );
	add_action( 'bp_template_content', 'bp_checkins_screen_settings_menu_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_checkins_screen_settings_menu_header() {
	_e( 'Checkins Settings', 'bp-checkins' );
}

function bp_checkins_screen_settings_menu_title() {
	_e( 'You have a foursquare account ?', 'bp-checkins' );
}

function bp_checkins_screen_settings_menu_content() {	
	?>
		<?php if( bp_checkins_is_logged_in_user_connected() ):?>
			
			<p><?php _e('Your account is connected to this website.', 'bp-checkins');?></p>
			
			<div id="foursquare-connected"></div>
			
			<?php if ( !(int)bp_get_option( 'foursquare-user-import' ) || '' == bp_get_option( 'foursquare-user-import' ) ) :?>
				<div id="user_fs_update">
					<a href="?update=1" class="button"><?php _e('Import your new foursquare checkins', 'bp-checkins');?></a>
				</div>
			<?php endif;?>
			
		<?php else:?>
			<p><?php _e("Let's connect foursquare checkins to this website !", 'bp-checkins');?></p>

			<?php bp_checkins_foursquare_user_activation();?>
			
		<?php endif;?>

<?php
}

function bp_checkins_my_checkins(){
	
	bp_core_load_template( apply_filters( 'bp_checkins_my_checkins', 'bp-checkins-mine' ) );
	
	if( !bp_checkins_is_bp_default() )
		add_filter( 'bp_get_template_part', 'bp_checkins_user_checkins_template_part', 10, 3 );
}

function bp_checkins_user_checkins_template_part( $templates, $slug, $name ) {
	if( $slug != 'members/single/plugins' )
		return $templates;
	
	return array( 'bp-checkins-mine.php' );
}


function bp_checkins_my_places(){
	
	if ( isset( $_GET['n'] ) ) {
		bp_core_delete_notifications_by_type( bp_loggedin_user_id(), 'checkins', 'new_comment' );
	}
	
	bp_core_load_template( apply_filters( 'bp_checkins_my_places', 'bp-checkins-my-places' ) );
	
	if( !bp_checkins_is_bp_default() )
		add_filter( 'bp_get_template_part', 'bp_checkins_user_places_template_part', 10, 3 );
}

function bp_checkins_user_places_template_part( $templates, $slug, $name ) {
	if( $slug != 'members/single/plugins' )
		return $templates;
	
	return array( 'bp-checkins-my-places.php' );
}

function bp_checkins_load_template_choose( $template = false, $require_once = true ) {
	if( empty( $template ) )
		return false;
	
	if( bp_checkins_is_bp_default() ) {
		bp_checkins_load_template( $template . '.php', $require_once );
	} else {
		bp_get_template_part( $template );
	}
}

function bp_checkins_locate_template_choose( $template = false ) {
	if( empty( $template ) )
		return false;
	
	if( bp_checkins_is_bp_default() ) {
		locate_template( array(  $template . '.php' ), true );
	} else {
		bp_get_template_part( $template );
	}
}

function bp_checkins_load_template( $template, $require_once = true ){	
	
	if ( file_exists( STYLESHEETPATH . '/' . $template ) )
		$filtered_templates = STYLESHEETPATH . '/' . $template;
	else
		$filtered_templates = BP_CHECKINS_PLUGIN_DIR . '/templates/' . $template;
		
	status_header( 200 );
		
	load_template( apply_filters( 'bp_checkins_load_template', $filtered_templates ),  $require_once);
}


/** Theme Compatability *******************************************************/

/**
 * The main theme compat class for BuddyPress Groups
 *
 * This class sets up the necessary theme compatability actions to safely output
 * group template parts to the_title and the_content areas of a theme.
 *
 * @since BuddyPress (1.7)
 */
class BP_Checkins_Theme_Compat {

	/**
	 * Setup the groups component theme compatibility
	 *
	 * @since BuddyPress (1.7)
	 */
	public function __construct() {
		global $bp;
		
		add_action( 'bp_setup_theme_compat', array( $this, 'is_checkin' ) );
	}

	/**
	 * Are we looking at something that needs group theme compatability?
	 *
	 * @since BuddyPress (1.7)
	 */
	public function is_checkin() {
		
		if ( ! bp_current_action() && !bp_displayed_user_id() && bp_is_current_component( 'checkins' ) ) {
			bp_update_is_directory( true, 'checkins' );

			do_action( 'checkins_directory_checkins_setup' );

			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'bp_replace_the_content',                    array( $this, 'directory_content'    ) );

		}
		
		if ( bp_checkins_is_place_home() ) {

			if( bp_checkins_is_single_place() ) {

				do_action( 'bp_checkins_screen_is_single_place' );

				add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'single_place_dummy_post' ) );
				add_filter( 'bp_replace_the_content',                    array( $this, 'single_place_content'    ) );
			}

			else if( bp_checkins_is_category_place() ) {
				
				add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'place_category_dummy_post' ) );
				add_filter( 'bp_replace_the_content',                    array( $this, 'place_category_content'    ) );
			}

			else {

				add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'place_home_dummy_post' ) );
				add_filter( 'bp_replace_the_content',                    array( $this, 'place_home_content'    ) );

			}
		}
	}

	/** Directory *************************************************************/

	/**
	 * Update the global $post with directory data
	 *
	 * @since BuddyPress (1.7)
	 */
	public function directory_dummy_post() {

		// Title based on ability to create groups 
		$title = __( 'Checkins & Places', 'bp-checkins' );
		
		if( is_user_logged_in() )
			$title = '<span id="checkins-dir-title">' . __('Where are you ?', 'bp-checkins') ."</span>";
		

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => $title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_checkins',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the groups index template part
	 *
	 * @since BuddyPress (1.7)
	 */
	public function directory_content() {
		
		bp_buffer_template_part( 'bp-checkins' );
	}
	
	/** Single Place *************************************************************/

	/**
	 * Update the global $post with directory data
	 *
	 * @since BuddyPress (1.7)
	 */
	public function single_place_dummy_post() {
		
		$place = wp_cache_get( 'single_query', 'bp_checkins_single' );
		
		if ( false === $place ) {
			
			$place = new BP_Checkins_Place();

			$place->get( array( 'p' => bp_action_variable( 0 ) ) );	
		}

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => $place->query->post->post_title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_checkins',
			'post_status'    => 'publish',
			'is_archive'     => false,
			'comment_status' => 'closed',
			'is_single'     => true,
		) );
	}

	/**
	 * Filter the_content with the groups index template part
	 *
	 * @since BuddyPress (1.7)
	 */
	public function single_place_content() {
		bp_buffer_template_part( 'bp-checkins-place-single' );
	}
	
	/** Place Category *************************************************************/

	/**
	 * Update the global $post with directory data
	 *
	 * @since BuddyPress (1.7)
	 */
	public function place_category_dummy_post() {

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => __('Browse Place categories', 'bp-checkins'),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_checkins',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed',
		) );
	}

	/**
	 * Filter the_content with the groups index template part
	 *
	 * @since BuddyPress (1.7)
	 */
	public function place_category_content() {
		bp_buffer_template_part( 'bp-checkins-place-category' );
	}
	
	/** Place Home *************************************************************/

	/**
	 * Update the global $post with directory data
	 *
	 * @since BuddyPress (1.7)
	 */
	public function place_home_dummy_post() {

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => __('Search Places', 'bp-checkins'),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_checkins',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed',
		) );
	}

	/**
	 * Filter the_content with the groups index template part
	 *
	 * @since BuddyPress (1.7)
	 */
	public function place_home_content() {
		bp_buffer_template_part( 'bp-checkins-place-home' );
	}
	

}

new BP_Checkins_Theme_Compat();
