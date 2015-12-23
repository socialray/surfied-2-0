<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* this file shares the actions for bp-checkins-activity 
and the component checkins & places */

function bp_checkins_show_friends_checkins() {
	if( (int)bp_get_option( 'bp-checkins-disable-geo-friends' ) )
		return false;
		
	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) )
		return false;

	if(  bp_displayed_user_id() && bp_is_friends_component() && !bp_is_user_friend_requests() )
		return true;
		
	else return false;
}

function bp_checkins_is_activity_or_friends() {

	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) )
		return false;
		
	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && bp_is_activity_component() && !bp_is_single_activity() )
		return false;
		
	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && bp_is_group_home() )
		return false;
		
	if( !bp_checkins_show_friends_checkins() && bp_displayed_user_id() && bp_is_friends_component() )
		return false;
	
	if(  bp_is_group_home() || bp_is_activity_component() || ( bp_displayed_user_id() && bp_is_friends_component() ) )
		return true;
		
	else return false;
}

function bp_checkins_is_directory() {
	if( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) )
		return false;
	
	if( bp_is_current_component('checkins') && !bp_displayed_user_id() && !bp_current_action() )
		return true;
	else 
		return false;
}

function bp_checkins_is_group_checkins_area() {
	if( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) )
		return false;
	
	if( bp_is_groups_component() && bp_is_single_item() && bp_is_current_action( 'checkins' ) )
		return true;
		
	else return false;
}

function bp_checkins_is_foursquare_ready() {
	$client_id = false;
	$client_secret = false;
	$foursquare_activate = true;
	
	if( bp_get_option( 'foursquare-client-id' ) && "" != bp_get_option( 'foursquare-client-id' ) )
		$client_id = true;
		
	if( bp_get_option( 'foursquare-client-secret' ) && "" != bp_get_option( 'foursquare-client-secret' ) )
		$client_secret = true;
		
	if ( (int)bp_get_option( 'bp-checkins-deactivate-foursquare' ) )
		$foursquare_activate = false;
		
	if( !$client_id || !$client_secret || !$foursquare_activate )
		return false;
	
	else
		return true;
}

function bp_checkins_localize_script( $script = 'dir' ) {
	
	if( $script == 'dir' ) {
		wp_localize_script('bp-ckeckins-dir', 'bp_checkins_dir_vars', array(
					'addCheckinTitle'        => __('Add a check-in!','bp-checkins'),
					'addMapViewTitle'        => __('View on map','bp-checkins'),
					'addMapSrcTitle'         => __('Search address','bp-checkins'),
					'modCheckinTitle'        => __('Edit your position','bp-checkins'),
					'refreshCheckinTitle'    => __('Refresh your position','bp-checkins'),
					'addErrorGeocode'        => __('OOps, we could not geocode your address for this reason','bp-checkins'),
					'addressPlaceholder'     => __('type your address','bp-checkins'),
					'html5LocalisationError' => __('OOps, we could not localized you, you can search for your address in the field that received the focus.','bp-checkins'),
					'addPolaTitle'           => __('Add a photo', 'bp-checkins'),
					'addPolaLinkTitle'       => __('Insert this photo', 'bp-checkins'),
					'yourcontenthere'        => __('Add your content here..', 'bp-checkins'),
					'isrequired'             => __('is required', 'bp-checkins'),
					'uploadAuthorized'       => bp_get_option( 'bp-checkins-enable-image-uploads' ),
					'imageAddUpload'         => __('Upload', 'bp-checkins'),
					'imageErrorType'         => __('Please select an image file only.', 'bp-checkins'),
					'imageAddExternal'       => __('Use external url', 'bp-checkins'),
					'imageUrlPlaceholder'    => __('Paste the url to your image here', 'bp-checkins'),
					'imageErrorNoUrl'        => __('Please add an url to an image.', 'bp-checkins'),
					'maxUploadFileSize'      => bp_get_option( 'bp-checkins-max-upload-size' ),
					'imageTooBig'            => __('Your image is too big, please reduce it (max file size : ', 'bp-checkins'),
					'pleaseLocalizeU'        => __('Please localize yourself to enable this field', 'bp-checkins'),
					'placeTitle'             => __('What about sharing a new place with the community ?', 'bp-checkins'),
					'checkinTitle'           => __('Where are you ?', 'bp-checkins')
				)
			);
	}
	else if( $script == 'activity' ) {
		wp_localize_script('bp-ckeckins', 'bp_checkins_vars', array(
					'addCheckinTitle'        => __('Add a check-in!','bp-checkins'),
					'addMapViewTitle'        => __('View on map','bp-checkins'),
					'addMapSrcTitle'         => __('Search address','bp-checkins'),
					'modCheckinTitle'        => __('Edit your position','bp-checkins'),
					'resetCheckinTitle'      => __('Cancel this check-in','bp-checkins'),
					'addErrorGeocode'        => __('OOps, we could not geocode your address for this reason','bp-checkins'),
					'addressPlaceholder'     => __('type your address','bp-checkins'),
					'html5LocalisationError' => __('OOps, we could not localized you, you can search for your address in the field that received the focus.','bp-checkins')
				)
			);
	}
	else if( $script == 'single') {
		
		$timer = (int)bp_get_option( 'bp-checkins-live-places-timer' );
		$timer = empty( $timer ) ? 8000 : $timer ;
		
		wp_localize_script('bp-ckeckins-single', 'bp_checkins_single_vars', array(
					'addPolaTitle'           => __('Add a photo', 'bp-checkins'),
					'addPolaLinkTitle'       => __('Insert this photo', 'bp-checkins'),
					'pleaseWait'             => __('Please wait 12 hours before checking in again..'),
					'uploadAuthorized'       => bp_get_option( 'bp-checkins-enable-comment-image-uploads' ),
					'imageAddUpload'         => __('Upload', 'bp-checkins'),
					'imageErrorType'         => __('Please select an image file only.', 'bp-checkins'),
					'imageAddExternal'       => __('Use external url', 'bp-checkins'),
					'imageUrlPlaceholder'    => __('Paste the url to your image here', 'bp-checkins'),
					'imageErrorNoUrl'        => __('Please add an url to an image.', 'bp-checkins'),
					'maxUploadFileSize'      => bp_get_option( 'bp-checkins-max-upload-size' ),
					'imageTooBig'            => __('Your image is too big, please reduce it (max file size : ', 'bp-checkins'),
					'livePlaceTimer'         => $timer,
					'disablePlaceTimer'      => bp_get_option( 'bp-checkins-disable-timer' ),
					'livePlaceMessage'       => __('Place is live : next refresh', 'bp-checkins'),
					'livePlaceEnded'         => __('Live is finished !', 'bp-checkins')
				)
			);
	}
	
	
}


function bp_checkins_load_gmap3() {
	if( bp_checkins_is_activity_or_friends() || bp_checkins_is_directory() || bp_checkins_is_group_checkins_area() ) {
		wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
		wp_enqueue_script( 'gmap3', BP_CHECKINS_PLUGIN_URL_JS . '/gmap3.min.js', array('jquery') );
		
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
		
		if( !empty( $_GET['map'] ) && $_GET['map'] == 1 ) {
			global $bpci_lat, $bpci_lng;
			$bpci_lat = bp_activity_get_meta( bp_current_action(), 'bpci_activity_lat' );
			$bpci_lng = bp_activity_get_meta( bp_current_action(), 'bpci_activity_lng' );

			if( !empty( $bpci_lat ) && !empty( $bpci_lng ) ) {
				add_action('wp_head', 'bp_checkins_item_map');
			}
		} elseif( bp_checkins_show_friends_checkins() ){
			wp_enqueue_script( 'bp-ckeckins-friends', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-friends.js' );
		} else {
			
			if( bp_checkins_is_directory() || bp_checkins_is_group_checkins_area() ) {
				wp_enqueue_script( 'bp-ckeckins-dir', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-dir.js' );
				bp_checkins_localize_script('dir');
			} else {
				wp_enqueue_script( 'bp-ckeckins', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins.js' );
				bp_checkins_localize_script('activity');
			}
			
		}
		
		if( bp_is_single_activity() ){
			add_action('wp_footer', 'bp_checkins_img_trick');
		}
	}
	
	if( bp_displayed_user_id() && bp_is_settings_component() && bp_is_current_action( 'checkins-settings') ){
		wp_enqueue_style( 'bpcistyle', BP_CHECKINS_PLUGIN_URL_CSS . '/bpcinstyle.css');
	}
}

add_action('bp_actions', 'bp_checkins_load_gmap3');

function bp_checkins_item_map() {
	global $bpci_lat, $bpci_lng;
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			var bpciPosition = new google.maps.LatLng(<?php echo $bpci_lat;?>,<?php echo $bpci_lng;?>);
			
			adresse = $(".update-checkin").html();
			
			$(".activity-checkin").append('<div id="bpci-map"></div>');
			$(".activity-checkin").css('width','100%');
			
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
	            content: '<div class="bpci-avatar"><s></s><i></i><span>' + $(".activity-avatar").html() + '</span></div>',
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


function bp_checkins_record_group_geoloc_meta( $content, $user_id, $group_id, $activity_id ) {
	bp_checkins_record_geoloc_meta( $content, $user_id, $activity_id );
}

function bp_checkins_record_geoloc_meta( $content, $user_id, $activity_id ){
	
	/* javascript disabled */
	if( isset( $_POST['bpci-lat'] ) ) {
		$lat = $_POST['bpci-lat'];
		$lng = $_POST['bpci-lng'];
		$address = $_POST['bpci-address'];
	}
	else{
		if ( !empty( $_POST['cookie'] ) )
			$_BP_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
		else
			$_BP_COOKIE = &$_COOKIE;
		
		if( $_BP_COOKIE['bp-ci-data-delete'] == "delete")
			return false;
			
		if( strlen( $_BP_COOKIE['bp-ci-data'] ) < 2 )
			return false;
			
		$geotable = explode('|', $_BP_COOKIE['bp-ci-data'] );
		$lat = $geotable[0];
		$lng = $geotable[1];
		$address = $geotable[2];
	}
	
	if( !empty($lat) && !empty($lng) && !empty($address) ) {
		
		// let's add some meta to activity
		bp_activity_update_meta( $activity_id, 'bpci_activity_lat', $lat );
		bp_activity_update_meta( $activity_id, 'bpci_activity_lng', $lng );
		bp_activity_update_meta( $activity_id, 'bpci_activity_address', $address );
		
		$check_activity_array = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
		
		$check_activity = $check_activity_array['activities'][0];
		
		/* if the activity is new_place or solo comment place, we should not update the user's last position */
		if( !in_array( $check_activity->type, array('new_place', 'place_comment') ) ) {
			// let's update latest user's position for 'show my friends on map' feature
			bp_update_user_meta( $user_id, 'bpci_latest_lat', $lat );
			bp_update_user_meta( $user_id, 'bpci_latest_lng', $lng );
			bp_update_user_meta( $user_id, 'bpci_latest_address', $address );
		}
		
		/* as we have the activity, let's rename the type of the activity to activity_checkin if it's a regular activity_update */
		if( $check_activity->type == "activity_update" && (int)bp_get_option( 'bp-checkins-activate-component' ) ) {
			bp_checkins_update_to_checkin_type( $activity_id, $check_activity );
			
		}
			
		
	}
	
}

/**
* Hooking activity display to show the user's checkin
*
*/
add_action( 'bp_activity_entry_content', 'bp_checkins_display_user_checkin');

function bp_checkins_display_user_checkin(){
	
	if( ( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && !bp_is_current_component('checkins') && !bp_is_current_action( 'checkins' ) && !bp_is_single_activity() ) || ( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && bp_is_single_activity() && ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) )  )
		return false;
		
	$activity_id = bp_get_activity_id();
	$activity_permalink = bp_activity_get_permalink( $activity_id ) . '?map=1';
	
	$address = bp_activity_get_meta( $activity_id, 'bpci_activity_address' );
	
	
	if( $address ){
		?>
		<div class="activity-checkin">
			<a href="<?php echo $activity_permalink;?>" title="<?php _e('Open the map for this update', 'bp-checkins');?>" class="link-checkin"><span class="update-checkin"><?php echo stripslashes( $address );?></span></a>
		</div>
		<?php
	}
	
}

add_filter( 'bp_activity_permalink_redirect_url', 'bp_checkins_handle_bp_redirection');

function bp_checkins_handle_bp_redirection( $redirect ) {
	
	if( empty( $_GET['map'] ) )
		return $redirect;
	
	if( $_GET['map'] == 1 && !strpos( $redirect, '?map=1' ) )
		return $redirect . '?map=1';
		
	else
		return $redirect;
}


function bp_checkins_img_trick(){
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
			$('.bp-ci-zoompic').click( function(){
				
				if( $(this).find('.thumbnail').attr('width') != "100%" ){
					var thumb = $(this).find('.thumbnail').attr('src');
					var full = $(this).attr('href');
					$(this).find('.thumbnail').attr('src', full);
					$(this).attr('href', thumb);
					$('#footer').append('<div id="bpci-full" style="visibility:hidden"><img  src="'+full+'"></div>');
					var reverseh = $('#bpci-full img').height();
					var reversew = $('#bpci-full img').width();
					var ratio = Number( reverseh / reversew );	
					$(this).find('.thumbnail').attr('width', '100%');
					//$(this).find('.thumbnail').attr('height', '100%');
					$(this).find('.thumbnail').css('max-width', '100%');
					$(this).find('.thumbnail').attr('height', Number(ratio * $(this).find('.thumbnail').width() ) +'px');
					$('#footer #bpci-full').remove();
					return false;
				} else {
					var full = $(this).find('.thumbnail').attr('src');
					var thumb = $(this).attr('href');
					$(this).find('.thumbnail').attr('src', thumb);
					$(this).attr('href', full);
					$('#footer').append('<div id="bpci-thumb" style="visibility:hidden"><img  src="'+thumb+'"></div>');
					var reverseh = $('#bpci-thumb img').height();
					var reversew = $('#bpci-thumb img').width();
					var ratio = Number( reverseh / reversew );
					$(this).find('.thumbnail').attr('width', '100px');
					$(this).find('.thumbnail').attr('height', Number(ratio * 100) +'px');
					$('#footer #bpci-thumb').remove();
					return false;
				}
				return false;
			});
		});
	</script>
	<?php
}

add_action('wp_head', 'bp_checkins_style_widget');

function bp_checkins_style_widget() {
	if(is_active_widget( false, false, 'bp_checkins_places_widget' ) ) {
		?>
		<style>
			.widget_bp_checkins_places_widget ul li div.item-avatar{
				float:left;
				margin-right:5px;
			}
			.widget_bp_checkins_places_widget ul li div.item-avatar img{
				width:30px!important;
				height:30px!important;
			}
		</style>
		<?php
	}
}


add_action('bp_directory_members_actions', 'bp_checkins_add_friend_position', 99);

function bp_checkins_add_friend_position(){
	if( (int)bp_get_option( 'bp-checkins-disable-geo-friends' ) )
		return false;
		
	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) )
		return false;
	
	if( bp_is_user_friends() ) {
		
		$user_id = bp_get_member_user_id();
		
		$lat = bp_get_user_meta( $user_id, 'bpci_latest_lat', true );
		$lng = bp_get_user_meta( $user_id, 'bpci_latest_lng', true );
		$address = bp_get_user_meta( $user_id, 'bpci_latest_address', true );
		
		if($lat && $lng && $address){
			?>
			<div class="activity-checkin">
				<a href="#bpci-map" title="<?php _e('Center the map on this friend', 'bp-checkins');?>" id="friend-<?php echo $user_id;?>" rel="<?php echo $lat.','.$lng;?>" class="link-checkin"><span class="update-checkin"><?php echo stripslashes( $address );?></span></a>
			</div>
			<?php
		}
	}
}

add_action('bp_before_member_friends_content', 'bp_checkins_load_friends_map');

function bp_checkins_load_friends_map(){
	if( (int)bp_get_option( 'bp-checkins-disable-geo-friends' ) )
		return false;
		
	if( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) && ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) )
		return false;
	
	$user_id = bp_displayed_user_id();
	
	if(!$user_id) return false;
	
	$lat = bp_get_user_meta( $user_id, 'bpci_latest_lat', true );
	$lng = bp_get_user_meta( $user_id, 'bpci_latest_lng', true );
	$address = bp_get_user_meta( $user_id, 'bpci_latest_address', true );
	?>
	<div id="bpci-map_container"></div>
	
	<?php if( !empty( $lat ) ):?>
	
		<script type="text/javascript">
			var displayedUserLat = "<?php echo $lat;?>";
			var displayedUserLng = "<?php echo $lng;?>";
			var displayedUserAddress = "<?php echo $address;?>";
		</script>
		
	<?php endif;?>
	
	<?php
}

?>