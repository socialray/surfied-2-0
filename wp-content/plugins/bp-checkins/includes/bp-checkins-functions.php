<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


function bp_checkins_is_user_area() {
	if( bp_is_current_component('checkins') && bp_displayed_user_id() ) {
		return true;
	}
	else
		return false;
}

function bp_checkins_if_single_place() {
	if ( !bp_displayed_user_id() && bp_is_current_component( 'checkins' ) &&  bp_is_current_action('place') && bp_action_variable( 0 ) && 'category' != bp_action_variable( 0 ) ) {
		return true;
	} else {
		return false;
	}
}

function bp_checkins_is_single_place() {
	global $wp_query;
	if ( bp_checkins_if_single_place() ) {
		$wp_query->is_404 = false;
		$wp_query->is_singular = true;
		return true;
	}
		
	else
		return false;
}

function bp_checkins_if_place_home() {
	if ( !bp_displayed_user_id() && bp_is_current_component( 'checkins' ) &&  bp_is_current_action('place') )
		return true;
	else
		return false;
}

function bp_checkins_is_place_home() {
	global $wp_query;
	if ( bp_checkins_if_place_home() ) {
		$wp_query->is_404 = false;
		$wp_query->is_page = true;
		return true;
	}
		
	else
		return false;
}

function bp_checkins_if_category_place() {
	if ( !bp_displayed_user_id() && bp_is_current_component( 'checkins' ) &&  bp_is_current_action('place') && bp_action_variable( 0 ) && 'category' == bp_action_variable( 0 ) && bp_action_variable( 1 ) )
		return true;
	else
		return false;
}

function bp_checkins_is_category_place() {
	global $wp_query;
	if ( bp_checkins_if_category_place() ) {
		$wp_query->is_404 = false;
		$wp_query->is_archive = true;
		return true;
	}
		
	else
		return false;
}


function bp_checkins_is_group_places_area() {
	if( bp_is_groups_component() && bp_is_single_item() && bp_is_current_action( 'checkins' ) && bp_action_variable( 0 ) == 'places' )
		return true;
		
	else return false;
}

function bp_checkins_group_can_checkin() {
	
	if( groups_get_groupmeta( bp_get_group_id(), 'checkins_ok' ) )
		return true;
	else
		return false;
}

function bp_checkins_ajax_querystring( $object = false, $action = false ) {
	global $bp;
	
	if( empty( $object ) ){
		$object = 'places';
	}	
		
	if ( empty( $bp->checkins->ajax_query ) )
		$bp->checkins->ajax_query = 'object='.$object.'&type='.$action.'&action='.$action ;

	return apply_filters( 'bp_checkins_ajax_querystring', $bp->checkins->ajax_query, $object, $action );
}

function bp_checkins_post_update( $args = '' ) {
	global $bp;

	$defaults = array(
		'content'       => false,
		'user_id'       => $bp->loggedin_user->id, 
		'type'          => 'checkin',
		'place_id'      => false,
		'place_name'    => false,
		'comment_id'    => false,
		'recorded_time' => bp_core_current_time()
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( $type == "checkin" && ( empty( $content ) || !strlen( trim( $content ) ) ) )
		return false;

	if ( bp_is_user_spammer( $user_id ) || bp_is_user_deleted( $user_id ) )
		return false;

	// Record this on the user's profile
	$from_user_link   = bp_core_get_userlink( $user_id );
	
	$component = 'checkins';
	
	if( $type == 'checkin' ){
		
		$activity_action  = sprintf( __( '%s added a checkin', 'bp-checkins' ), $from_user_link );
		$activity_content = $content;
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'activity_checkin';
		$item_id = false;
		$secondary_item_id = false;
		
	} else if( $type == 'new_place' && !empty( $place_id ) ) {

		$component = 'places';
		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%s added a new place %s', 'bp-checkins' ), $from_user_link, $place_permalink );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'new_place';
		$item_id = $place_id;
		$activity_content = $content;
		$secondary_item_id = false;

	} else if( $type == 'place_checkin' && !empty( $place_id ) ) {
		
		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%s checked-in %s', 'bp-checkins' ), $from_user_link, $place_permalink );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_checkin';
		$item_id = $place_id;
		$activity_content = false;
		$secondary_item_id = false;
		
	} else if( $type == 'place_comment' && !empty( $place_id ) && !empty( $comment_id ) ) {

		$component = 'places';
		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%s added a comment on %s', 'bp-checkins' ), $from_user_link, $place_permalink );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_comment';
		$activity_content = $content;
		$item_id = $place_id;
		$secondary_item_id = $comment_id;
		
	} else if( $type == 'place_checkin_comment' && !empty( $place_id ) && !empty( $comment_id ) ) {

		$component = 'places';
		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%s checked-in and added a comment on %s', 'bp-checkins' ), $from_user_link, $place_permalink );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_comment';
		$activity_content = $content;
		$item_id = $place_id;
		$secondary_item_id = $comment_id;
		
	}
	

	// Now write the values
	$activity_id = bp_activity_add( array(
		'user_id'           => $user_id,
		'action'            => apply_filters( 'bp_activity_new_update_action', $activity_action ),
		'content'           => apply_filters( 'bp_activity_new_update_content', $activity_content ),
		'primary_link'      => apply_filters( 'bp_activity_new_update_primary_link', $primary_link ),
		'component'         => $component,
		'type'              => $checkin_type, 
		'item_id'	        => $item_id,
		'secondary_item_id' => $secondary_item_id,
		'recorded_time'     => $recorded_time
	) );
	
	if( $type == 'checkin' )
		bp_update_user_meta( $bp->loggedin_user->id, 'bp_latest_update', array( 'id' => $activity_id, 'content' => wp_filter_kses( $content ) ) );
		
	if( $checkin_type == 'place_comment' )
		update_comment_meta( $comment_id, 'group_place_activity_id', $activity_id );

	do_action( 'bp_activity_posted_checkin', $content, $user_id, $activity_id );

	return $activity_id;
}

function bp_checkins_groups_post_update( $args = '' ) {
	global $bp;

	$defaults = array(
		'content'       => false,
		'user_id'       => $bp->loggedin_user->id,
		'group_id'      => 0,
		'type'          => 'checkin', 
		'place_id'      => false,
		'place_name'    => false,
		'comment_id'    => false,
		'recorded_time' => bp_core_current_time()
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( empty( $group_id ) && !empty( $bp->groups->current_group->id ) )
		$group_id = $bp->groups->current_group->id;
		
	if ( $type == "checkin" && ( empty( $content ) || !strlen( trim( $content ) ) ) )
		return false;

	if ( empty( $user_id ) || empty( $group_id ) )
		return false;

	$bp->groups->current_group = new BP_Groups_Group( $group_id );

	// Be sure the user is a member of the group before posting.
	if ( !is_super_admin() && !groups_is_user_member( $user_id, $group_id ) )
		return false;
		
	$from_user_link   = bp_core_get_userlink( $user_id );
		
	if( $type == 'checkin' ){
		
		$activity_action  = sprintf( __( '%1$s added a checkin in the group %2$s', 'bp-checkins'), $from_user_link, '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
		$activity_content = $content;
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'activity_checkin';
		$item_id = $group_id;
		$secondary_item_id = false;
		
	} else if( $type == 'new_place' && !empty( $place_id ) ) {

		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%1$s added a new place %2$s in the group %3$s', 'bp-checkins' ), $from_user_link, $place_permalink, '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'new_place';
		$item_id = $group_id;
		$activity_content = $content;
		$secondary_item_id = $place_id;
		
	} else if( $type == 'place_checkin' && !empty( $place_id ) ) {
		
		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%1$s checked-in %2$s in the group %3$s', 'bp-checkins' ), $from_user_link, $place_permalink, '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_checkin';
		$item_id = $group_id;
		$activity_content = false;
		$secondary_item_id = $place_id;
		
	} else if( $type == 'place_comment' && !empty( $place_id ) && !empty( $comment_id ) ) {

		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%1$s added a comment on %2$s in the group %3$s', 'bp-checkins' ), $from_user_link, $place_permalink, '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_comment';
		$activity_content = $content;
		$item_id = $group_id;
		$secondary_item_id = $place_id;

	} else if( $type == 'place_checkin_comment' && !empty( $place_id ) && !empty( $comment_id ) ) {

		$place_permalink = '<a href="' . bp_get_checkins_places_the_permalink( $place_id ) .'" title="'.$place_name.'">'.$place_name.'</a>';
		$activity_action  = sprintf( __( '%1$s checked-in and added a comment on %2$s in the group %3$s', 'bp-checkins' ), $from_user_link, $place_permalink, '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
		$primary_link     = bp_core_get_userlink( $user_id, false, true );
		$checkin_type = 'place_comment';
		$activity_content = $content;
		$item_id = $group_id;
		$secondary_item_id = $place_id;

	}

	$activity_id = groups_record_activity( array(
		'user_id'           => $user_id,
		'component'         => 'groups',
		'action'            => apply_filters( 'groups_activity_new_update_action',  $activity_action  ),
		'content'           => apply_filters( 'groups_activity_new_update_content', $activity_content ),
		'type'              => $checkin_type,
		'item_id'           => $group_id,
		'secondary_item_id' => $secondary_item_id,
		'recorded_time'     => $recorded_time
	) );
	
	if( $type == 'checkin' )
		groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );
		
	if( $checkin_type == 'place_comment' )
		update_comment_meta( $comment_id, 'group_place_activity_id', $activity_id );
		
	do_action( 'bp_groups_posted_checkin', $content, $user_id, $group_id, $activity_id );

	return $activity_id;
}

/**
* Adding a place !
*
*/

function bp_checkins_add_new_place($args = '' ) {
	global $bp;

	$defaults = array(
		'id'              => false,
		'group_id'        => 0,
		'hide_sitewide'   => 0,
		'title'           => false,
		'content'         => false,
		'user_id'         => $bp->loggedin_user->id,
		'address'         => false,
		'lat'             => false,
		'lng'             => false,
		'place_category'  => false,
		'type'            => false,
		'start'           => false,
		'end'             => false
	);
	$params = wp_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	// Setup snippet to be added
	$place                  = new BP_Checkins_Place();
	$place->group_id        = $group_id;
	$place->hide_sitewide   = $hide_sitewide;
	$place->title           = $title;
	$place->content         = $content;
	$place->user_id         = $user_id;
	$place->address         = $address;
	$place->lat             = $lat;
	$place->lng             = $lng;
	$place->place_category  = $place_category;
	$place->type            = $type;
	$place->start           = $start;
	$place->end             = $end;
	
	if ( !$place->save() )
		return false;
		
	do_action( 'bp_checkins_add_new_place', $place->id, $params );

	return $place->id;
}


add_action('bp_checkins_add_new_place', 'bp_checkins_attach_images', 1, 2);

function bp_checkins_attach_images( $place_id, $params ) {
	global $wpdb;
	
	if( empty( $_POST['attached_images'] ) )
		return false;
	
	$images = explode(',', substr($_POST['attached_images'], 0, -1) );
	foreach($images as $image){
		$wpdb->update($wpdb->posts, array( 'post_parent' => $place_id ), array('ID' => $image) );
	}

	//takes care of thumbnail if needed
	if (!empty( $_POST['featured_image'] ) ) update_post_meta( $place_id, '_thumbnail_id', intval($_POST['featured_image']) );
	
}

add_action( 'bp_places_entry_content', 'bp_checkins_display_place_checkin');

function bp_checkins_display_place_checkin(){
		
	$place_id = bp_get_checkins_places_id();
	$place_permalink = bp_get_checkins_places_the_permalink();
	
	$address = get_post_meta( $place_id, 'bpci_places_address', true );
	
	
	if( $address ){
		?>
		<div class="activity-checkin">
			<a href="<?php echo $place_permalink;?>" title="<?php _e('Open the map for this update', 'bp-checkins');?>" class="link-checkin"><span class="update-checkin"><?php echo stripslashes( $address );?></span></a>
		</div>
		<?php
	}
	
}

function bp_checkins_place_map() {
	global $bpci_lat, $bpci_lng;
	$bpci_lat = get_post_meta( get_the_ID(), 'bpci_places_lat', true );
	$bpci_lng = get_post_meta( get_the_ID(), 'bpci_places_lng', true );
	
	if(!empty($bpci_lat) && !empty($bpci_lng)){
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				var bpciPosition = new google.maps.LatLng(<?php echo $bpci_lat;?>,<?php echo $bpci_lng;?>);

				$("#bpci-map").gmap3({
		            action: 'addMarker', 
		            latLng:bpciPosition,
					map:{
						center: true,
						zoom: 16
					}
				},
				{
					action : 'clear',
					name: 'marker'
				},
				{ action:'addOverlay',
		          latLng: bpciPosition,
		          options:{
		            content: '<div class="bpci-avatar"><s></s><i></i><span>' + $(".places-avatar").html() + '</span></div>',
		            offset:{
		              y:-40,
		              x:10
		            }
		          }
				});
			});

		</script>
		<?php
	}

}

function bp_checkins_places_geo_fields() {
	global $bpci_lat, $bpci_lng;
	if(!empty($bpci_lat) && !empty($bpci_lng)) {
		
		$place_address = get_post_meta( get_the_ID(), 'bpci_places_address', true );
		
		?>
		<input type="hidden" name="bpci-lat" id="bpci_place_lat" value="<?php echo $bpci_lat;?>">
		<input type="hidden" name="bpci-lng" id="bpci_place_lng" value="<?php echo $bpci_lng;?>">
		<input type="hidden" name="bpci-address" id="bpci_place_address" value="<?php echo esc_attr($place_address);?>">
		<input type="hidden" name="_bpci_place_name" id="bpci_place_name" value="<?php echo esc_attr(get_the_title());?>">
		<input type="hidden" name="_bpci_place_id" id="bpci_place_id" value="<?php echo intval(get_the_ID());?>">
		<input type="hidden" name="_bpci_comment_image_url" id="bpci_comment_image_url">
		<?php wp_nonce_field( 'place_post_checkin', '_wpnonce_place_post_checkin' ); ?>
		<?php
	}
}

function bp_checkins_get_name_from_id( $id ) {
	$place = get_post( $id );
	
	if( $place )
		return $place->post_name;
}

function bp_checkins_places_update_checkins_meta( $user_id, $place_id ) {
	if( empty($user_id) || empty($place_id) )
		return false;
		
	$list_user_checked = get_post_meta($place_id, 'bpci_place_checked_users', true);
	
	if( !empty($list_user_checked) && is_array($list_user_checked) && !in_array( $user_id , $list_user_checked) )
		$list_user_checked[] = $user_id;
		
	else
		$list_user_checked = array( $user_id );
		
	update_post_meta( $place_id, 'bpci_place_checked_users', $list_user_checked );
	
	$count_user_checked = get_post_meta($place_id, 'bpci_place_checked_count', true);
	
	$count_user_checked = !empty( $count_user_checked ) ? (int)$count_user_checked + 1 : 1;
	
	update_post_meta( $place_id, 'bpci_place_checked_count', $count_user_checked);
	
}

function bp_checkins_places_user_checkins_transcient( $user_id, $place_id ) {
	
	if( empty($user_id) || empty($place_id) )
		return false;
	
	$list_place_checkins = get_transient('user_checkedin_'.$user_id );
	
	if( !empty($list_place_checkins) && is_array($list_place_checkins) && !in_array( $place_id , $list_place_checkins) )
		$list_place_checkins[] = $place_id;
		
	else
		$list_place_checkins = array( $place_id );
	
	set_transient('user_checkedin_'.$user_id, $list_place_checkins, 60 * 60 * 12);
}

function bp_checkins_list_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type )
		return false;

	if ( 1 == $depth )
		$avatar_size = 50;
	else
		$avatar_size = 25;
	?>

	<li <?php comment_class() ?> id="comment-<?php comment_ID() ?>">
		<div class="comment-avatar-box">
			<div class="avb">
				<a href="<?php echo get_comment_author_url() ?>" rel="nofollow">
					<?php if ( $comment->user_id ) : ?>
						<?php echo bp_core_fetch_avatar( array( 'item_id' => $comment->user_id, 'width' => $avatar_size, 'height' => $avatar_size, 'email' => $comment->comment_author_email ) ) ?>
					<?php else : ?>
						<?php echo get_avatar( $comment, $avatar_size ) ?>
					<?php endif; ?>
				</a>
			</div>
		</div>

		<div class="comment-content">
			<div class="comment-meta">
				<p>
					<?php
						/* translators: 1: comment author url, 2: comment author name, 3: comment permalink, 4: comment date/timestamp*/
						printf( __( '<a href="%1$s" rel="nofollow">%2$s</a> said on <a href="%3$s"><span class="time-since">%4$s</span></a>', 'bp-checkins' ), get_comment_author_url(), get_comment_author(), get_comment_link(), get_comment_date() );
					?>
				</p>
			</div>

			<div class="comment-entry">
				<?php if ( $comment->comment_approved == '0' ) : ?>
				 	<em class="moderate"><?php _e( 'Your comment is awaiting moderation.', 'bp-checkins' ); ?></em>
				<?php endif; ?>
				
				<?php bp_checkins_display_comment_meta( $comment->comment_ID );?>

				<?php comment_text() ?>
				
				<div class="clear"></div>
			</div>

			<div class="comment-options">

					<?php if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) : ?>
						<?php printf( '<a class="button comment-edit-link bp-secondary-action" href="%1$s" title="%2$s">%3$s</a> ', get_edit_comment_link( $comment->comment_ID ), esc_attr__( 'Edit comment', 'bp-checkins' ), __( 'Edit', 'bp-checkins' ) ) ?>
					<?php endif; ?>
					
					<?php if ( bp_checkins_places_comment_can_delete() ) : ?>
						<?php printf( '<a class="bpci-delete-comment button bp-secondary-action confirm" href="%1$s" title="%2$s">%3$s</a> ', bp_checkins_places_get_delete_comment_link( $comment->comment_ID ), esc_attr__( 'Delete comment', 'bp-checkins' ), __( 'Delete', 'bp-checkins' ) ) ?>
					<?php endif; ?>

			</div>

		</div>

<?php
}

function bp_checkins_update_to_checkin_type( $activity_id, $old_activity, $new_type = 'activity_checkin' ) {
	
	if(!empty($activity_id) && !empty( $old_activity ) ) {
		$activity                    = new BP_Activity_Activity( $activity_id );
		$activity->user_id           = $old_activity->user_id;
		$activity->component         = $old_activity->component;
		$activity->type              = $new_type;
		$activity->action            = $old_activity->action;
		$activity->content           = $old_activity->content;
		$activity->primary_link      = $old_activity->primary_link;
		$activity->item_id           = $old_activity->item_id;
		$activity->secondary_item_id = $old_activity->secondary_item_id;
		$activity->date_recorded     = $old_activity->date_recorded;
		$activity->hide_sitewide     = $old_activity->hide_sitewide;
		
		$test = $activity->save();
		
	}
}

function bp_checkins_get_places_filter() {
	$filter  = '<li id="places-filter-select" class="last">';
	$filter .= '<label for="places-filter-by">'. __( 'Show:', 'bp-checkins' ).'</label>';
	$filter .= '<select id="places-filter-by">';
	$filter .= '<option value="-1">'. __( 'Everything', 'bp-checkins' ) .'</option>';
	$filter .= '<option value="all_live_places">'. __( 'Live Places', 'bp-checkins' ) .'</option>';
	$filter .= '<option value="upcoming_places">'. __( 'Upcoming Places', 'bp-checkins' ) .'</option>';
	if ( is_user_logged_in() )
		$filter .= '<option value="places_around">'. __( 'Places around', 'bp-checkins' ) .'</option>';
		
	$filter .= '<option value="browse_search">'. __( 'Search or Browse by category', 'bp-checkins' ) .'</option>';
	$filter .= '</select>';
	$filter .= '</li>';
	
	return apply_filters( 'bp_checkins_get_places_filter', $filter);
}

/**
* use wordpress date function to be sure of the timezone
*/
function bp_checkins_date( $timestamp = false ) {
	
	$current_offset = get_option('gmt_offset');
	$tzstring = get_option('timezone_string');
	
	if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
		$check_zone_info = false;
		if ( 0 == $current_offset )
			$tzstring = 'UTC+0';
		elseif ($current_offset < 0)
			$tzstring = 'UTC' . $current_offset;
		else
			$tzstring = 'UTC+' . $current_offset;
	}
	
	date_default_timezone_set($tzstring);
	
	if( empty( $timestamp ) )
		$timestamp = time();
	
	return date_i18n( 'Y-m-d H:i:s', $timestamp );
	
}

/**************** Deleting Places & Places comment *******************/

/* if superadmin is trashing ! */
function bp_checkins_trashed_place( $place_id ){
	
	if( empty($place_id) )
		return false;
		
	$place = get_post( $place_id );
	
	if( !in_array( $place->post_type, array( 'places' ) ) )
		return false;
		
	/* do we have a group_id ? */
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
	
	if( !empty($group_id) && $group_id != 0 ) {
		bp_activity_delete( array( 'component' => 'groups', 'type' => 'new_place', 'item_id' => $group_id, 'secondary_item_id' => $place_id ) );
		// second comments
		bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_comment', 'item_id' => $group_id, 'secondary_item_id' => $place_id ) );
		//finally checkins
		bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_checkin', 'item_id' => $group_id, 'secondary_item_id' => $place_id ) );
	} else {
		bp_activity_delete( array( 'component' => 'places', 'item_id' => $place_id ) );
		bp_activity_delete( array( 'component' => 'checkins', 'type' => 'place_checkin', 'item_id' => $place_id ) );
	}
	
}
add_action( 'trashed_post', 'bp_checkins_trashed_place', 10, 1 );


/* if he changes his mind */
function bp_checkins_untrashed_place( $place_id ){
	// we need to rebuilt the activity type new_place and its activity_meta
	if( empty($place_id) )
		return false;
		
	$place = get_post( $place_id );
	
	if( !in_array( $place->post_type, array( 'places' ) ) )
		return false;
		
	/* do we have a group_id ? */
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
	
	/* let's build the activity vars */
	$place_name = esc_attr($place->post_title);
	$thumbnail = bp_get_checkins_places_featured_image( $place_id );
	$excerpt = bp_get_checkins_places_excerpt( $place->post_content );
	$place_content = apply_filters('bp_checkins_place_content_before_activity', $thumbnail . $excerpt );
	
	$args = array('content' => $place_content, 'user_id' => $place->post_author, 'type' => 'new_place', 'place_id' => $place_id,  'place_name' => $place_name, 'recorded_time' => $place->post_date_gmt);
	
	if( !empty($group_id) && $group_id != 0 ) {
		// adding the group_id to args.
		$args['group_id'] = $group_id;
		
		$activity_id = bp_checkins_groups_post_update( $args );
		
	} else {
		
		$activity_id = bp_checkins_post_update( $args );
		
	}
	if( !empty( $activity_id ) ) {
		/* 
		* now we store the post meta as activity meta 
		* this way if cookies of superadmin were writen
		* we'll be sure to have the correct values.
		*/
		
		$lat = get_post_meta( $place_id, 'bpci_places_lat', true );
		$lng = get_post_meta( $place_id, 'bpci_places_lng', true );
		$address = get_post_meta( $place_id, 'bpci_places_address', true );
		
		if( empty( $lat) || empty( $lng ) || empty( $address ) )
			return false;
		
		bp_activity_update_meta( $activity_id, 'bpci_activity_lat', $lat );
		bp_activity_update_meta( $activity_id, 'bpci_activity_lng', $lng );
		bp_activity_update_meta( $activity_id, 'bpci_activity_address', $address );
	}
	
}

add_action('untrashed_post', 'bp_checkins_untrashed_place', 10, 1);

/*
* as we'll need this code twice, let's put it in a fonction!
* returns args for activity recording
*/
function bp_checkins_place_comment_restore( $comment_content, $user_id, $place_id, $comment_id, $place_name, $recorded_time ){
	
	if( empty( $comment_content ) || empty( $user_id ) || empty( $place_id ) || empty( $comment_id ) || empty( $place_name ) || empty( $recorded_time ) )
		return false;
	
	$args = array();
	$content = "";
	$content = wp_kses( $comment_content, array() );
	$content = bp_create_excerpt( $content, 180, array( 'filter_shortcodes' => true) );
	$content = get_comment_meta( $comment_id, '_bpci_comment_image', true ) . $content;
	
	$args = array('content' => $content, 
				  'user_id' => $user_id, 
				  'type' => 'place_comment', 
				  'place_id' => $place_id, 
				  'comment_id' => $comment_id, 
				  'place_name' => $place_name, 
				  'recorded_time' => $recorded_time );
				
	/* do we have a group_id ? */
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
	
	if( !empty($group_id) && $group_id != 0 ) {
		$args['group_id'] = $group_id;
		
		$activity_id = bp_checkins_groups_post_update( $args );
		
	} else {
		$activity_id = bp_checkins_post_update( $args );
	}
	/* as the old activity was deleted during place trash, set a new activity_id */
	if( !empty( $activity_id ) )
		update_comment_meta( $comment_id, 'group_place_activity_id', $activity_id );
		
	return $activity_id;
	
}

/* restore comments if place is untrashed */
function bp_checkins_place_untrashed_comment( $place_id ) {
	global $wpdb;
	
	if( empty($place_id) )
		return false;
	
	$place = get_post( $place_id );
	
	if( !in_array( $place->post_type, array( 'places' ) ) )
		return false;
		
	$place_name = esc_attr($place->post_title);
		
	/* do we have a group_id ? */
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true);
		
	$comments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d", $place_id) );
	
	if ( empty($comments) )
		return;
		
	foreach( $comments as $comment) {
		
		if( $comment->comment_approved == 1 )
			bp_checkins_place_comment_restore( $comment->comment_content, $comment->user_id, $place_id, $comment->comment_ID, $place_name, $comment->comment_date_gmt );

	}
	
}
add_action('untrashed_post_comments', 'bp_checkins_place_untrashed_comment', 10 , 1);


function bp_checkins_place_remove_comment( $comment_id ) {
	
	if( empty( $comment_id ) )
		return false;
	
	$comment = get_comment( $comment_id );
	
	if( empty( $comment ) )
		return $comment_id;
	
	$place = get_post( $comment->comment_post_ID );
	
	if( !in_array( $place->post_type, array( 'places' ) ) )
		return false;
		
	$activity_id = get_comment_meta( $comment_id, 'group_place_activity_id', true);
		
	if( !empty( $activity_id) )
		$args = array('id' => $activity_id);
	else
		$args = array( 'component' => 'places', 'type' => 'place_comment', 'secondary_item_id' => $comment_id );
	
	bp_activity_delete( $args );
}

function bp_checkins_place_record_comment( $comment_id ) {
	
	if( empty( $comment_id ) )
		return false;
	
	$comment = get_comment( $comment_id );
	
	$place_id = $comment->comment_post_ID;
	$place = get_post( $place_id );
	
	if( !in_array( $place->post_type, array( 'places' ) ) )
		return false;
		
	$place_name = esc_attr($place->post_title);
	
	bp_checkins_place_comment_restore( $comment->comment_content, $comment->user_id, $place_id, $comment_id, $place_name, $comment->comment_date_gmt );
	
}


/* now if a comment changed status to trash we delete the activity just like bp_blogs_manage_comment does for blog posts !*/
function bp_checkins_place_comment_is_in_trash( $comment_id, $comment_status ) {
	if ( 'spam' == $comment_status || 'hold' == $comment_status || 'delete' == $comment_status || 'trash' == $comment_status )
		return bp_checkins_place_remove_comment( $comment_id );

	return bp_checkins_place_record_comment( $comment_id );
}
add_action( 'wp_set_comment_status', 'bp_checkins_place_comment_is_in_trash', 11, 2 );

/* if delete is performed from front, let's just trash ! */
function bp_checkins_delete_place( $place_id = false ) {
	
	if( $deleted = wp_trash_post( $place_id ) ){
		
		$message = array( 'message' => __('Place has been successfully removed', 'bp-checkins'), 'type' => 'updated' );
		
	} else {
		
		$message = array( 'message' => __('Oops something went wrong, place could not be removed', 'bp-checkins'), 'type' => 'error' );
		
	}
	
	return $message;
}

function bp_checkins_delete_place_comment( $comment_id = false ){
	
	$message = false;
	
	if( empty( $comment_id ) )
		return false;
		
	if( $deleted = wp_trash_comment( $comment_id ) ){
		$message = array( 'message' => __('Comment has been successfully removed', 'bp-checkins'), 'type' => 'updated' );
	} else {
		$message = array( 'message' => __('Oops something went wrong, comment couldnot be removed', 'bp-checkins'), 'type' => 'error' );
	}
	
	return $message;
}

function bp_checkins_group_disabled_checkins( $group_id = false ) {
	global $bp;
	
	$hide_sitewide = 0;
		
	if ( empty( $group_id ) && !empty( $bp->groups->current_group->id ) )
		$group_id = $bp->groups->current_group->id;
	
	else
		$bp->groups->current_group = new BP_Groups_Group( $group_id );
		
	if ( isset( $bp->groups->current_group->status ) && 'public' != $bp->groups->current_group->status )
		$hide_sitewide = 1;
	
	/* let's delete activities */
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'new_place', 'item_id' => $group_id ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_comment', 'item_id' => $group_id ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_checkin', 'item_id' => $group_id ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'activity_checkin', 'item_id' => $group_id ) );
	
	$deleted_places = BP_Checkins_Place::delete( array('group_id' => $group_id, 'hide_sitewide' => $hide_sitewide) );
	
	return $deleted_places;
}

function bp_checkins_user_is_deleted( $user_id = false ) {
	if( empty( $user_id ) )
		return false;
		
	$deleted_places = BP_Checkins_Place::delete( array('user_id' => $user_id ) );
	
	return $deleted_places;
}


function bp_checkins_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = "string" ) {
	global $bp;
	
	// Set up the string and the filter
	if ( (int)$total_items > 1 ) {
		$link = bp_loggedin_user_domain() . BP_CHECKINS_SLUG . '/places-area/?n=1';
		$text = sprintf( __( '%d new comments on your place(s)', 'bp-checkins' ), (int)$total_items );
		$filter = 'bp_checkins_place_multiple_comments';
	} else {
		
		if(is_numeric( $secondary_item_id ) && $secondary_item_id > 0 ) {
			$link = get_comment_link( $secondary_item_id );
			$link = str_replace( '#comment', '?n=1#comment', $link);
		}
		else
			$link = get_permalink( $item_id ) . '?n=1';
			
			
		$text = __( '1 new comment on your place', 'bp-checkins' );
		$filter = 'bp_checkins_place_single_comment';
	}

	// Return either an HTML link or an array, depending on the requested format
	if ( 'string' == $format ) {
		$return = apply_filters( $filter, '<a href="' . $link . '">' . $text . '</a>', (int)$total_items );
	} else {
		$return = apply_filters( $filter, array(
			'link' => $link,
			'text' => $text
		), (int)$total_items );
	}

	do_action( 'bp_checkins_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}


function bp_checkins_is_bp_default() {
	if( in_array( 'bp-default', array( get_stylesheet(), get_template() ) ) )
        return true;

    else if( current_theme_supports( 'buddypress') )
    	return true;

	else if( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7-beta1-6797', '<' ) )
    	return true;

    else
        return false;
}

?>