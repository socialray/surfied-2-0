<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function bp_checkins_switch_template() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Sanitize the post object
	$object = esc_attr( $_POST['object'] );
	
	ob_start();
	if( $object == 'places')
		bp_checkins_load_template_choose( 'bp-checkins-places-loop' );
	else
		bp_checkins_locate_template_choose( 'activity/activity-loop' );
	$result = array();
	$result['contents'] = ob_get_contents();
	
	if( $object == 'places') {
		$result['selectbox'] = bp_checkins_get_places_filter();
	} else {
		$result['selectbox'] = 0;
	}
	//$result['feed_url'] = apply_filters( 'bp_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	echo json_encode( $result );
	exit;
	
}

add_action('wp_ajax_switch_checkins_template', 'bp_checkins_switch_template');
add_action('wp_ajax_nopriv_switch_checkins_template', 'bp_checkins_switch_template');

function bp_checkins_filter_ajax_query( $query_string, $object, $action ) {
	global $bp;

	if ( empty( $object ) )
		return false;

	/* Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts */
	if ( !empty( $_POST['cookie'] ) )
		$_BP_CI_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
	else
		$_BP_CI_COOKIE = &$_COOKIE;

	$bp_ci_qs = false;
	
	/* we only want places */
	$bp_ci_qs[] = 'object='.$object;
	
	//$fix_object_filter = str_replace(',groups', '', $object);

	/***
	 * Check if any cookie values are set. If there are then override the default params passed to the
	 * template loop
	 */
	if ( !empty( $_BP_CI_COOKIE['bp-' . $object . '-filter'] ) && '-1' != $_BP_CI_COOKIE['bp-' . $object . '-filter'] ) {
		$bp_ci_qs[] = 'type=' . $_BP_CI_COOKIE['bp-' . $object . '-filter'];
		$bp_ci_qs[] = 'action=' . $_BP_CI_COOKIE['bp-' . $object . '-filter']; // bp-checkins filtering
		
	}

	if( bp_checkins_is_user_area() && $bp->displayed_user->id == $bp->loggedin_user->id ) {
		$bp_ci_qs[] = 'show_hidden=1';
	}

	/* If page and search_terms have been passed via the AJAX post request, use those */
	if ( !empty( $_POST['page'] ) && '-1' != $_POST['page'] )
		$bp_ci_qs[] = 'page=' . $_POST['page'];

	/* Now pass the querystring to override default values. */
	$query_string = empty( $bp_ci_qs ) ? '' : join( '&', (array)$bp_ci_qs );

	$object_filter = '';
	if ( isset( $_BP_CI_COOKIE['bp-' . $object . '-filter'] ) && $_BP_CI_COOKIE['bp-' . $object . '-filter'] != 'friends_checkin')
		$object_filter = $_BP_CI_COOKIE['bp-' . $object . '-filter'];

	return apply_filters( 'bp_checkins_filter_ajax_query', $query_string, $object, $object_filter );
}
add_filter( 'bp_checkins_ajax_querystring', 'bp_checkins_filter_ajax_query', 10, 3 );


function bp_checkins_checkins_template_loader() {
	global $bp;

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$scope = '';
	if ( !empty( $_POST['scope'] ) )
		$scope = $_POST['scope'];

	/* Buffer the loop in the template to a var for JS to spit out. */
	ob_start();
	bp_checkins_locate_template_choose( 'activity/activity-loop' );
	$result = array();
	$result['contents'] = ob_get_contents();
	//$result['feed_url'] = apply_filters( 'bp_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	echo json_encode( $result );
	exit;
}
add_action( 'wp_ajax_checkins_apply_filter', 'bp_checkins_checkins_template_loader' );
add_action( 'wp_ajax_checkins_get_older_updates', 'bp_checkins_checkins_template_loader' );
add_action( 'wp_ajax_nopriv_checkins_apply_filter', 'bp_checkins_checkins_template_loader' );
add_action( 'wp_ajax_nopriv_checkins_get_older_updates', 'bp_checkins_checkins_template_loader' );

function bp_checkins_places_template_loader() {
	global $bp;
	
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;
	
	ob_start();
	bp_checkins_load_template_choose( 'bp-checkins-places-loop' );
	$result = array();
	$result['contents'] = ob_get_contents();
	//$result['feed_url'] = apply_filters( 'bp_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	echo json_encode( $result );
	exit;
}

add_action( 'wp_ajax_places_get_older_updates', 'bp_checkins_places_template_loader' );
add_action( 'wp_ajax_places_apply_filter', 'bp_checkins_places_template_loader' );
add_action( 'wp_ajax_nopriv_places_get_older_updates', 'bp_checkins_places_template_loader' );
add_action( 'wp_ajax_nopriv_places_apply_filter', 'bp_checkins_places_template_loader' );

function bp_checkins_post_checkin() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'post_checkin', '_wpnonce_post_checkin' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	
	$content = apply_filters('bp_checkins_contenteditable_fix', $_POST['content'] );

	$activity_id = 0;
	if ( $_POST['object'] == "checkin" && bp_is_active( 'checkins' ) ) {
		$activity_id = bp_checkins_post_update( array( 'content' => $content ) );

	} elseif ( $_POST['object'] == 'groups' ) {
		if ( !empty( $_POST['item_id'] ) && bp_is_active( 'groups' ) )
			$activity_id = bp_checkins_groups_post_update( array( 'content' => $content, 'group_id' => $_POST['item_id'] ) );

	} else {
		$activity_id = apply_filters( 'bp_checkins_custom_update', $_POST['object'], $_POST['item_id'], $content );
	}

	if ( empty( $activity_id ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'bp-checkins' ) . '</p></div>';
		return false;
	}

	if ( bp_has_activities ( 'include=' . $activity_id ) ) : ?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
			<?php bp_checkins_locate_template_choose( 'activity/entry' ) ?>
		<?php endwhile; ?>
	 <?php endif;
	exit;
}

add_action('wp_ajax_post_checkin', 'bp_checkins_post_checkin');
add_action('wp_ajax_nopriv_post_checkin', 'bp_checkins_post_checkin');

function bp_checkins_post_places() {
	global $bp;	
	
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'post_places', '_wpnonce_post_places' );
	
	$group_id = 0;
	if( !empty( $_POST['group_id'] ) )
		$group_id = intval( $_POST['group_id'] );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}
	
	if ( empty( $_POST['title'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please put the name of the place.', 'bp-checkins' ) . '</p></div>';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to place.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	
	if ( empty( $_POST['bpci-address'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter an address for the place.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	
	if ( empty( $_POST['category'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please select a category for the place.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	
	if ( $_POST['type'] == "live" && ( $_POST['start'] == " " || $_POST['end'] == " " ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please choose a date for your live place.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	

	$args = array(
			'group_id'        => $group_id,
			'title'           => $_POST['title'],
			'content'         => $_POST['content'],
			'address'         => $_POST['bpci-address'],
			'lat'             => $_POST['bpci-lat'],
			'lng'             => $_POST['bpci-lng'],
			'place_category'  => intval( $_POST['category']) 
			);
			
	if( $_POST['type'] == "live") {
		$args['type']  = $_POST['type'];
		$args['start'] = $_POST['start'];
		$args['end']   = $_POST['end'];
	}
	
	// If the group is not public, hide the activity sitewide.
	
	if( !empty( $group_id ) ) {
		
		$bp->groups->current_group = new BP_Groups_Group( $group_id );
		
		if ( isset( $bp->groups->current_group->status ) && 'public' != $bp->groups->current_group->status )
			$args['hide_sitewide'] = 1;
		/*else
			$args['hide_sitewide'] = 0;*/
	}
	
	
	
	$place_id = bp_checkins_add_new_place( $args );
	
	if ( empty( $place_id ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your place, please try again.', 'bp-checkins' ) . '</p></div>';
		return false;
	}
	
	// setting the activity content
	$place = get_post( $place_id );
	$thumbnail = bp_get_checkins_places_featured_image( $place_id );
	$excerpt = bp_get_checkins_places_excerpt( $place->post_content );
	$place_name = esc_attr( $place->post_title );
	
	$content = apply_filters('bp_checkins_place_content_before_activity', $thumbnail . $excerpt );

	$activity_id = 0;
	if ( empty( $group_id ) && bp_is_active( 'checkins' ) ) {
		$activity_id = bp_checkins_post_update( array( 'content' => $content, 'type' =>'new_place', 'place_id' => $place_id, 'place_name' => $place_name) );

	} else {
		$activity_id = bp_checkins_groups_post_update( array( 'content' => $content, 'group_id' => $group_id, 'type' =>'new_place', 'place_id' => $place_id, 'place_name' => $place_name ) );

	}

	if ( empty( $place_id ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your place, please try again.', 'bp-checkins' ) . '</p></div>';
		return false;
	}

	if ( bp_checkins_has_places ( 'p=' . $place->post_name ) ) : ?>
		<?php while ( bp_checkins_has_places() ) : bp_checkins_the_place(); ?>
			<?php bp_checkins_load_template_choose( 'bp-checkins-places-entry' ) ?>
		<?php endwhile; ?>
	 <?php endif;
	exit;
}

add_action('wp_ajax_post_places', 'bp_checkins_post_places');
add_action('wp_ajax_nopriv_post_places', 'bp_checkins_post_places');

function bp_checkins_handle_checkin_upload() {
	
	if( $_POST['encodedimg'] ) {
		
		if($_POST['type_upload'] == 'checkin' )
			check_admin_referer( 'post_checkin', '_wpnonce_post_checkin' );
		else
			check_admin_referer( 'place_post_checkin', '_wpnonce_place_post_checkin' );
			
		
		$imgresponse = array();
		$uploaddir = wp_upload_dir();
		
		$img = $_POST['encodedimg'];
		$img = str_replace('data:'.$_POST['imgtype'].';base64,', '', $img);
		$img = str_replace(' ', '+', $img);
		$data = base64_decode($img);
		
		$imgname = wp_unique_filename( $uploaddir['path'], $_POST['imgname'] );
		
		$filepath = $uploaddir['path'] . '/' . $imgname;
		$fileurl = $uploaddir['url'] . '/' . $imgname;
		$success = file_put_contents($filepath, $data);
		
		if($success){
			$imgresponse[0] = "1";
			$imgresponse[1] = $fileurl;
			
			$size = @getimagesize( $filepath );

			/* Check image size and shrink if too large */
			if ( $size[0] > 100 ) {
				
				$editor = wp_get_image_editor( $filepath );
				if ( is_wp_error( $editor ) )
					return $editor;
				$editor->set_quality( 90 );
				
				$resized = $editor->resize( 100, 100, true );
				
				if ( is_wp_error( $resized ) ) {
					$imgresponse[0] = "0";
					$imgresponse[1] = sprintf( __( 'Upload Failed! Error was: %s', 'bp-checkins' ), $resized->get_error_message() );
					echo json_encode( $imgresponse );
					die();
				}

				$dest_file = $editor->generate_filename( null, null );
				$thumb = $editor->save( $dest_file );
				
				if ( is_wp_error( $thumb ) ){
					$imgresponse[0] = "0";
					$imgresponse[1] = sprintf( __( 'Upload Failed! Error was: %s', 'bp-checkins' ), $thumb->get_error_message() );
					echo json_encode( $imgresponse );
					die();
				}
				
				$image_resized = $thumb['file'];

				if ( !empty( $image_resized ) ){
					$imgresponse[2] = $uploaddir['url'] . '/'. $image_resized;
				}
			}
			
		} else {
			$imgresponse[0] = "0";
			$imgresponse[1] = __('Upload Failed! Unable to write the image on server', 'bp-checkins');
			
		}
		
	}
	else {
		$imgresponse[0] = "0";
		$imgresponse[1] = __('Upload Failed! No image sent', 'bp-checkins');
		
	}
	
	echo json_encode( $imgresponse );
	die();
}

add_action('wp_ajax_upload_checkin_pic', 'bp_checkins_handle_checkin_upload');
add_action('wp_ajax_nopriv_upload_checkin_pic', 'bp_checkins_handle_checkin_upload');

function bp_checkins_handle_places_checkin() {
	global $bp;
	
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'place_post_checkin', '_wpnonce_place_post_checkin' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['bpci-lat'] ) || empty( $_POST['bpci-lng'] ) || empty( $_POST['bpci-address'] ) || empty( $_POST['place_id'] ) ) {
		echo __( 'Oops something went wrong, please try again.', 'bp-checkins' );
		return false;
	}
	
	$place_id = intval($_POST['place_id']);
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
	$place_name = esc_attr( $_POST['bpci-address'] );
	
	if( !empty( $_POST['place'] ) )
		$place_name = esc_attr( $_POST['place'] );

	$activity_id = 0;
	if ( empty( $group_id ) && bp_is_active( 'checkins' ) ) {
		$activity_id = bp_checkins_post_update( array( 'type' => 'place_checkin', 'place_id' => $place_id, 'place_name' => $place_name ) );

	} else {
			$activity_id = bp_checkins_groups_post_update( array( 'group_id' => $group_id, 'type' => 'place_checkin', 'place_id' => $place_id, 'place_name' => $place_name ) );
	}

	if ( empty( $activity_id ) ) {
		echo __( 'There was a problem posting your checkin, please try again.', 'bp-checkins' ) . '</p></div>';
		return false;
	} else {
		// update checkins count and list of users for this place
		bp_checkins_places_update_checkins_meta( $bp->loggedin_user->id, $place_id );
		// update user meta for user & cookie ?
		bp_checkins_places_user_checkins_transcient( $bp->loggedin_user->id, $place_id );
		// insert user in group if not a member ?
		echo 'checkedin';
	}
	exit;
}

add_action('wp_ajax_places_simply_checkin', 'bp_checkins_handle_places_checkin');
add_action('wp_ajax_nopriv_places_simply_checkin', 'bp_checkins_handle_places_checkin');

function bp_checkins_autocomplete_place_src() {
	$src = $_REQUEST['q'];
	
	if ( bp_checkins_has_places ( 'src=' . $src ) ) : ?>
		<?php while ( bp_checkins_has_places() ) : bp_checkins_the_place(); ?>
			<li><?php bp_checkins_places_avatar(); ?><a href="<?php bp_checkins_places_the_permalink();?>" title="<?php bp_checkins_places_title(); ?>" class="rez-place" onclick="javascript:launch('<?php bp_checkins_places_the_permalink();?>')"><?php bp_checkins_places_title();?></a></li>
		<?php endwhile; ?>
	 <?php endif;
	die();
}

add_action('wp_ajax_autocomplete_place_src', 'bp_checkins_autocomplete_place_src');
add_action('wp_ajax_nopriv_autocomplete_place_src', 'bp_checkins_autocomplete_place_src');

function bp_checkins_live_get_new_comments() {
	
	if( empty( $_POST['place_id'] ) ) {
		$result['contents'] = 0;
		echo json_encode( $result );
		return false;
	}
	
	$all_count = get_comment_count( $_POST['place_id'] );
	
	$comments = array();
	$begin = $end = false;
	
	if( !empty( $_POST['displayed_count'])  ) {
		
		$allready_displayed = $_POST['displayed_count'];
		$new = $all_count['approved'] - $allready_displayed;
		
		if( $new >= 1 )
			$comments = get_comments( array('post_id' => $_POST['place_id'], 'offset'=>$new, 'number'=>$new, 'order' => 'ASC') );
			
	} else if( $all_count['approved'] >= 1 && empty( $_POST['displayed_count'] )  ) {
		$comments = get_comments( array('post_id' => $_POST['place_id']) );
	}
	
	if( count( $comments ) >= 1) {
		add_filter('comment_class', 'bp_checkins_add_live_comment_class');
		
		$result = array();
		
		if( empty( $_POST['displayed_count'] ) ) {
			$begin = '<div id="comments"><h3>' . __( '<span>1</span> response(s)', 'bp-checkins' ) .'</h3><ol class="commentlist">';
			$end = '</ol></div>';
			$result['add_div'] = 1;
		}
		
		ob_start();
		wp_list_comments( array( 'callback' => 'bp_checkins_list_comments', 'type' => 'comment', 'reverse_top_level' => 'ASC' ), $comments );
		
		$result['contents'] = $begin . ob_get_contents() . $end;
		$result['comment_count'] = $all_count['approved'];
		ob_end_clean();
	} else {
		$result['contents'] = 0;
	}

	echo json_encode( $result );
	exit;
}

add_action('wp_ajax_place_live_comments', 'bp_checkins_live_get_new_comments');
add_action('wp_ajax_nopriv_place_live_comments', 'bp_checkins_live_get_new_comments');

function bp_checkins_place_delete() {

	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'bp_checkins_place_delete_link' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['place_id'] ) || !is_numeric( $_POST['place_id'] ) ) {
		echo '-1';
		return false;
	}

	$place = new BP_Checkins_Place( (int) $_POST['place_id'] );
	
	if( empty( $place->query->post->post_author ) || !bp_checkins_places_user_can_delete( $place->query->post ) ) {
		echo '-1';
		return false;
	}
	
	do_action( 'bp_checkins_before_place_delete', $place->query->post->ID );

	$notice = bp_checkins_delete_place( $place->query->post->ID );

	if ( !$notice || $notice['type'] != 'updated' ) {
		echo '-1<div id="message" class="error"><p>' . $notice['message'] . '</p></div>';
		return false;
	}

	do_action( 'bp_checkins_after_place_delete', $place->query->post->ID );

	return true;
	exit;
}
add_action( 'wp_ajax_place_delete', 'bp_checkins_place_delete' );
add_action( 'wp_ajax_nopriv_place_delete', 'bp_checkins_place_delete' );
?>