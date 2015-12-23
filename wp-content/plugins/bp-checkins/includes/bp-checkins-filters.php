<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


add_filter( 'bp_checkins_contenteditable_fix', 'bp_checkins_prepare_for_bp_filters', 1, 1);

function bp_checkins_prepare_for_bp_filters( $content ) {
	$content = str_replace('&nbsp;',' ', $content);
	$content = html_entity_decode( $content );
	$content = str_replace('<div>',"|", $content);
	$content = str_replace('</div>',"", $content);
	$content = trim( str_replace("|", "\n", $content) );
	return $content;
}

add_filter('bp_get_activity_content_body', 'bp_checkins_attach_thumb', 99, 1 );
	
function bp_checkins_attach_thumb( $activity_content ) {
	$activity_id = bp_get_activity_id();
	
	$pictures = bp_activity_get_meta( $activity_id, 'bpci_picture' );
	
	if( $pictures ){
		
		foreach( $pictures as $pic ){
			if($pic->width == 100){
				$src["100"] = $pic->url;
			}
			if($pic->width == 960){
				$src["960"] = $pic->url;
			}
		}
		
		if( $src ) {
			$image = '<a href="'.esc_attr( $src["960"] ).'" class="bp-ci-zoompic align-left"><img src="' . esc_attr( $src["100"] ) . '" width="100px" alt="' . __( 'Thumbnail', 'bp-checkins' ) . '" class="align-left thumbnail" /></a>';

			return $image . $activity_content;
		}
		else return $activity_content;
	}
	
	else return $activity_content;
}

add_filter("mce_external_plugins", "bp_checkins_tinymce_plugin");

function bp_checkins_tinymce_plugin($plugin_array) {
	if(!is_admin()){
		$plugin_array['BPCheckinsImg'] = BP_CHECKINS_PLUGIN_URL_JS.'/img/editor_plugin.js';
		$plugin_array['BPCheckinsLink'] = BP_CHECKINS_PLUGIN_URL_JS.'/link/editor_plugin.js';
	}
   		
   
	return $plugin_array;
}

add_filter('mce_buttons', 'bp_checkins_register_mce_button');

function bp_checkins_register_mce_button($buttons) {
   array_push($buttons, "separator", "BPCheckinsImg","BPCheckinsLink");

   return $buttons;
}



add_filter('mce_buttons', 'bp_checkins_teeny_button_filter', 9, 1);

function bp_checkins_teeny_button_filter($buttons){
	if(!is_admin())
		return array('bold, italic, underline, blockquote, separator, strikethrough, bullist, numlist,justifyleft, justifycenter, justifyright, undo, redo, BPCheckinsImg, BPCheckinsLink, unlink, fullscreen');
	else return $buttons;
}

function bp_checkins_comment_template( $comment_template ) {
	global $post;

	if( bp_is_current_component( 'checkins' ) &&  bp_is_current_action('place') && bp_action_variable( 0 ) && 'bp_checkins' != $post->post_type )
		return BP_CHECKINS_PLUGIN_DIR . '/templates/bp-checkins-place-comments.php';
	else
		return $comment_template;
}

add_filter('comments_template', 'bp_checkins_comment_template', 9, 1);

function bp_chekins_allow_comments( $retval, $open, $post_id ) {
	if( bp_is_current_component( 'checkins' ) &&  bp_is_current_action('place') && bp_action_variable( 0 ) )
		return $open;
	else
		return $retval;
}

add_filter( 'bp_force_comment_status',  'bp_chekins_allow_comments', 1, 3 );

add_filter('bp_modify_page_title', 'bp_checkins_browser_header', 99, 4);

function bp_checkins_browser_header( $title_and_sep, $title, $sep, $seplocation ){
	if( bp_checkins_if_single_place() ) {
		
		$place_title = ucfirst(bp_get_checkins_slug()) . ' '.$sep.' Place'  .$title_and_sep;
		
		$place = wp_cache_get( 'single_query', 'bp_checkins_single' );
		
		if ( false === $place || is_null( $place->query->post->ID ) ) {
			
			$place = new BP_Checkins_Place();

			$place->get( array( 'p' => bp_action_variable( 0 ) ) );
		}
		
		$place_title = apply_filters( 'get_the_title', $place->query->post->post_title ) . ' '.$sep.' Place'  .$title_and_sep;
		
		return $place_title;
	}
			
	elseif(bp_checkins_if_category_place())
		return ucfirst(bp_get_checkins_places_term_info( 'name', bp_action_variable( 1 ) ) ) . $title_and_sep;
		
	elseif(bp_checkins_if_place_home())
		return __('Browse or Search Places', 'bp-checkins') . $title_and_sep;
		
	elseif(bp_checkins_is_directory())
		return ucfirst(bp_get_checkins_slug()) . ' &amp Places ' .$sep .' ';
		
	else return $title_and_sep;
}

add_filter('bp_checkins_comment_form_title', 'bp_checkins_filter_comment_reply_title', 9, 1);

function bp_checkins_filter_comment_reply_title( $title_reply ) {
	global $bp;

	$list_checked_in_places =  get_transient('user_checkedin_'.$bp->loggedin_user->id );
	if( !empty( $list_checked_in_places ) && is_array( $list_checked_in_places ) && in_array( get_the_ID(), $list_checked_in_places) ) {
		
		return __('<a href="#" class="add-checkin without checkedin">Checked-in</a><a href="#" class="add-checkin with">Comment &amp; check-in</a>', 'bp-checkins');
	}
	else return $title_reply;
}

add_filter('pre_comment_approved', 'bp_checkins_new_rule_to_approve_comments', 11, 2);

function bp_checkins_new_rule_to_approve_comments( $approved, $commentdata ){
	
	$place_id = $commentdata['comment_post_ID'];
	
	$place = get_post( $place_id );
	
	if( $place->post_type == 'places' ) {
		if( !is_user_logged_in() )
			return 0;
			
		else
			return 1;
	}
	
	else
		return $approved;
}

add_filter('bp_checkins_places_form_show', 'bp_checkins_display_places_form_filter', 1, 1);

function bp_checkins_display_places_form_filter( $class ){
	if( bp_checkins_is_group_places_area() || bp_checkins_is_user_area() )
		return false;
	else
		return $class;
}

function bp_checkins_body_class_is_live( $classes ){
	$classes[] = 'bpci_is_live';
	return $classes;
}

function bp_checkins_body_class_is_single_place( $classes ) {
	$classes[] = 'single-place';
	return $classes;
}

function bp_checkins_add_live_comment_class( $classes ){
	$classes[] = 'bpci-latest';
	return $classes;
}

function bp_checkins_body_class_is_user( $classes ) {
	$classes[] = 'bpci-user';
	return $classes;
}


/* let's filter to avoid the spam and trash link in notification message */

function bp_checkins_filter_wp_notification($message, $comment_id){
	$comment = get_comment($comment_id);
	$post = get_post($comment->comment_post_ID);
	$user = get_userdata( $post->post_author );
	
	if( !user_can($user->ID, 'edit_comment') && $post->post_type == "places" ){
		$notify_message  = sprintf( __( 'New comment on your place "%s"' ), $post->post_title ) . "\r\n";
		$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= sprintf( __('Permalink: %s'), get_comment_link( $comment_id ) ) . "\r\n";
		return $notify_message;
	}
	else return $message;
}

add_filter('comment_notification_text', 'bp_checkins_filter_wp_notification', 9, 2);


function bp_checkins_activity_querystring_filter( $query_string, $object ) {
	// not on a checkin area, then return the query without changing it!
	if( !bp_is_current_component( 'checkins' ) && !bp_checkins_is_group_checkins_area() )
		return $query_string;
		
	/* Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts */
	if ( !empty( $_POST['cookie'] ) )
		$_BP_CI_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
	else
		$_BP_CI_COOKIE = &$_COOKIE;
		
	$defaults = array( 'page' => false );
	$r = wp_parse_args( $query_string, $defaults );
	extract( $r, EXTR_SKIP );
	
	//default values to filter on
	$object = 'checkins,groups';
	$action = 'activity_checkin,foursquare_checkin,place_checkin';
		
	$bp_ci_qs = false;
	
	$bp_ci_qs[] = 'object='.$object;

	/***
	 * Check if any cookie values are set. If there are then override the default params passed to the
	 * template loop
	 */
	if ( !empty( $_BP_CI_COOKIE['bp-checkins-filter'] ) && '-1' != $_BP_CI_COOKIE['bp-checkins-filter'] && $_BP_CI_COOKIE['bp-checkins-filter'] != 'friends_checkin') {
		$bp_ci_qs[] = 'type=' . $_BP_CI_COOKIE['bp-checkins-filter'];
		$bp_ci_qs[] = 'action=' . $_BP_CI_COOKIE['bp-checkins-filter'];
		
	} else {
		$bp_ci_qs[] = 'type=' . $object;
		$bp_ci_qs[] = 'action=' . $action;
	}
	
	if( !empty( $_BP_CI_COOKIE['bp-checkins-filter'] ) && '-1' != $_BP_CI_COOKIE['bp-checkins-filter'] && $_BP_CI_COOKIE['bp-checkins-filter'] == 'friends_checkin' ) {
			// this is my trick to transsform a filter to a scope !
			$bp_ci_qs[] = 'scope=friends';
			
	}
	//includes the !public group checkins
	if( bp_checkins_is_user_area() && bp_is_my_profile() ) {
		$bp_ci_qs[] = 'show_hidden=1';
	}
	
	if ( !empty( $page ) )
		$bp_ci_qs[]= 'page='.$page;
	
	//builds the bp_checkins query
	$query_string = empty( $bp_ci_qs ) ? '' : join( '&', (array)$bp_ci_qs );
	
	return apply_filters( 'bp_checkins_activity_querystring_filter', $query_string, $object, $action );
}

add_filter( 'bp_ajax_querystring', 'bp_checkins_activity_querystring_filter', 11, 2  );

/*
Maybe in a next release... needs to add some options to BP Checkins settings..
function bp_checkins_force_comment_status( $open, $post_id = 0 ) {

	// Get the post type of the post ID
	$post_type = get_post_type( $post_id );

	// Default return value is what is passed in $open
	$retval = $open;

	// Only force for bbPress post types
	if ( $post_type == 'places') {
		$retval = true;
	}

	// Allow override of the override
	return apply_filters( 'bp_checkins_force_comment_status', $retval, $open, $post_id, $post_type );
}

add_filter( 'comments_open', 'bp_checkins_force_comment_status', 10, 2 );*/

?>