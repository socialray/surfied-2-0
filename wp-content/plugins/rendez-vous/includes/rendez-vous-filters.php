<?php
/**
 * Rendez Vous Filters.
 *
 * Filters
 *
 * @package Rendez Vous
 * @subpackage Filters
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Filters *******************************************************************/

// Apply WordPress defined filters
add_filter( 'rendez_vous_single_get_the_description', 'rendez_vous_filter_kses', 1 );
add_filter( 'rendez_vous_edit_get_the_description',   'rendez_vous_filter_kses', 1 );
add_filter( 'rendez_vous_single_get_the_venue',       'rendez_vous_filter_kses', 1 );
add_filter( 'rendez_vous_description_before_save',    'rendez_vous_filter_kses', 1 );
add_filter( 'rendez_vous_single_get_the_report',      'wp_filter_kses',          1 );
add_filter( 'rendez_vous_report_before_save',         'wp_filter_kses',          1 );
add_filter( 'rendez_vous_title_before_save',          'strip_tags',              1 );
add_filter( 'rendez_vous_venue_before_save',          'strip_tags',              1 );
add_filter( 'rendez_vous_duration_before_save',       'strip_tags',              1 );
add_filter( 'rendez_vous_single_get_the_duration',    'strip_tags',              1 );
add_filter( 'rendez_vous_single_get_the_title',       'strip_tags',              1 );
add_filter( 'rendez_vous_get_the_title',              'strip_tags',              1 );
add_filter( 'rendez_vous_get_the_excerpt',            'strip_tags',              1 );

add_filter( 'rendez_vous_get_the_excerpt',            'force_balance_tags' );
add_filter( 'rendez_vous_single_get_the_description', 'force_balance_tags' );
add_filter( 'rendez_vous_single_get_the_report',      'force_balance_tags' );

add_filter( 'rendez_vous_get_the_excerpt',            'wptexturize' );
add_filter( 'rendez_vous_single_get_the_description', 'wptexturize' );
add_filter( 'rendez_vous_get_the_title',              'wptexturize' );
add_filter( 'rendez_vous_single_get_the_title',       'wptexturize' );
add_filter( 'rendez_vous_single_get_the_report',      'wptexturize' );

add_filter( 'rendez_vous_get_the_excerpt',            'convert_smilies' );
add_filter( 'rendez_vous_single_get_the_description', 'convert_smilies' );
add_filter( 'rendez_vous_single_get_the_report',      'convert_smilies' );

add_filter( 'rendez_vous_get_the_excerpt',            'convert_chars' );
add_filter( 'rendez_vous_single_get_the_description', 'convert_chars' );
add_filter( 'rendez_vous_single_get_the_report',      'convert_chars' );

add_filter( 'rendez_vous_get_the_excerpt',            'wpautop' );
add_filter( 'rendez_vous_single_get_the_description', 'wpautop' );
add_filter( 'rendez_vous_single_get_the_report',      'wpautop' );

add_filter( 'rendez_vous_single_get_the_description', 'make_clickable', 9 );
add_filter( 'rendez_vous_single_get_the_report',      'make_clickable', 9 );

add_filter( 'rendez_vous_get_the_excerpt',            'stripslashes_deep', 5 );
add_filter( 'rendez_vous_single_get_the_description', 'stripslashes_deep', 5 );
add_filter( 'rendez_vous_single_get_the_report',      'stripslashes_deep', 5 );
add_filter( 'rendez_vous_single_get_the_venue',       'stripslashes_deep', 5 );
add_filter( 'rendez_vous_single_get_the_title',       'stripslashes_deep', 5 );
add_filter( 'rendez_vous_get_the_title',              'stripslashes_deep', 5 );
add_filter( 'rendez_vous_edit_get_the_description',   'stripslashes_deep', 5 );

add_filter( 'rendez_vous_single_get_the_report',      'rendez_vous_make_nofollow_filter' );
add_filter( 'rendez_vous_single_get_the_report',      'rendez_vous_make_nofollow_filter' );
add_filter( 'rendez_vous_single_get_the_description', 'rendez_vous_make_nofollow_filter' );

add_filter( 'rendez_vous_single_get_the_date', 'rendez_vous_append_ical_link', 10, 2 );

/**
 * Custom kses filtering for rendez-vous excerpt content.
 *
 * inspired by bp_activity_filter_kses
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_filter_kses( $content ) {
	global $allowedtags;

	$activity_allowedtags = $allowedtags;
	$activity_allowedtags['span']          = array();
	$activity_allowedtags['span']['class'] = array();
	$activity_allowedtags['a']['class']    = array();
	$activity_allowedtags['a']['id']       = array();
	$activity_allowedtags['a']['rel']      = array();
	$activity_allowedtags['img']           = array();
	$activity_allowedtags['img']['src']    = array();
	$activity_allowedtags['img']['alt']    = array();
	$activity_allowedtags['img']['width']  = array();
	$activity_allowedtags['img']['height'] = array();
	$activity_allowedtags['img']['class']  = array();
	$activity_allowedtags['img']['id']     = array();
	$activity_allowedtags['img']['title']  = array();
	$activity_allowedtags['code']          = array();

	$activity_allowedtags = apply_filters( 'rendez_vous_filter_kses', $activity_allowedtags );
	return wp_kses( $content, $activity_allowedtags );
}

/**
 * Add rel=nofollow to a link.
 *
 * inspired bp_activity_make_nofollow_filter
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_make_nofollow_filter( $text = '' ) {
	return preg_replace_callback( '|<a (.+?)>|i', 'rendez_vous_make_nofollow_filter_callback', $text );
}

	/**
	 * Add rel=nofollow to a link.
	 *
	 * inspired by bp_activity_make_nofollow_filter_callback
	 *
	 * @package Rendez Vous
	 * @subpackage Filters
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function rendez_vous_make_nofollow_filter_callback( $matches ) {
		$text = $matches[1];
		$text = str_replace( array( ' rel="nofollow"', " rel='nofollow'"), '', $text );
		return "<a $text rel=\"nofollow\">";
	}

/**
 * Add oembed support to rendez-vous description and report.
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.3.0)
 * @uses BP_Embed
 */
function rendez_vous_allow_oembed( $bp_oembed_class = null ) {
	add_filter( 'rendez_vous_single_get_the_report', array( &$bp_oembed_class, 'autoembed' ), 8 );
	add_filter( 'rendez_vous_single_get_the_report', array( &$bp_oembed_class, 'run_shortcode' ), 7 );

	add_filter( 'rendez_vous_single_get_the_description', array( &$bp_oembed_class, 'autoembed' ), 8 );
	add_filter( 'rendez_vous_single_get_the_description', array( &$bp_oembed_class, 'run_shortcode' ), 7 );
}
add_action( 'bp_core_setup_oembed', 'rendez_vous_allow_oembed', 10, 1 );

/**
 * Map capabilities
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		/** Reading ***********************************************************/

		case 'read_private_rendez_vouss' :

			if ( ! empty( $args[0] ) ) {
				// Get the post
				$_post = get_post( $args[0] );
				if ( ! empty( $_post ) ) {

					// Get caps for post type object
					$post_type           = get_post_type_object( $_post->post_type );
					$post_meta_attendees = get_post_meta( $_post->ID, '_rendez_vous_attendees' );
					$attendees           = ! empty( $post_meta_attendees ) ? (array) $post_meta_attendees : array();
					$caps                = array();

					// Allow author to edit his rendez vous
					if ( $user_id == $_post->post_author || in_array( $user_id, $attendees ) ) {
						$caps[] = 'exist';

					// Admins can always edit
					} else if ( user_can( $user_id, 'manage_options' ) ) {
						$caps = array( 'manage_options' );
					} else {
						$caps[] = $post_type->cap->edit_others_posts;
					}

				}

			} else if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Publishing ********************************************************/

		case 'publish_rendez_vouss' :

			if ( bp_is_my_profile() ) {
				$caps = array( 'exist' );
			}

			// Admins can always publish
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Participate to rendez-vous *********************************/

		case 'subscribe_rendez_vous' :
			if ( ! empty( $user_id ) ) {
				$caps = array( 'exist' );
			}

			break;

		/** Editing ***********************************************************/

		case 'edit_rendez_vouss'        :

			if ( bp_is_my_profile() ) {
				$caps = array( 'exist' );
			}

			// Admins can always edit
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// Used primarily in wp-admin
		case 'edit_others_rendez_vouss' :

			// Admins can always edit
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// Used everywhere
		case 'edit_rendez_vous' :

			if ( ! empty( $args[0] ) ) {
				// Get the post
				$_post = get_post( $args[0] );
				if ( ! empty( $_post ) ) {

					// Get caps for post type object
					$post_type = get_post_type_object( $_post->post_type );
					$caps      = array();

					// Allow author to edit his rendez vous
					if ( $user_id == $_post->post_author ) {
						$caps[] = 'exist';

					// Admins can always edit
					} else if ( user_can( $user_id, 'manage_options' ) ) {
						$caps = array( 'manage_options' );
					} else {
						$caps[] = $post_type->cap->edit_others_posts;
					}

				}

			} else if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Deleting **********************************************************/

		case 'delete_rendez_vous' :

			if ( ! empty( $args[0] ) ) {
				// Get the post
				$_post = get_post( $args[0] );
				if ( ! empty( $_post ) ) {

					// Get caps for post type object
					$post_type = get_post_type_object( $_post->post_type );
					$caps      = array();

					// Allow author to edit his rendez vous
					if ( $user_id == $_post->post_author ) {
						$caps[] = 'exist';

					// Admins can always edit
					} else if ( user_can( $user_id, 'manage_options' ) ) {
						$caps = array( 'manage_options' );
					} else {
						$caps[] = $post_type->cap->delete_others_posts;
					}
				}

			} else if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// Moderation override
		case 'delete_rendez_vouss'        :
		case 'delete_others_rendez_vouss' :

			// Moderators can always delete
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Admin *************************************************************/

		case 'rendez_vouss_moderate' :

			// Admins can always moderate
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

	}

	return apply_filters( 'rendez_vous_map_meta_caps', $caps, $cap, $user_id, $args );
}
add_filter( 'map_meta_cap', 'rendez_vous_map_meta_caps', 10, 4 );

/*** Editor filters, inspired by bbPress way of dealing with it ***/


/**
 * Edit TinyMCE plugins to match core behaviour
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_tiny_mce_plugins( $plugins = array() ) {

	// Unset fullscreen
	foreach ( $plugins as $key => $value ) {
		if ( 'fullscreen' === $value ) {
			unset( $plugins[$key] );
			break;
		}
	}

	return apply_filters( 'rendez_vous_get_tiny_mce_plugins', $plugins );
}

/**
 * Edit TeenyMCE buttons to match allowedtags
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_teeny_mce_buttons( $buttons = array() ) {

	// Remove some buttons from TeenyMCE
	$buttons = array_diff( $buttons, array(
		'underline',
		'justifyleft',
		'justifycenter',
		'justifyright',
		'aligncenter',
		'alignleft',
		'alignright',
		'numlist',
		'bullist'
	) );

	return apply_filters( 'rendez_vous_teeny_mce_buttons', $buttons );
}

/**
 * Edit TinyMCE quicktags buttons to match allowedtags
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_quicktags_settings( $settings = array() ) {

	// Get buttons out of settings
	$buttons_array = explode( ',', $settings['buttons'] );

	// Diff the ones we don't want out
	$buttons = array_diff( $buttons_array, array(
		'ins',
		'more',
		'spell',
		'img',
		'ul',
		'li',
		'ol'
	) );

	// Put them back into a string in the $settings array
	$settings['buttons'] = implode( ',', $buttons );

	return apply_filters( 'rendez_vous_quicktags_settings', $settings );
}

/**
 * Append a link to download the iCalendar file of the rendez-vous
 *
 * If for some reason, the dates/hours are not consistent, simply use
 * remove_filter( 'rendez_vous_single_get_the_date', 'rendez_vous_append_ical_link' );
 * till i'll fix the issue ;)
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.1.0)
 *
 * @param  string $output      the definitive date output for the rendez-vous
 * @param  Rendez_Vous_Item $rendez_vous the rendez-vous object
 * @return string              HTML Output
 */
function rendez_vous_append_ical_link( $output = '', $rendez_vous = null ) {
	if ( empty( $output ) || empty( $rendez_vous ) ) {
		return $output;
	}

	if ( bp_loggedin_user_id() != $rendez_vous->organizer && ! in_array( bp_loggedin_user_id(), $rendez_vous->attendees ) ) {
		return $output;
	}

	$output .= ' <a href="' . esc_url( rendez_vous_get_ical_link( $rendez_vous->id, $rendez_vous->organizer ) ) . '" title="' . esc_attr__( 'Download the iCal file', 'rendez-vous' ) . '" class="ical-link"><span></span></a>';

	return $output;
}

/**
 * Adds Rendez-vous slug into groups forbidden names
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since  Rendez Vous (1.1.0)
 *
 * @param  array  $names the groups forbidden names
 * @uses   buddypress() to get the BuddyPress main instance
 * @return array        the same names + rendez-vous forbidden ones.
 */
function rendez_vous_forbidden_names( $names = array() ) {
	// Get the rendez-vous slug
	$rendez_vous_slug = buddypress()->rendez_vous->slug;

	$forbidden = array( $rendez_vous_slug );

	// Just in case!
	if ( 'rendez-vous' != $rendez_vous_slug ) {
		$forbidden[] = 'rendez-vous';
	}

	return array_merge( $names, $forbidden );
}
add_filter( 'groups_forbidden_names', 'rendez_vous_forbidden_names', 10, 1 );

/**
 * Customize the login message
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since  Rendez Vous (1.1.0)
 *
 * @param  string $message  the login message
 * @param  string $redirect the url to redirect to once logged in
 * @uses   buddypress()     to get BuddyPress instance
 * @return string           the login message
 */
function render_vous_login_message( $message = '', $redirect = '' ) {
	if( ! empty( $redirect ) && false !== strpos( $redirect, buddypress()->rendez_vous->slug . '/schedule/ical' ) ) {
		$message = __( 'You must log in to download the calendar file.', 'render-vous' );
	}

	return $message;
}
add_filter( 'bp_wp_login_error', 'render_vous_login_message', 10, 2 );
