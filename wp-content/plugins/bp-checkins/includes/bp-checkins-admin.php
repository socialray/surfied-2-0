<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', 'bp_checkins_administration_menu', 21);


function bp_checkins_administration_menu() {
	global $bp, $bp_checkins_manager_admin_page, $bp_checkins_logs_slug;
	
	if( version_compare( BP_CHECKINS_PLUGIN_VERSION, get_option( 'bp-checkins-version' ), '>' ) )
		do_action('bp_checkins_plugin_updated');

	if ( !$bp->loggedin_user->is_site_admin )
		return false;
		
	$admin_page = bp_checkins_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' )
		$submenu = 'bp-general-settings';
	else
		$submenu = $admin_page;
		
	$bp_checkins_manager_admin_page = add_submenu_page( $submenu, __( 'BP Checkins Settings', 'bp-checkins' ), __( 'BP Checkins Settings', 'bp-checkins' ), 'manage_options', 'bp-checkins-admin', 'bp_checkins_settings_admin' );
	
	if( bp_checkins_is_foursquare_ready() ) {
		$bp_checkins_logs_slug = 'foursquare-logs';
		if( is_multisite() )
			$bp_checkins_logs_page = add_submenu_page( $submenu, __('Foursquare logs', 'bp-checkins'), __('Foursquare logs', 'bp-checkins'), 'manage_options', $bp_checkins_logs_slug, 'bp_checkins_fs_logs' );
		
		else
			$bp_checkins_logs_page = add_management_page( __('Foursquare logs', 'bp-checkins'), __('Foursquare logs', 'bp-checkins'), 'manage_options', $bp_checkins_logs_slug, 'bp_checkins_fs_logs' );
		
	}
		
		
	add_action("load-$bp_checkins_manager_admin_page", 'bp_checkins_admin_css');
}

function bp_checkins_admin_css() {
	wp_enqueue_style( 'bp-checkins-admin-css', BP_CHECKINS_PLUGIN_URL_CSS . '/admin.css' );
	
	if ( isset( $_POST['bp_checkins_admin_submit'] ) && isset( $_POST['bpci-admin'] ) ) {
		if ( !check_admin_referer('bp-checkins-admin') )
			return false;

		// Settings form submitted, now save the settings.
		foreach ( (array)$_POST['bpci-admin'] as $key => $value )
			bp_update_option( $key, $value );

	}
	
	/* handling install / desinstall of checkin page ! */
	$checkins_and_places_activated = (int)bp_get_option( 'bp-checkins-activate-component' );
	$pages = bp_get_option( 'bp-pages' );
	$active_components = bp_get_option('bp-active-components');
	
	if( $checkins_and_places_activated == 1 ){
		// first check if page exists !
		if( empty( $pages[BP_CHECKINS_SLUG] ) ){
			$page_checkins = wp_insert_post( array( 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => ucwords( BP_CHECKINS_SLUG ), 'post_status' => 'publish', 'post_type' => 'page' ) );

			$pages[BP_CHECKINS_SLUG] = $page_checkins;
			bp_update_option('bp-pages', $pages );
		}
		if( empty( $active_components[BP_CHECKINS_SLUG] ) ){
			$active_components[BP_CHECKINS_SLUG] = 1;
			bp_update_option('bp-active-components', $active_components );
		}
		
		do_action('bp_checkins_component_activated');
		
	} else {
		if( !empty( $pages[BP_CHECKINS_SLUG] ) ){
			wp_delete_post($pages[BP_CHECKINS_SLUG], true);
			unset($pages[BP_CHECKINS_SLUG]);
			bp_update_option('bp-pages', $pages );
		}
		if( !empty( $active_components[BP_CHECKINS_SLUG] ) ){
			unset($active_components[BP_CHECKINS_SLUG]);
			bp_update_option('bp-active-components', $active_components );
		}
		
		do_action('bp_checkins_component_deactivated');
	}
	
}

function bp_checkins_admin_tabs( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';
	$admin_page = bp_checkins_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' )
		$admin_page = 'admin.php';

	// Setup core admin tabs
	$tabs = array(
		'0' => array(
			'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-checkins-admin' ), $admin_page ) ),
			'name' => __( 'Activity checkins', 'bp-checkins' )
		),
		'1' => array(
			'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-checkins-admin', 'tab' => 'component'   ), $admin_page ) ),
			'name' => __( 'Checkins & Places Component', 'bp-checkins' )
		),
		'2' => array(
			'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-checkins-admin', 'tab' => 'foursquare'   ), $admin_page ) ),
			'name' => __( 'Foursquare API Settings', 'bp-checkins' )
		)
	);

	// Loop through tabs and build navigation
	foreach( $tabs as $tab_id => $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
	}

	// Output the tabs
	echo $tabs_html;
}

function bp_checkins_settings_admin(){
	global $bp_checkins_logs_slug;
	
	$active = __( 'Activity checkins', 'bp-checkins' );
	
	if( !empty( $_GET['tab'] ) && $_GET['tab'] == 'component' )
		$active = __( 'Checkins & Places Component', 'bp-checkins' );
		
	if( !empty( $_GET['tab'] ) && $_GET['tab'] == 'foursquare' )
		$active = __( 'Foursquare API Settings', 'bp-checkins' );
		
		
	$upload_size_unit = $max_upload_size =  wp_max_upload_size();
	$sizes = array( 'KB', 'MB', 'GB' );
	for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
		$upload_size_unit /= 1024;
	if ( $u < 0 ) {
		$upload_size_unit = 0;
		$u = 0;
	} else {
		$upload_size_unit = (int) $upload_size_unit;
	}
	
	do_action( 'bp_checkins_cron_options' );
	
	$schedules = wp_get_schedules();
	$admin_page = bp_checkins_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' )
		$admin_page = 'admin.php';
		
	$buddy_settings_page = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
	?>
	<div class="wrap">
		<?php screen_icon( 'bp-checkins' ); ?>
		
		<h2 class="nav-tab-wrapper"><?php bp_checkins_admin_tabs( $active );?></h2>
		
		<?php if ( isset( $_POST['bpci-admin'] ) ) : ?>

			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'bp-checkins' ); ?></p>
			</div>

		<?php endif; ?>
		
		<form action="" method="post" id="bp-admin-form">
			
			<?php if( empty( $_GET['tab'] ) ):?>
			
			<table class="form-table">
				<tbody>
						<tr>
							<th scope="row"><?php _e( 'Disable Activity checkins in profile and group post forms', 'bp-checkins' ) ?></th>
							<td>
								<input type="radio" name="bpci-admin[bp-checkins-disable-activity-checkins]"<?php if ( (int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-activity-checkins-yes" value="1" /> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
								<input type="radio" name="bpci-admin[bp-checkins-disable-activity-checkins]"<?php if ( !(int)bp_get_option( 'bp-checkins-disable-activity-checkins' ) || '' == bp_get_option( 'bp-checkins-disable-activity-checkins' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-activity-checkins-no" value="0" /> <?php _e( 'No', 'bp-checkins' ) ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Hide position of user&#39;s friends in friends list', 'bp-checkins' ) ?></th>
							<td>
								<input type="radio" name="bpci-admin[bp-checkins-disable-geo-friends]"<?php if ( (int)bp_get_option( 'bp-checkins-disable-geo-friends' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-geo-friends-yes" value="1" /> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
								<input type="radio" name="bpci-admin[bp-checkins-disable-geo-friends]"<?php if ( !(int)bp_get_option( 'bp-checkins-disable-geo-friends' ) || '' == bp_get_option( 'bp-checkins-disable-geo-friends' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-geo-friends-no" value="0" /> <?php _e( 'No', 'bp-checkins' ) ?>
							</td>
						</tr>
				</tbody>
			</table>
			
			<?php elseif( $_GET['tab'] == 'component' ):?>
				
				<?php if( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ):?>
					
					<input type="hidden" name="bpci-admin[bp-checkins-activate-component]" value="1">
					
					<p>
						<?php _e('If you want to activate this component, simply click on the &#39;Save Settings&#39; button, then you will be able to edit its behavior', 'bp-checkins');?>
					</p>
					
				<?php else:?>
					
					<h3><?php _e('Checkins Component', 'bp-checkins');?></h3>
					
					<table class="form-table">
						<tbody>
								<tr>
									<th scope="row"><?php _e( 'Enable image uploads in Checkins (it needs at least <b>64M</b> of memory limit)', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[bp-checkins-enable-image-uploads]"<?php if ( (int)bp_get_option( 'bp-checkins-enable-image-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-image-uploads-yes" value="1"/> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
										<input type="radio" name="bpci-admin[bp-checkins-enable-image-uploads]"<?php if ( !(int)bp_get_option( 'bp-checkins-enable-image-uploads' ) || '' == bp_get_option( 'bp-checkins-enable-image-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-image-uploads-no" value="0"/> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
								</tr>
						</tbody>
					</table>
					
					<h3><?php printf(__('<a href="%s">Places Component</a>', 'bp-checkins'), admin_url('edit.php?post_type=places') ) ;?> : </h3>
					
					<table class="form-table">
						<tbody>
								<tr>
									<th scope="row"><?php _e( 'Enable image uploads in Places', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[bp-checkins-enable-place-uploads]"<?php if ( (int)bp_get_option( 'bp-checkins-enable-place-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-image-uploads-yes" value="1"/> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
										<input type="radio" name="bpci-admin[bp-checkins-enable-place-uploads]"<?php if ( !(int)bp_get_option( 'bp-checkins-enable-place-uploads' ) || '' == bp_get_option( 'bp-checkins-enable-place-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-image-uploads-no" value="0"/> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Max width for the image added via an url in Places', 'bp-checkins' );?></th>
									<td>
										<input type="text" name="bpci-admin[bp-checkins-max-width-image]" 
										<?php if ( (int)bp_get_option( 'bp-checkins-max-width-image' ) ){
											echo 'value="'. intval(bp_get_option( 'bp-checkins-max-width-image' )) .'"'; 
										} else {
											echo 'value="300"';
										}
										?>	 
										 id="bp-checkins-max-width-image"/> &nbsp;
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Enable image uploads in Places comments (it needs at least <b>64M</b> of memory limit)', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[bp-checkins-enable-comment-image-uploads]"<?php if ( (int)bp_get_option( 'bp-checkins-enable-comment-image-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-comment-image-uploads-yes" value="1"/> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
										<input type="radio" name="bpci-admin[bp-checkins-enable-comment-image-uploads]"<?php if ( !(int)bp_get_option( 'bp-checkins-enable-comment-image-uploads' ) || '' == bp_get_option( 'bp-checkins-enable-comment-image-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-comment-image-uploads-no" value="0"/> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Enable info box to show friends that checked in a place to logged in user', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[bp-checkins-enable-box-checkedin-friends]"<?php if ( (int)bp_get_option( 'bp-checkins-enable-box-checkedin-friends' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-box-checkedin-friends-yes" value="1"/> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
											<input type="radio" name="bpci-admin[bp-checkins-enable-box-checkedin-friends]"<?php if ( !(int)bp_get_option( 'bp-checkins-enable-box-checkedin-friends' ) || '' == bp_get_option( 'bp-checkins-enable-box-checkedin-friends' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-enable-box-checkedin-friends-no" value="0"/> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Amount of milliseconds for the timer in Live places', 'bp-checkins' );?></th>
									<td>
										<input type="text" name="bpci-admin[bp-checkins-live-places-timer]" 
										<?php if ( (int)bp_get_option( 'bp-checkins-live-places-timer' ) ){
											echo 'value="'. intval(bp_get_option( 'bp-checkins-live-places-timer' )) .'"'; 
										} else {
											echo 'value="8000"';
										}
										?>	 
										 id="bp-checkins-live-places-timer"/> &nbsp;
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Disable timer in the comments of live places', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[bp-checkins-disable-timer]"<?php if ( (int)bp_get_option( 'bp-checkins-disable-timer' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-disable-timer-yes" value="1"/> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
										<input type="radio" name="bpci-admin[bp-checkins-disable-timer]"<?php if ( !(int)bp_get_option( 'bp-checkins-disable-timer' ) || '' == bp_get_option( 'bp-checkins-disable-timer' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-disable-timer-no" value="0"/> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
								</tr>
						</tbody>
					</table>
					
					
					<h3><?php _e('Shared settings', 'bp-checkins');?></h3>
					
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php printf(__( 'Max upload file size for images (%s)', 'bp-checkins' ), $sizes[$u]) ?></th>
								<td>
									<input type="text" name="bpci-admin[bp-checkins-max-upload-size]" 
									<?php if ( (int)bp_get_option( 'bp-checkins-max-upload-size' ) ){
										echo 'value="'. intval(bp_get_option( 'bp-checkins-max-upload-size' )) .'"'; 
									} else {
										echo 'value="'.intval($upload_size_unit).'"';
									}
									?>	 
									 id="checkins-max-upload-size"/> &nbsp;
								</td>
							</tr>
						</tbody>
					</table>
					<p>&nbsp;</p>
					<p class="description"><?php _e('Image uploads in checkin component or in the comments of places uses the HTML5 File and File Reader API, it may not work for all users depending on their browser...', 'bp-checkins')?></p>
					
					<h3><?php _e('Disable Checkins and Places components', 'bp-checkins');?></h3>
					
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php _e( 'Check the &#39;yes&#39; option and save the settings to disable the components', 'bp-checkins' ) ?></th>
								<td>
									<input type="radio" name="bpci-admin[bp-checkins-activate-component]"<?php if ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-activate-component-yes" value="0" /> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
									<input type="radio" name="bpci-admin[bp-checkins-activate-component]"<?php if ( (int)bp_get_option( 'bp-checkins-activate-component' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-activate-component-no" value="1" /> <?php _e( 'No', 'bp-checkins' ) ?>
								</td>
							</tr>
						</tbody>
					</table>
					
				<?php endif;?>
			
			<?php elseif( $_GET['tab'] == 'foursquare' ):?>
				
				<?php if( !bp_is_active( 'settings' ) ):?>
					
					<div id="message" class="updated">
						<p>
							<?php printf( __('If you want to use this feature, you need to activate the Account Settings BuddyPress component, to do so, please activate it in <a href="%s">BuddyPress settings</a>', 'bp-checkins'), bp_get_admin_url( add_query_arg( array( 'page' => 'bp-components' ), $buddy_settings_page ) ) );?>
						</p>
					</div>
				
				<?php elseif( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ):?>
					
					<p>
						<?php printf( __('If you want to use this feature, you need to activate the Checkins and Places component, to do so use <a href="%s">the appropriate tab</a>', 'bp-checkins'), bp_get_admin_url( add_query_arg( array( 'page' => 'bp-checkins-admin', 'tab' => 'component'   ), $admin_page ) ) );?>
					</p>
					
				<?php else:?>
					
					<h3><?php _e('Foursquare credentials', 'bp-checkins');?></h3>

					<table class="form-table">
						<tbody>
								<tr>
									<th scope="row"><?php _e( 'Client ID', 'bp-checkins' ) ?></th>
									<td>
										<input type="text" name="bpci-admin[foursquare-client-id]" value="<?php if( bp_get_option( 'foursquare-client-id' ) && "" != bp_get_option( 'foursquare-client-id' ) ) echo bp_get_option( 'foursquare-client-id' );?>" id="foursquare_client_id">
									</td>
									<td>
										<p class="description"><?php _e('given by Foursquare once you registered an API key.', 'bp-checkins');?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Client Secret', 'bp-checkins' ) ?></th>
									<td>
										<input type="text" name="bpci-admin[foursquare-client-secret]" value="<?php if( bp_get_option( 'foursquare-client-secret' ) && "" != bp_get_option( 'foursquare-client-secret' ) ) echo bp_get_option( 'foursquare-client-secret' );?>" id="foursquare_secret_id">
									</td>
									<td>
										<p class="description"><?php _e('given by Foursquare once you registered an API key.', 'bp-checkins');?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Callback Url', 'bp-checkins' ) ?></th>
									<td>
										<p style="color:green"><?php echo site_url( bp_get_checkins_root_slug() );?></p>
									</td>
									<td>
										<p class="description"><?php _e('The callback url is needed when registering a Foursquare API key.', 'bp-checkins');?></p>
									</td>
								</tr>
						</tbody>
					</table>

					<p class="description"><?php printf(__('If you have not registered a Foursquare API Key, yet, you can get one by clicking <a href="%s">here</a>. You will be able to choose your application name, your application website and the callback url displayed in green on this screen.', 'bp-checkins'), 'https://foursquare.com/oauth/register' )?></p>

					<h3><?php _e('Foursquare import preferences', 'bp-checkins');?></h3>

					<table class="form-table">
						<tbody>
								<tr>
									<th scope="row"><?php _e( 'Use WordPress Cron', 'bp-checkins' ) ?></th>
									<td>
										<select name="bpci-admin[foursquare-cron-schedule]" id="foursquare_cron_schedule">
											<option value="0"><?php _e('Do not use WP Cron', 'bp-checkins');?></option>
											<?php foreach( $schedules as $value_interval => $display_interval ):?>
												<option value="<?php echo $value_interval;?>" <?php selected( bp_get_option( 'foursquare-cron-schedule' ), $value_interval );?>><?php echo $display_interval['display'];?></option>
											<?php endforeach;?>
										</select>
									</td>
									<td>
										<p class="description"><?php _e('WordPress Cron is run when a frontend or admin page is loaded on your web site', 'bp-checkins');?></p>
										<?php if( !empty( $bp_checkins_logs_slug ) ):?>
											<p class="description"><a href="<?php bp_checkins_admin_url($bp_checkins_logs_slug);?>"><?php _e('Foursquare import logs page', 'bp-checkins');?></a></p>
										<?php endif;?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Enable your members to import manually their checkins', 'bp-checkins' ) ?></th>
									<td>
										<input type="radio" name="bpci-admin[foursquare-user-import]"<?php if ( !(int)bp_get_option( 'foursquare-user-import' ) || '' == bp_get_option( 'foursquare-user-import' ) ) : ?> checked="checked"<?php endif; ?> id="foursquare_user_import_yes" value="0" /> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
										<input type="radio" name="bpci-admin[foursquare-user-import]"<?php if ( (int)bp_get_option( 'foursquare-user-import' ) ) : ?> checked="checked"<?php endif; ?> id="foursquare_user_import_no" value="1" /> <?php _e( 'No', 'bp-checkins' ) ?>
									</td>
									<td>
										<p class="description"><?php _e('If enabled, members will be able to import their checkins from the checkins tab of their settings area', 'bp-checkins');?></p>
									</td>
								</tr>
						</tbody>
					</table>
					<h3><?php _e('Disable Foursquare API', 'bp-checkins');?></h3>
					
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php _e( 'Check the &#39;yes&#39; option and save the settings to disable foursquare API', 'bp-checkins' ) ?></th>
								<td>
									<input type="radio" name="bpci-admin[bp-checkins-deactivate-foursquare]"<?php if ( (int)bp_get_option( 'bp-checkins-deactivate-foursquare' ) == 1 ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-deactivate-foursquare-yes" value="1" /> <?php _e( 'Yes', 'bp-checkins' ) ?> &nbsp;
									<input type="radio" name="bpci-admin[bp-checkins-deactivate-foursquare]"<?php if ( !(int)bp_get_option( 'bp-checkins-deactivate-foursquare' ) || '' == bp_get_option( 'bp-checkins-deactivate-foursquare' ) ) : ?> checked="checked"<?php endif; ?> id="bp-checkins-deactivate-foursquare-no" value="0" /> <?php _e( 'No', 'bp-checkins' ) ?>
								</td>
							</tr>
						</tbody>
					</table>
					
				<?php endif;?>
				
			<?php endif;?>
			
			<p class="submit">
				<input class="button-primary" type="submit" name="bp_checkins_admin_submit" id="bp-checkins-admin-submit" value="<?php _e( 'Save Settings', 'bp-checkins' ); ?>" />
			</p>

			<?php wp_nonce_field( 'bp-checkins-admin' ); ?>
			
		</form>
		
	</div>
	<?php
}

function bp_checkins_fs_logs() {
	global $wpdb;
	
	$limit = 20;
	if( isset($_GET['paged']) ){
		$paged = $_GET['paged'];
	}
	else{
		$paged = 1;
	}
	$offset = $limit * ($paged - 1);
	
	$logs = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->base_prefix}bp_checkins_foursquare_logs ORDER BY id DESC LIMIT %d,%d", $offset, $limit)
    );
	
	$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_checkins_foursquare_logs");
	
	$all_count = $total_logs;
	$max_pages = ceil($all_count / $limit);
	$timestamp = wp_next_scheduled( 'bp_checkins_cron_job' );
	
	?>
	<div class="wrap">
		<h2><?php _e('BP Checkins : foursquare import logs', 'bp-checkins' );?></h2>
		<p class="description"><?php printf(__('Next run for cron job : %s', 'bp-checkins'), bp_checkins_date($timestamp) )?></p>
		<div style="margin-top:1em">
			<table class="widefat fixed"> 
		    	<thead> 
		         	<tr> 
		            	<th scope="col"><?php _e('User id', 'bp-checkins');?></th> 
		            	<th scope="col"><?php _e('Log type', 'bp-checkins');?></th> 
		             	<th scope="col"><?php _e('Log info', 'bp-checkins');?></th> 
		          	</tr> 
		       	</thead> 
		       	<tbody> 
		        	<?php foreach( $logs as $log ):?>
						
						<tr><td><?php echo $log->user_id;?></td><td><?php echo $log->type;?></td><td><?php echo $log->log;?></td></tr>
		                 
					<?php endforeach;?>
		      	</tbody> 
		  	</table>
		</div>
		
		<div class="tablenav">
		   <?php
		    $page_links = paginate_links( array(
		        'base' => add_query_arg( 'paged', '%#%' ),
		        'format' => '',
		        'prev_text' => __('&laquo;'),
		        'next_text' => __('&raquo;'),
		        'total' => $max_pages,
		        'current' => $paged
		     ));
		    ?>

		<?php if ( $page_links ) { ?>
			<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
			number_format_i18n( $offset+1 ),
			number_format_i18n( $offset + count($logs) ),
			number_format_i18n( $all_count ),
			$page_links
		); echo $page_links_text."</div>"; ?>

		<?php } ?>
		
	</div>
	<?php
}

add_action( 'places_category_edit_form_fields', 'bp_checkins_edit_places_category', 10, 2);

function bp_checkins_edit_places_category($cat, $taxonomy) {
    $places_category_thumb_id = get_metadata( $cat->taxonomy, $cat->term_id, 'places_category_thumbnail_id', true);
	
	$places_category_thumbnail = wp_get_attachment_image_src( $places_category_thumb_id, $size='thumbnail' );
	
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="places_category_thumbnail"><?php _e('Thumbnail of the places category', 'bp-checkins');?></label></th>
        <td>
		<?php if( empty($places_category_thumb_id) ):?>
            <input type="text" name="places_category_thumbnail" id="places_category_thumbnail"/>
			<input id="places_category_upload_image_button" type="button" value="<?php _e('Upload Places category image', 'bp-checkins');?>" class="button-secondary" style="width:inherit!important" />
			<input type="hidden" name="places_category_thumbnail_id" id="places_category_thumbnail_id"/>
            <p class="description"><?php _e('Choose an image to illustrate your category.', 'bp-checkins');?></p>
		<?php else:?>
			<img src="<?php echo $places_category_thumbnail[0];?>" height="<?php echo $places_category_thumbnail[2];?>" width="<?php echo $places_category_thumbnail[1];?>" id="bpci_places_cat_img"/>
			<input type="text" name="places_category_thumbnail" id="places_category_thumbnail" value="<?php echo $places_category_thumbnail[0];?>" readonly/>
			<input id="places_category_upload_image_button" type="button" value="<?php _e('Upload Places category image', 'bp-checkins');?>" class="button-secondary" style="width:inherit!important" />
			<input type="hidden" name="places_category_thumbnail_id" id="places_category_thumbnail_id"/>
			<input type="hidden" name="places_category_thumbnail_oldid" id="places_category_thumbnail_oldid" value="<?php echo $places_category_thumb_id;?>"/>
            <p class="description"><?php _e('Choose an image to replace the actual thumbnail for your category.', 'bp-checkins');?></p>
		<?php endif;?>
        </td>
    </tr>
    <?php
}

add_action( 'edited_places_category', 'bp_checkins_save_places_category', 10, 2);

function bp_checkins_save_places_category($term_id, $tt_id) {
	
    if (!$term_id) return;
    
    if (isset($_POST['places_category_thumbnail_id'])) {
	
		if( !empty( $_POST['places_category_thumbnail_oldid'] ) ){
			wp_delete_attachment( $_POST['places_category_thumbnail_oldid'], true );
		}
		update_metadata( $_POST['taxonomy'], $term_id, 'places_category_thumbnail_id', 
            $_POST['places_category_thumbnail_id']);
	}

}
 
add_action('load-edit-tags.php', 'bp_checkins_places_cat_thumb_uploads_scripts');

function bp_checkins_places_cat_thumb_uploads_scripts(){
	$screen = get_current_screen();

	if( $screen->id == 'edit-places_category' ) {
		wp_enqueue_script('media-upload');
		add_thickbox();
		wp_register_script('bp-checkins-cat-upload', BP_CHECKINS_PLUGIN_URL_JS . '/bp-checkins-cat-thumbs.js', array('jquery','media-upload','thickbox') );
		wp_enqueue_script('bp-checkins-cat-upload');
	}
}

add_action('bp_checkins_cron_options', 'bp_checkins_sets_wpcron', 1 );

function bp_checkins_sets_wpcron() {
	
	if( bp_get_option( 'foursquare-cron-schedule' )=="" || !bp_get_option( 'foursquare-cron-schedule' ) )
		$cron_is_enable = false;
	else
		$cron_is_enable = true;
		
	if( (int)bp_get_option( 'bp-checkins-deactivate-foursquare' ) == 1 )
		$cron_is_enable =false;
		
	if ( !(int)bp_get_option( 'bp-checkins-activate-component' ) || '' == bp_get_option( 'bp-checkins-activate-component' ) ) 
		$cron_is_enable =false;
		
	$cron_is_enable = apply_filters('bp_checkins_sets_wpcron', $cron_is_enable );
		
	if( $cron_is_enable ) {
		if ( !wp_next_scheduled( 'bp_checkins_cron_job' ) ) { 
				//schedule the event to run hourly 
		        wp_schedule_event( time(), bp_get_option( 'foursquare-cron-schedule' ), 'bp_checkins_cron_job' ); 
		} else {
			//if bp_checkins_cron_job is set let's verify if the shedule has changed
			$bp_checkins_cron_get_schedule = wp_get_schedule( 'bp_checkins_cron_job' );
			
			if( bp_get_option( 'foursquare-cron-schedule' ) != $bp_checkins_cron_get_schedule ) {
				$timestamp = wp_next_scheduled( 'bp_checkins_cron_job' );
				//unschedule custom action hook 
				wp_unschedule_event( $timestamp, 'bp_checkins_cron_job' );
				wp_schedule_event( time(), bp_get_option( 'foursquare-cron-schedule' ), 'bp_checkins_cron_job' );
			}
		}
	} else {
		$timestamp = wp_next_scheduled( 'bp_checkins_cron_job' );
		//unschedule custom action hook 
		wp_unschedule_event( $timestamp, 'bp_checkins_cron_job' );
	}
}


add_action( 'bp_checkins_component_deactivated', 'bp_checkins_delete_all_checkins_activities' );

function bp_checkins_delete_all_checkins_activities() {
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'new_place' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_comment' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'place_checkin' ) );
	bp_activity_delete( array( 'component' => 'groups', 'type' => 'activity_checkin' ) );
	bp_activity_delete( array( 'component' => 'places' ) );
	bp_activity_delete( array( 'component' => 'checkins' ) );
}

/**
* BP 1.6beta1 new admin area
*/
function bp_checkins_16_new_admin(){
	if( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.6-beta2-6162', '>=' ) ){
		$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
		return $page;
	}
	else return 'bp-general-settings.php';
}

/* wp-pointer */
add_action( 'admin_enqueue_scripts', 'bp_checkins_enqueue_pointer' );

function bp_checkins_enqueue_pointer() {
	$bp_checkins_welcome = get_user_setting( '_bp_checkins_user_settings', 0 );
	if ( ! $bp_checkins_welcome ) {
		wp_enqueue_style( 'wp-pointer' ); 
		wp_enqueue_script( 'wp-pointer' ); 
		wp_enqueue_script( 'utils' );
		add_action( 'admin_print_footer_scripts', 'bp_checkins_pointer_print_footer_scripts' );
	}
}

function bp_checkins_pointer_print_footer_scripts() {

	$admin_page = bp_checkins_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' ) {
		$pointermenu = 'toplevel_page_bp-general-settings';
		
		$pointer_content = '<h3>'. __('Welcome in BP Checkins 1.0', 'bp-checkins') . '</h3>';
		$pointer_content .= '<p>'. __("Please take a few seconds to configure the options of this plugin in BuddyPress Settings menu / BP Checkins Settings submenu.", 'bp-checkins') . '</p>';
		
	} else {
		$pointermenu = 'menu-settings';
		
		$pointer_content = '<h3>'. __('Welcome in BP Checkins 1.0', 'bp-checkins') . '</h3>';
		$pointer_content .= '<p>'. __("Please take a few seconds to configure the options of this plugin in WordPress Settings menu / BP Checkins Settings submenu.", 'bp-checkins') . '</p>';
	}
		
	
?>
<script type="text/javascript"> 
//<![CDATA[
jQuery(document).ready( function($) { 
	$('#<?php echo $pointermenu; ?>').pointer({ 
		content: '<?php echo $pointer_content; ?>', 
		position: {
			edge:'left',
			my: 'left middle', 
			at: 'right top', 
			offset: '0 10',
		},
		close: function() { 
			setUserSetting( '_bp_checkins_user_settings', '1' ); 
		} 
	}).pointer('open'); 
}); 
//]]> 
</script>
<?php
}

function bp_checkins_admin_url( $path = false ) {
	$admin_page = bp_checkins_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' )
		$admin_page = 'admin.php';
	
	if( is_multisite() )
		echo network_admin_url( $admin_page .'?page='.$path );
		
	else
		echo admin_url( 'tools.php?page='.$path );
}

add_action( 'bp_checkins_plugin_updated', 'bp_checkins_actions_after_update' );

function bp_checkins_actions_after_update() {
	// do we need to do some db stuff ?
	update_option( 'bp-checkins-version', BP_CHECKINS_PLUGIN_VERSION );
}