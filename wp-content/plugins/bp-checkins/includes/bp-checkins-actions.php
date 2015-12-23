<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'bp_activity_posted_checkin', 'bp_checkins_record_geoloc_meta', 9, 3 );
add_action( 'bp_groups_posted_checkin', 'bp_checkins_record_group_geoloc_meta', 9, 4);

function bp_checkins_script_css_loader(){
	if( bp_checkins_is_user_area() ){
		wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
		wp_enqueue_script( 'gmap3', BP_CHECKINS_PLUGIN_URL_JS . '/gmap3.min.js', array('jquery') );
		
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
		wp_enqueue_script( 'bp-ckeckins-dir', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-dir.js' );
		bp_checkins_localize_script('dir');
		
		add_filter('bp_get_the_body_class', 'bp_checkins_body_class_is_user');
	}
}

add_action('bp_actions', 'bp_checkins_script_css_loader', 10);

function bp_checkins_single_place_check_access() {
	if( bp_checkins_if_single_place() ){
		global $wp_query, $bp;
		
		$redirect = bp_get_checkins_places_home();
		
		$check_places = wp_cache_get( 'single_query', 'bp_checkins_single' );
		
		if ( false === $check_places ) {
			
			$check_places = new BP_Checkins_Place();

			$check_places->get( array( 'p' => bp_action_variable( 0 ) ) );
		}
		
		$place_id = $check_places->query->post->ID;
		
		if( empty( $place_id ) ) {
			bp_core_add_message( __('OOps, looks like this place does not exist ! You can try to search for it or browse categories', 'bp-checkins'), 'error' );
			bp_core_redirect($redirect);
		} else {
			
			if( bp_is_active( 'groups' ) ) {
				
				// 1. check for group access !!

				$group_id = get_post_meta( $place_id, '_bpci_group_id', true );

				$bp->groups->current_group = new BP_Groups_Group( $group_id );

				if ( isset( $bp->groups->current_group->status ) && 'public' != $bp->groups->current_group->status ){
					if( !is_user_logged_in() || !groups_is_user_member( $bp->loggedin_user->id, $group_id ) ) {
						bp_core_add_message( __('OOps, looks like this place is private ! You can try to search for another one or browse categories', 'bp-checkins'), 'error' );
						bp_core_redirect($redirect);
					}
				}
				
			}
			
			// 2. check for live type to load the timer on the client side !
			if ( "live" == get_post_meta( $place_id, 'bpci_places_is_live', true ) ) {
				$start = get_post_meta( $place_id, 'bpci_places_live_start', true );
				$end = get_post_meta( $place_id, 'bpci_places_live_end', true );
				$start = strtotime($start);
				$end = strtotime($end);

				$now = current_time('timestamp');

				if ( $end >= $now && $now >= $start )
					add_filter('bp_get_the_body_class', 'bp_checkins_body_class_is_live');
					
			}
			add_filter('bp_get_the_body_class', 'bp_checkins_body_class_is_single_place');
			
			// 3. Check for notifications
			if ( isset( $_GET['n'] ) ) {
				bp_core_delete_notifications_by_item_id( $bp->loggedin_user->id, $place_id, 'checkins', 'new_comment' );
			}
		}
	}
	
}

add_action('bp_actions', 'bp_checkins_single_place_check_access', 11);

function bp_checkins_single_place_load_cssjs() {
	add_action('comment_form_after_fields', 'bp_checkins_places_geo_fields');
	if( bp_checkins_if_single_place() ) {
		wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
		wp_enqueue_script( 'gmap3', BP_CHECKINS_PLUGIN_URL_JS . '/gmap3.min.js', array('jquery') );
		wp_enqueue_script( 'bp-ckeckins-single', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-single.js' );
		bp_checkins_localize_script('single');
		
		add_action('bpci_map_single', 'bp_checkins_place_map');
		add_action('comment_form_top', 'bp_checkins_places_geo_fields');
		
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
		
	}
	else if( bp_checkins_if_category_place() ) {
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
		wp_enqueue_script( 'bp-ckeckins-cats', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-cats.js', array('jquery'));
	}
	else if( bp_checkins_if_place_home()  ){
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
		wp_enqueue_script( 'bp-jquery-autocomplete', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.autocomplete.js', array( 'jquery' ), '20110723' );
		wp_enqueue_script( 'bp-jquery-bgiframe', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.bgiframe.js', array(), '20110723' );
		wp_enqueue_script( 'bp-jquery-dimensions', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.dimensions.js', array(), '20110723' );
		add_action('wp_footer', 'bp_checkins_init_autocomplete');
	}
	
}

function bp_checkins_init_autocomplete(){
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var autocw = $('#places-search').width();
		$("#places-search").autocomplete(ajaxurl, {
			width: Number(autocw),
			selectFirst: false,
			extraParams: {
				action: 'autocomplete_place_src',
				'cookie':0
			}
		});
		window.launch = function(url){
			location.href = url;
		}
		$('#form-places-search').submit(function(){
			return false;
		});
	});
	</script>
	<?php
}

add_action('bp_actions', 'bp_checkins_single_place_load_cssjs', 12);


function bp_checkins_add_filter_options(){
	?>
	<option value="activity_checkin"><?php _e( 'Activity checkins', 'bp-checkins' ); ?></option>
	<option value="place_checkin"><?php _e( 'Place checkins', 'bp-checkins' ); ?></option>
	<?php
}

add_action('bp_activity_filter_options', 'bp_checkins_add_filter_options', 11);
add_action('bp_member_activity_filter_options', 'bp_checkins_add_filter_options', 11 );
add_action('bp_group_activity_filter_options', 'bp_checkins_add_filter_options', 11 );

function bp_checkins_add_places_filter_options(){
	?>
	<option value="new_place"><?php _e( 'All places', 'bp-checkins' ); ?></option>
	<option value="place_comment"><?php _e( 'Place comments', 'bp-checkins' ); ?></option>
	<?php
}

add_action('bp_activity_filter_options', 'bp_checkins_add_places_filter_options', 14);
add_action('bp_member_activity_filter_options', 'bp_checkins_add_places_filter_options', 14 );
add_action('bp_group_activity_filter_options', 'bp_checkins_add_places_filter_options', 14 );

add_action( 'comment_post', 'bp_checkins_add_checkins_meta_to_comment', 11, 2 );

function bp_checkins_add_checkins_meta_to_comment( $comment_id, $is_approved = true ) {
	/**
	* inspired by BuddyPress bp_blogs_record_comment function
	*/
	// is it a place comment !
	if ( !empty( $_POST['_bpci_place_id'] ) ) {
		
		$checkin = !empty( $_POST['_checkin_comment'] ) ? $_POST['_checkin_comment'] : false ;
		$place_id = intval( $_POST['_bpci_place_id'] );
		$place_name = esc_attr( $_POST['_bpci_place_name'] );
		$image_data = $_POST['_bpci_comment_image_url'];
		$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
		$html_meta = false;
		
		if ( empty( $is_approved ) )
			return false;
			
		$recorded_comment = get_comment( $comment_id );
		
		// Don't record activity if no email address has been included
		if ( empty( $recorded_comment->comment_author_email ) )
			return false;
			
		// Don't record activity if no content has been included
		if ( empty( $recorded_comment->comment_content ) )
			return false;

		// Get the user_id from the comment author email.
		$user    = get_user_by( 'email', $recorded_comment->comment_author_email );
		$user_id = (int)$user->ID;
		
		$recorded_comment->post = get_post( $recorded_comment->comment_post_ID );

		if ( empty( $recorded_comment->post ) || is_wp_error( $recorded_comment->post ) )
			return false;

		// Stop if the comment's associated post isn't a Place Post
		if ( !in_array( $recorded_comment->post->post_type, array( 'places' )  ) )
			return false;
			
		// preparing activity content
		$activity_content = wp_kses( $recorded_comment->comment_content, array() );
		$activity_content = bp_create_excerpt( $activity_content, 180, array( 'filter_shortcodes' => true) );
	
			
		//Now let's first record comment meta data (image) !
		if( !empty( $image_data ) ){

			$is_metas = explode('|', $image_data);
			if( count($is_metas) > 1){
				$html_meta = '<a href="'.$is_metas[0].'" class="bp-ci-zoompic align-left"><img src="'.$is_metas[1].'" width="100px" alt="Photo" class="align-left thumbnail" /></a>';
			} else {
				$html_meta = '<a href="'.$image_data.'" class="bp-ci-zoompic align-left"><img src="'.$image_data.'" width="100px" alt="Photo" class="align-left thumbnail" /></a>';
			}
			
			if( !empty( $html_meta ) )
				update_comment_meta( $comment_id, '_bpci_comment_image', $html_meta );
		}
		
		//do we have a checkin or is it a regular comment ?
		if( !empty( $checkin ) ) {
			
			$args = array(
				'content' => $html_meta . $activity_content,
				'user_id' => $user_id, 
				'type' => 'place_checkin_comment',
				'place_id' => $place_id,
				'place_name' => $place_name,
				'comment_id' => $comment_id
			);
			
			// update checkins count and list of users for this place
			bp_checkins_places_update_checkins_meta( $user_id, $place_id );
			// update user meta for user & cookie ?
			bp_checkins_places_user_checkins_transcient( $user_id, $place_id );
			
		} else {
			
			// empty lat lng address posted values...
			unset( $_POST['bpci-lat'] );
			unset( $_POST['bpci-lng'] );
			unset( $_POST['bpci-address'] );
			
			$args = array(
				'content' => $html_meta . $activity_content,
				'user_id' => $user_id, 
				'type' => 'place_comment',
				'place_id' => $place_id,
				'place_name' => $place_name,
				'comment_id' => $comment_id
			);
			
		}
		
		if( empty( $group_id ) )
			$activity_id = bp_checkins_post_update( $args );
		else {
			$args['group_id'] = $group_id;
			$activity_id = bp_checkins_groups_post_update( $args );
		}
		
		do_action( 'bp_chekins_places_recorded_comment', $recorded_comment->post->post_author, $place_id, $user_id, $comment_id );
					
		return true;
	}
	
}

add_action( 'bp_chekins_places_recorded_comment', 'bp_checkins_send_screen_notification', 1, 4 );

function bp_checkins_send_screen_notification( $author_id = false, $place_id = false, $user_id = false, $comment_id = false ){
	
	if( empty( $author_id ) || empty( $place_id ) || empty( $user_id ) )
		return false;
		
	if( $author_id == $user_id )
		return false;
	
	bp_core_add_notification( $place_id, $author_id, 'checkins', 'new_comment', $comment_id );
}

function bp_checkins_places_delete_content() {
	
	
	if( bp_is_current_component('checkins') && bp_is_current_action( 'comments' ) && 'delete' == bp_action_variable( 0 ) ) {
		
		$redirect = esc_url( wp_get_referer() );
		
		if ( !bp_action_variable( 1 ) || !is_numeric( bp_action_variable( 1 ) ) ) {
			bp_core_add_message( __('No comment to delete', 'bp-checkins'), 'error' );
			bp_core_redirect( $redirect );
		}
		
		check_admin_referer('bp_checkins_comment_delete_link');
		
		$comment_id = (int) bp_action_variable( 1 );
		
		$notice = bp_checkins_delete_place_comment( $comment_id );
		
		if( empty( $notice) )
			bp_core_add_message( __('No comment id was given, please try again', 'bp-checkins'), 'error' );
		
		else if( is_array( $notice ) && $notice['type'] == 'updated' )
			bp_core_add_message( $notice['message'] );
		
		else
			bp_core_add_message( $notice['message'], $notice['type'] );
		
		bp_core_redirect( $redirect );
		
	}
	
	if( bp_is_current_component('checkins') && bp_is_current_action( 'places' ) && 'delete' == bp_action_variable( 0 ) ) {
		
		$redirect = bp_get_checkins_places_home();
		
		if ( !bp_action_variable( 1 ) || !is_numeric( bp_action_variable( 1 ) ) ) {
			bp_core_add_message( __('No place to delete', 'bp-checkins'), 'error' );
			bp_core_redirect( $redirect );
		}
		
		check_admin_referer('bp_checkins_place_delete_link');
		
		$place_id = (int) bp_action_variable( 1 );
		
		do_action( 'bp_checkins_before_place_delete', $place_id );
		
		$notice = bp_checkins_delete_place( $place_id );
		
		if( empty( $notice) )
			bp_core_add_message( __('No place id was given, please try again', 'bp-checkins'), 'error' );
		
		else if( is_array( $notice ) && $notice['type'] == 'updated' )
			bp_core_add_message( $notice['message'] );
		
		else
			bp_core_add_message( $notice['message'], $notice['type'] );
			
		do_action( 'bp_checkins_after_place_delete', $place_id );
		
		bp_core_redirect( $redirect );
		
	}
}

add_action('bp_actions', 'bp_checkins_places_delete_content');

// if a group is deleted, then we'll need to delete places attached to it
add_action('groups_before_group_deleted', 'bp_checkins_group_disabled_checkins', 10, 1 );

// if a user is deleted, we delete his places
add_action( 'wpmu_delete_user',  'bp_checkins_user_is_deleted', 11, 1 );
add_action( 'delete_user',       'bp_checkins_user_is_deleted', 11, 1 );
add_action( 'bp_make_spam_user', 'bp_checkins_user_is_deleted', 11, 1 );

// if group changes its visibility status, we'll change the visibility of places if needed.
function bp_checkins_group_changed_status( $group_id = false ) {
	global $bp;
	
	$status = $_POST['group-status'];
	
	if( empty( $status ) || empty( $group_id ) )
		return false;
	
	return BP_Checkins_Place::group_update_visibility( $group_id, $status );
}

add_action( 'groups_group_settings_edited', 'bp_checkins_group_changed_status', 10, 1 );

/** to add type of activities to the 1.6 new admin screen select box */
function bp_checkins_activity_actions() {
	global $bp;

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) )
		return false;

	bp_activity_set_action( $bp->checkins->id, 'activity_checkin', __( 'Activity checkins', 'bp-checkins' ) );
	bp_activity_set_action( $bp->checkins->id, 'place_checkin',    __( 'Place checkins', 'bp-checkins' ) );
	
	if( bp_checkins_is_foursquare_ready() )
		bp_activity_set_action( $bp->checkins->id, 'foursquare_checkin',    __( 'Foursquare checkins', 'bp-checkins' ) );
		
	bp_activity_set_action( $bp->checkins->id, 'new_place',    __( 'All places', 'bp-checkins' ) );
	bp_activity_set_action( $bp->checkins->id, 'place_comment',    __( 'Place comments', 'bp-checkins' ) );

	do_action( 'bp_checkins_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'bp_checkins_activity_actions' );

function bp_checkins_reset_post_data() {
	if( bp_checkins_if_single_place() )
		remove_filter( 'bp_force_comment_status',  'bp_chekins_allow_comments', 1 );
	
	wp_reset_postdata();
}

add_action( 'bp_after_places_single_loop', 'bp_checkins_reset_post_data', 1 );
add_action( 'bp_after_member_places_body', 'bp_checkins_reset_post_data', 1 );
add_action( 'bp_after_home_bp_checkins_page', 'bp_checkins_reset_post_data', 1 );
add_action( 'bp_after_places_category_loop', 'bp_checkins_reset_post_data', 1 );
add_action( 'bp_after_group_places_content', 'bp_checkins_reset_post_data', 1 );
?>