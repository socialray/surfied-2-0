<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function bp_checkins_root_slug() {
	echo bp_get_checkins_root_slug();
}
	function bp_get_checkins_root_slug() {
		global $bp;

		$bp_checkins_root_slug = isset( $bp->checkins->root_slug ) ? $bp->checkins->root_slug : BP_CHECKINS_SLUG;

		return apply_filters( 'bp_get_checkins_slug', $bp_checkins_root_slug );
	}
	
function bp_checkins_slug() {
	echo bp_get_checkins_slug();
}
	function bp_get_checkins_slug() {
		global $bp;

		$bp_checkins_slug = isset( $bp->checkins->slug ) ? $bp->checkins->slug : BP_CHECKINS_SLUG;

		return apply_filters( 'bp_get_checkins_slug', $bp_checkins_slug );
	}
	
function bp_checkins_places_home() {
	echo bp_get_checkins_places_home();
}
	function bp_get_checkins_places_home() {
		return apply_filters( 'bp_get_checkins_places_home', bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/place' );
	}

function bp_checkins_display_wp_editor() {
	global $allowedtags;
	
	$place_description = !empty( $_POST["_place_description"] ) ? wp_kses(stripslashes($_POST["_place_description"]), $allowedtags) : false;
	
	$args = array(
		'wpautop' => true,
		'media_buttons' => false,
		'textarea_name' => '_place_description',
		'textarea_rows' => get_option('default_post_edit_rows', 10),
		'teeny' => false,
		'dfw' => false,
		'tinymce' => true,
		'quicktags' => false
	);
	wp_editor( $place_description, "place_content", $args );
}


function bp_checkins_place_title_field() {
	echo bp_get_checkins_place_title_field();
}
	function bp_get_checkins_place_title_field() {
		
		return apply_filters('bp_get_checkins_place_title_field', '<p><input type="text" id="bp-checkins-place-title" name="bp-checkins-place-title" placeholder="'.__('Name of your place', 'bp-checkins').'"/></p>');
		
	}
	
function bp_checkins_place_geolocate() {
	?>
	<input type="hidden" name="bpci-place-lat" id="bpci-place-lat">
	<input type="hidden" name="bpci-place-lng" id="bpci-place-lng">
	<input type="text" id="bpci-place-address" name="bpci-place-address" placeholder="<?php _e('Address of your place','bp-checkins');?>">
	<a href="#" id="bpci-place-show-on-map" class="map-action" title="<?php _e('Search address','bp-checkins');?>"><span><?php _e('Search address','bp-checkins');?></span></a>
	<div id="bpci-place-map" class="map-hide"></div>
	<?php
}

function bp_checkins_place_display_cats() {
	echo bp_get_checkins_place_display_cats();
}

	function bp_get_checkins_place_display_cats() {
		$bp_checkins_cat_taxo = get_terms('places_category', 'orderby=count&hide_empty=0');
		$output = "";

		if( count($bp_checkins_cat_taxo) >= 0 ){
			
			$output .= '<h5><label id="bp_checkins_place_category_label" for="bp_checkins_place_category">'. __('Choose a category for your place', 'bp-checkins'). '</label></h5>';
			$output .= '<ul class="bp-checkins-form-ul">';
			
			foreach($bp_checkins_cat_taxo as $taxo){
				$output .='<li><input type="radio" name="bp_checkins_place_category" id="bp_checkins_place_category-' . $taxo->term_id .'" value="'. $taxo->term_id .'">' . $taxo->name . '</li>';
			}
				
			$output .= '</ul>';
		}
		
		if( !empty( $output) )
			return apply_filters( 'bp_get_checkins_place_display_cats' , $output, $bp_checkins_cat_taxo );
	}
	
function bp_checkins_place_type() {
	echo bp_get_checkins_place_type();
}

	function bp_get_checkins_place_type() {
		$output = '';
		$output .= '<h5><label for="bp_checkins_place_type">'. __('Choose a type for your place', 'bp-checkins'). '</label></h5>';
		$output .= '<ul class="bp-checkins-type">';
		$output .='<li><input type="radio" name="bp_checkins_place_type" id="bp_checkins_place_type-regular" value="regular" checked>' . __('Regular place', 'bp-checkins') . '</li>';
		$output .='<li><input type="radio" name="bp_checkins_place_type" id="bp_checkins_place_type-live" value="live">' . __('Live place', 'bp-checkins') . '</li>';
		$output .= '</ul>';
		$output .= '<div id="bp_checkins_place_time" class="bp-checkins-hide">
					<h5><label id="bp_checkins_place_start_time_label" for="bp_checkins_place_start_time">'. __('Define the begining date', 'bp-checkins'). '</label></h5>
					<input type="date" name="bp_checkins_place_start_date" id="bp_checkins_place_start_date" placeholder="'.__('YYYY-MM-DD', 'bp-checkins').'" class="bp-checkins-time-field" min="'.date('Y-m-d').'">
					<input type="time" name="bp_checkins_place_start_time" id="bp_checkins_place_start_time" placeholder="'.__('HH:MM', 'bp-checkins').'" class="bp-checkins-time-field">
					<h5><label id="bp_checkins_place_end_time_label" for="bp_checkins_place_end_time">'. __('Define the ending date', 'bp-checkins'). '</label></h5>
					<input type="date" name="bp_checkins_place_end_date" id="bp_checkins_place_end_date" placeholder="'.__('YYYY-MM-DD', 'bp-checkins').'" class="bp-checkins-time-field" min="'.date('Y-m-d').'">
					<input type="time" name="bp_checkins_place_end_time" id="bp_checkins_place_end_time" placeholder="'.__('HH:MM', 'bp-checkins').'" class="bp-checkins-time-field">
					</div>';
					
		return apply_filters( 'bp_get_checkins_place_type', $output);
	}
	
	
	
function bp_checkins_has_places( $args = '' ) {
	global $bp, $places_template;
	
	$place_in_cache = false;

	// This keeps us from firing the query more than once
	if ( empty( $places_template ) ) {
		
		/***
		 * Set the defaults for the parameters you are accepting via the "bp_checkins_has_places()"
		 * function call
		 */
		$defaults = array(
			'id'              => false,
			'p'               => false,
			'group_id'        => false,
			'user_id'         => false,
			'per_page'        => 10,
			'paged'		      => 1,
			'type' 			  => false,
			'places_category' => false,
			'src'             => false
		);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		
		if( bp_checkins_is_group_places_area() ) {
			$group_id = $bp->groups->current_group->id;
		} else {
			if( bp_action_variable( 0 ) && bp_action_variable( 0 ) !="category" ) {
				
				$p = bp_action_variable( 0 );
				$place = wp_cache_get( 'single_query', 'bp_checkins_single' );
				
				if( false !== $place  && $place->query->post->post_name == $p )
					$place_in_cache = $place;
			} else {
				if( false !== wp_cache_get( 'single_query', 'bp_checkins_single' ) )
					wp_cache_delete( 'single_query', 'bp_checkins_single' );
			}
				
			if( bp_action_variable( 0 ) && bp_action_variable( 0 ) == "category" && bp_action_variable( 1 ) )
				$places_category = bp_action_variable( 1 );
				
			if( bp_checkins_is_user_area() && bp_is_current_action('places-area') )
				$user_id = $bp->displayed_user->id;
		}
		
		if( empty( $place_in_cache ) ) {
			
			$places_template = new BP_Checkins_Place();

			if( !empty($src) )
				$places_template->get( array( 'per_page' => $per_page, 'search' => $src ) );
			else
				$places_template->get( array( 'id' => $id, 'p' => $p, 'group_id' => $group_id, 'user_id' => $user_id, 'per_page' => $per_page, 'paged' => $paged, 'type' => $type, 'places_category' => $places_category ) );
			
		} else {
			$places_template = $place_in_cache;
		}
	
		
	}

	return $places_template->have_posts();
}

function bp_checkins_has_more_places() {
	global $places_template;
	
	$total_places = intval( $places_template->query->found_posts );
	$pag_num = intval( $places_template->query->query_vars['posts_per_page'] );
	$pag_page = intval( $places_template->query->query_vars['paged'] );
	
	$remaining_pages = floor( ( $total_places - 1 ) / ( $pag_num * $pag_page ) );
	$has_more_places  = (int)$remaining_pages ? true : false;

	return apply_filters( 'bp_checkins_has_more_places', $has_more_places );
}

function bp_checkins_the_place() {
	global $places_template;
	return $places_template->query->the_post();
}

function bp_checkins_places_was_posted_in_group( $place_id = false ) {
	global $places_template;
	
	if( empty( $place_id ) )
		$place_id = $places_template->query->post->ID;
	
	$group_id = get_post_meta( $place_id, '_bpci_group_id', true );
	
	if( !empty( $group_id ) )
		return true;
		
	else
		return false;
}

function bp_checkins_places_can_publish_in_group( $group_id = false ) {
	global $bp, $places_template;
	
	if( empty( $group_id ) ) {
		if( !empty( $bp->groups->current_group->id ) )
			$group_id = $bp->groups->current_group->id;
		
		else
			$group_id = get_post_meta( $places_template->query->post->ID, '_bpci_group_id', true );
	}
	
	$bp->groups->current_group = new BP_Groups_Group( $group_id );

	// Be sure the user is a member of the group before posting.
	if ( !is_super_admin() && !groups_is_user_member( $bp->loggedin_user->id, $group_id ) )
		return false;
		
	else 
		return true;
}

function bp_checkins_places_group_permalink() {
	echo bp_get_checkins_places_group_permalink();
}

	function bp_get_checkins_places_group_permalink( $group_id = false ) {
		global $bp, $places_template;
	
		if( empty( $group_id ) ) {
			if( !empty( $bp->groups->current_group->id ) )
				$group_id = $bp->groups->current_group->id;
		
			else
				$group_id = get_post_meta( $places_template->query->post->ID, '_bpci_group_id', true );
		}
	
		$bp->groups->current_group = new BP_Groups_Group( $group_id );
	
		return apply_filters('bp_get_checkins_places_group_permalink', '<a href="'.bp_get_group_permalink( $bp->groups->current_group ) .'">'.esc_attr( $bp->groups->current_group->name ).'</a>');
	}

function bp_checkins_places_id() {
	echo bp_get_checkins_places_id();
}

	function bp_get_checkins_places_id() {
		global $places_template;
		
		return apply_filters('bp_get_checkins_places_id', $places_template->query->post->ID);
	}
	
function bp_get_checkins_places_term_info( $type = 'term_id', $slug = false ) {
	global $places_template;
		
	if( empty( $slug ) )
		return false;
			
	$term_object = get_term_by('slug', $slug, 'places_category');
		
	return $term_object->$type;
}
	
function bp_checkins_places_term_id() {
	echo bp_get_checkins_places_term_id();
}
	function bp_get_checkins_places_term_id() {
		
		$term_id = bp_get_checkins_places_term_info('term_id');
		
		return apply_filters('bp_get_checkins_places_term_id', $term_id );
	}
	
function bp_checkins_places_category_title() {
	echo bp_get_checkins_places_category_title();
}
	function bp_get_checkins_places_category_title() {
		global $places_template;

		$title = bp_get_checkins_places_term_info('name');
		
		return apply_filters('bp_get_checkins_places_category_title', $title );
	}
	
function bp_checkins_places_category_link() {
	echo bp_get_checkins_places_category_link();
}

	function bp_get_checkins_places_category_link( $slug = false ) {
		
		if( empty($slug) )
			$slug = bp_get_checkins_places_term_info('slug');
		
		$permalink = bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/place/category/' . $slug;
		
		return apply_filters('bp_get_checkins_places_category_link', $permalink );
	}

function bp_checkins_places_avatar() {
	echo bp_get_checkins_places_avatar();
}
	function bp_get_checkins_places_avatar() {
		
		$term_id = bp_get_checkins_places_term_id();
		
		if( !empty($term_id) ) {
			$term_name = bp_get_checkins_places_category_title();
			$term_link = bp_get_checkins_places_category_link();
		} else {
			
			$place_id = bp_get_checkins_places_id();

			$place_category = get_the_terms( $place_id, 'places_category' );
			
			if(count($place_category) == 1) {
				foreach( $place_category as $cat ) {
					$term_id = $cat->term_id;
					$term_name = $cat->name;
					$term_link = bp_get_checkins_places_category_link($cat->slug);
				}
			}
			
		}
		
		$avatar = bp_checkins_get_place_category_avatar( $term_id );
			
		$output = '<a href="'.$term_link.'" class="places-avatar" title="'.$term_name.'">'.$avatar.'</a>';
		
		return apply_filters('bp_get_checkins_places_avatar', $output);
	}
	
	function bp_checkins_get_place_category_avatar( $term_id ) {
		
		$avatar = false;
		
		$avatar_id = get_metadata( 'places_category', $term_id, 'places_category_thumbnail_id', true);
			
		if( !empty( $avatar_id ) ) {
				$avatar_array = wp_get_attachment_image_src( $avatar_id, $size='thumbnail' );
				$avatar = '<img src="'.$avatar_array[0].'" width="'.$avatar_array[1].'" height="'.$avatar_array[2].'">';
		}
		
		if( !$avatar )
			$avatar = '<img src="'.BP_CHECKINS_PLUGIN_URL_IMG.'/default.png" width="150px" height="150px">';
		
		return $avatar;
	}
	
function bp_checkins_places_browse_cats() {
	$places_category = get_terms( 'places_category', 'orderby=count&hide_empty=0' );
	?>
	<ul>
		<?php if(count($places_category) >= 1): foreach( $places_category as $term ):?>
		<li class="places-term-browsed">
			<a href="<?php echo bp_get_checkins_places_category_link( $term->slug );?>" title="<?php echo $term->name;?>"><?php echo bp_checkins_get_place_category_avatar( $term->term_id );?> <?php echo $term->name;?></a>
		</li>
		<?php endforeach;endif;?>
	</ul>
	<?php
}
	
function bp_checkins_places_featured_image(){
	echo bp_get_checkins_places_featured_image();
}

	function bp_get_checkins_places_featured_image( $place_id = false ) {
	
		if( empty( $place_id) )
			$place_id = bp_get_checkins_places_id();
	
		if( has_post_thumbnail( $place_id ) ) {
		
			$thumbnail_id = get_post_thumbnail_id( $place_id );
		
			$full = wp_get_attachment_image_src( $thumbnail_id, 'full');
		
			$thumb = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail');
		
			$output = '<a href="'.$full[0].'" class="bp-ci-zoompic align-left" rel="nofollow"><img src="'.$thumb[0].'" width="100px" height="100px" class="align-left thumbnail"></a>';
			return apply_filters('bp_checkins_places_featured_image', $output);
		}
	
	}

function bp_checkins_places_the_permalink() {
	echo bp_get_checkins_places_the_permalink();
}

	function bp_get_checkins_places_the_permalink( $id = false ) {
		global $places_template;
		
		if( empty( $id ) )
			$permalink = bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/place/' . $places_template->query->post->post_name . '/';
		else
			$permalink = bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/place/' . bp_checkins_get_name_from_id( $id ) .'/';
	
		return apply_filters('bp_get_checkins_places_the_permalink', $permalink);
}

function bp_checkins_places_action() {
	echo bp_get_checkins_places_action();
}
	function bp_get_checkins_places_action() {
		global $places_template;
		
		$place_id =  $places_template->query->post->ID;
		
		$date_posted = apply_filters('bp_get_checkins_place_posted_time', $places_template->query->post->post_date_gmt);
		
		$time_since = apply_filters( 'bp_checkins_place_time_since', '<span class="time-since">' . bp_core_time_since( $date_posted ) . '</span>' );
		
		$place_author =  $places_template->query->post->post_author;
		$place_group =  get_post_meta( $place_id, '_bpci_group_id', true );
		
		if( $place_group >= 1 ) {
			$group = groups_get_group( array( 'group_id' => $place_group ) );

			// Record this in activity streams
			$place_action  = sprintf( __( '%1$s added a place in the group %2$s', 'bp-checkins'), bp_core_fetch_avatar( array( 'item_id' => $place_author, 'object' => 'user', 'type' => 'thumb', 'width' => 20, 'height' => 20) ) . bp_core_get_userlink( $place_author ), bp_core_fetch_avatar( array( 'item_id' => $place_group, 'object' => 'group', 'type' => 'thumb', 'width' => 20, 'height' => 20) ) . '<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>' );
		} else {
			$place_action  = sprintf( __( '%s added a place', 'bp-checkins'), bp_core_fetch_avatar( array( 'item_id' => $place_author, 'object' => 'user', 'type' => 'thumb', 'width' => 20, 'height' => 20) ) . bp_core_get_userlink( $place_author ) );
		}
		
		$place_action .= ' <a href="'.bp_get_checkins_places_the_permalink().'" class="activity-time-since">'.$time_since.'</a>';
		
		return apply_filters('bp_get_checkins_places_action', '<p>' . $place_action . '</p>');
		
	}

function bp_checkins_places_title() {
	echo bp_get_checkins_places_title();
}

	function bp_get_checkins_places_title() {
		global $places_template;
		
		return apply_filters('bp_get_checkins_places_title', $places_template->query->post->post_title);
	}
	
function bp_checkins_places_is_live() {
	global $places_template;
	
	$place_id =  $places_template->query->post->ID;
	
	if ( "live" == get_post_meta( $place_id, 'bpci_places_is_live', true ) )
		return true;
		
	else
		return false;
	
}

function bp_checkins_places_live_status( $echo = true ) {
	$status = bp_get_checkins_places_live_status();
	
	if( $echo )
		echo '<span class="bpci-live-status '.$status[0].'">' . $status[1] . '</span>';
	else
		return $status[0];
}

	function bp_get_checkins_places_live_status() {
		global $places_template;
	
		$place_id =  $places_template->query->post->ID;
	
		$start = get_post_meta( $place_id, 'bpci_places_live_start', true );
		$end = get_post_meta( $place_id, 'bpci_places_live_end', true );
		$start = strtotime($start);
		$end = strtotime($end);
		$now = current_time('timestamp');
		
		if ( $end >= $now && $now >= $start )
			return apply_filters('bp_get_checkins_places_live_status', array('live', __('Place is live!', 'bp-checkins') ) );
		
		if( $now < $start )
			return apply_filters('bp_get_checkins_places_live_status', array('notstarted', sprintf( __( 'Place will be live in %s', 'bp-checkins'), human_time_diff( $now, $start ) ) ) );
		
		if( $now > $end )
			return apply_filters('bp_get_checkins_places_live_status', array('ended', sprintf( __( 'Live ended %s ago', 'bp-checkins'), human_time_diff( $end, $now) ) ) );
}

function bp_get_checkins_places_live_end_date() {
	global $places_template;

	$place_id =  $places_template->query->post->ID;
	
	$end_date = get_post_meta( $place_id, 'bpci_places_live_end', true );
	
	if( !empty( $end_date ) ) {
		$end_date = strtotime($end_date);
		
		echo '<input type="hidden" id="live-end-date" value="'.date_i18n('Y-m-d H:i:s', $end_date).'">';
	}
		
}
	
function bp_checkins_places_content() {
	echo bp_get_checkins_places_content();
}

	function bp_get_checkins_places_content() {
		global $places_template;
		
		$content = apply_filters('the_content', $places_template->query->post->post_content);
		
		return apply_filters('bp_get_checkins_places_content', $content );
	}
	
function bp_checkins_places_excerpt() {
	echo bp_get_checkins_places_excerpt();
}

	function bp_get_checkins_places_excerpt( $post_content = false ) {
		global $places_template;
		
		if( empty($post_content) )
			$post_content = $places_template->query->post->post_content;
		
		$excerpt = apply_filters('get_the_excerpt', wp_kses($post_content, array() ));
		
		$excerpt = bp_create_excerpt( $excerpt, 225, array( 'html' => false, 'filter_shortcodes' => true) );
		
		return apply_filters('bp_get_checkins_places_excerpt', $excerpt);
	}
	
function bp_checkins_places_can_comment() {
	$can_comment = true;
	
	return apply_filters('bp_checkins_places_can_comment', $can_comment);
}

function bp_get_checkins_places_comment_link() {
	if(!is_single())
		$comment_link = bp_get_checkins_places_the_permalink() .'#respond';
	else
		$comment_link = '#respond';
	
	echo apply_filters('bp_get_checkins_places_comment_link', $comment_link);
}

function bp_checkins_places_get_comment_count() {
	global $places_template;
	
	return apply_filters('bp_checkins_places_get_comment_count', $places_template->query->post->comment_count );
}

function bp_checkins_places_get_checkins_count() {
	$place_id = bp_get_checkins_places_id();
	
	$checkins_count = get_post_meta( $place_id, 'bpci_place_checked_count' , true );
	
	return apply_filters('bp_checkins_places_get_checkins_count', $checkins_count );
	
}

function bp_checkins_places_can_favorite() {
	$can_favorite = true;
	
	return apply_filters('bp_checkins_places_can_favorite', $can_favorite);
}

function bp_get_checkins_places_is_favorite() {
	return false;
}

function bp_checkins_places_favorite_link() {
	echo "#";
}

function bp_checkins_places_unfavorite_link() {
	echo "#";
}

function bp_checkins_places_user_can_delete( $place = false ) {
	global $places_template, $bp;
	
	if ( empty( $place ) && is_object( $places_template ) )
		$place = $places_template->query->post;
		
	if ( !is_object( $place ) )
		return false;

	$can_delete = false;

	if ( $bp->loggedin_user->is_super_admin )
		$can_delete = true;

	if ( $place->post_author == $bp->loggedin_user->id )
		$can_delete = true;
		
	/* handling group admins */
	$group_id = get_post_meta( $place->ID, '_bpci_group_id', true );
	
	if( !empty( $group_id ) && $group_id > 0 ) {
		if( groups_is_user_admin( $bp->loggedin_user->id, $group_id ) )
			$can_delete = true;
	}
	
	return apply_filters('bp_checkins_places_can_delete', $can_delete);
}

function bp_checkins_places_delete_link() {
	echo bp_get_checkins_places_delete_link();
}

function bp_get_checkins_places_delete_link() {
	global $places_template, $bp;

	$url   = bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/places/delete/' . $places_template->query->post->ID;
	$class = 'delete-place';

	$link = '<a href="' . wp_nonce_url( $url, 'bp_checkins_place_delete_link' ) . '" class="button item-button bp-secondary-action ' . $class . ' confirm" rel="nofollow">' . __( 'Delete', 'bp-checkins' ) . '</a>';
	return apply_filters( 'bp_checkins_places_delete_link', $link );
}

function bp_checkins_places_comment_can_delete() {
	
	$can_delete = bp_checkins_places_user_can_delete();
	
	return apply_filters('bp_checkins_places_comment_can_delete', $can_delete);
}

function bp_checkins_places_get_delete_comment_link( $comment_id = false ) {
	
	$url   = bp_get_root_domain() . '/' . bp_get_checkins_root_slug() . '/comments/delete/' . $comment_id;
	
	$link = wp_nonce_url( $url, 'bp_checkins_comment_delete_link' );
	return apply_filters( 'bp_checkins_places_comment_delete_link', $link );
	
}

function bp_checkins_display_comment_meta( $comment_id ){
	
	$checkins_meta = get_comment_meta( $comment_id, '_bpci_comment_image', true );
	
	if( !empty($checkins_meta) )
		echo $checkins_meta;

}

/**
* Adds a box showing the friends of the loggedin user that checked in a place.
*
* @package BP Checkins
* @since 1.1
*
* @global obj $bp BuddyPress's global object
*/

function bp_checkins_friends_checkedin() {
	echo bp_checkins_get_friends_checkedin();
}

	function bp_checkins_get_friends_checkedin() {
		global $bp;
		
		$output = "";
		
		if ( !(int)bp_get_option( 'bp-checkins-enable-box-checkedin-friends' ) || '' == bp_get_option( 'bp-checkins-enable-box-checkedin-friends' ) )
			return $output;
		
		if( !is_user_logged_in() )
			return $output;
			
		$place_id = bp_get_checkins_places_id();
		$args = array( 'filter' => array('action' => 'place_checkin', 'primary_id' => $place_id ) );
		
		$activities = bp_activity_get( $args );
		$friends_checkin = array();
		
		foreach( $activities['activities'] as $checkedin ) {
			 
			if( $checkedin->user_id != $bp->loggedin_user->id && 'is_friend' == friends_check_friendship_status( $bp->loggedin_user->id, $checkedin->user_id ) ) {
				// as people can checkin several times 1 each 12 hours...
				if( !in_array( $checkedin->user_id, $friends_checkin ) )
					$friends_checkin[] = $checkedin->user_id;
			}
				
		}
		
		shuffle( $friends_checkin );
		
		if( count( $friends_checkin ) >= 1 ) {
			$output = '<br style="clear:both"><div class="checkedin-amigos">'.__('Great! Some of your friends checked in this place.', 'bp-checkins').'<ul>';
			$step = 0;
			$max = apply_filters('bp_checkins_max_friends_checkedin', 5 );
			
			foreach( $friends_checkin as $friend_id ) { 
				
				if( $step == $max )
					break;
				
				$output .= '<li><a href="'. bp_core_get_userlink( $friend_id, false, true ) .'">'. bp_core_fetch_avatar( array( 'item_id' => $friend_id, 'object' => 'user', 'type' => 'thumb', 'class' => 'avatar checkedin_friend', 'width' => '40', 'height' => '40' ) ) .'</a></li>';
				
				$step += 1;
			}
			
			$output .= '</ul><br style="clear:both"></div>';
		}
		
		return apply_filters( 'bp_checkins_get_friends_checkedin', $output, $friends_checkin );

	}