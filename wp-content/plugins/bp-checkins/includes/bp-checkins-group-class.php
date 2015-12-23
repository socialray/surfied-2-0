<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Bp_Checkins_Group extends BP_Group_Extension {	

	var $visibility  = 'private';
	var $enable_create_step  = false;
	var $enable_nav_item  = true;
	var $enable_edit_item = true;
		
	function __construct() {
		global $bp;
		$this->name = __( 'Checkins', 'bp-checkins' );
		$this->slug = 'checkins';
		$this->nav_item_position = 31;
		$this->enable_nav_item = $this->enable_nav_item();
		
		add_action( 'bp_groups_admin_meta_boxes', array( $this, 'admin_ui_edit_screen' ) );
		add_action( 'bp_group_admin_edit_after', array( $this, 'admin_ui_edit_save'), 10, 1 );
	}

	function create_screen() {
		return false;
	}

	function create_screen_save() {
		return false;
	}

	function edit_screen() {
		global $bp;
		if ( !bp_is_group_admin_screen( $this->slug ) )
					return false; ?>

				<h2><?php echo esc_attr( $this->name ) ?> <?php _e('settings for this group.','bp-checkins');?></h2>

				<p>
						<input type="checkbox" name="_group_checkins_activate" value="1" <?php if ( groups_get_groupmeta( $bp->groups->current_group->id, 'checkins_ok' ) ) :?> checked="checked"<?php endif; ?> > <?php _e('Activate checkins & places for this group','bp-checkins');?>
				</p>
				<input type="submit" name="save" value="<?php _e('Save','bp-checkins');?>" />

				<?php
				wp_nonce_field( 'groups_edit_save_' . $this->slug );
	}

	function edit_screen_save() {
		global $bp;

		if ( !isset( $_POST['save'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		/* Insert your edit screen save code here */
		$checkins_ok = !empty( $_POST['_group_checkins_activate'] ) ? $_POST['_group_checkins_activate'] : false ;
		
		if( !empty($checkins_ok) ){
			$success = groups_update_groupmeta( $bp->groups->current_group->id, 'checkins_ok', $checkins_ok );
		}
		else $success = groups_delete_groupmeta( $bp->groups->current_group->id, 'checkins_ok' );
		
		if( groups_get_groupmeta( $bp->groups->current_group->id, 'checkins_ok' ) != 1 )
			bp_checkins_group_disabled_checkins();
			

		/* To post an error/success message to the screen, use the following */
		if ( !$success )
			bp_core_add_message( __( 'There was an error saving, please try again', 'bp-checkins' ), 'error' );
		else
			bp_core_add_message( __( 'Settings saved successfully', 'bp-checkins' ) );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . 'admin/' . $this->slug );
	}
	
	function admin_ui_edit_screen() {
		
		add_meta_box( 'bp_checkins_meta_box', _x( 'BP Checkins', 'group admin edit screen', 'bp-checkins' ), array( &$this, 'admin_edit_metabox'), get_current_screen()->id, 'side', 'core' );
		
	}
	
	function admin_edit_metabox( $item ) {
		
		$checkins_status = intval( groups_get_groupmeta( $item->id, 'checkins_ok' ) );

		?>
			<div class="bp-groups-settings-section" id="bp-groups-settings-section-checkins">
				<label for="group-use-checkin"><input type="checkbox" name="_group_checkins_activate" id="group-use-checkin" value="1" <?php checked( $checkins_status, 1 ) ?> /> <?php _e( 'Activate checkins & places','bp-checkins' ) ?><br />
			</div>
		<?php
		
	}
	
	function admin_ui_edit_save( $group_id ) {
		/* Insert your edit screen save code here */
		$checkins_ok = !empty( $_POST['_group_checkins_activate'] ) ? $_POST['_group_checkins_activate'] : false ;
		
		if( !empty($checkins_ok) ){
			$success = groups_update_groupmeta( $group_id, 'checkins_ok', $checkins_ok );
		}
		else $success = groups_delete_groupmeta( $group_id, 'checkins_ok' );
		
		/* we don't want activities to lead to 404 */
		if( groups_get_groupmeta( $group_id, 'checkins_ok' ) != 1 )
			bp_checkins_group_disabled_checkins( $group_id );
	}

	function display() {
		global $bp;
		$current_url = bp_get_group_permalink( $bp->groups->current_group ) . $bp->current_action.'/';
		$checkins_group_class = false;
		?>
		<div class="item-list-tabs no-ajax checkins-type-tabs" id="subnav">
			<form action="" method="get" id="checkins-form-filter">
			<ul>
				<li id="group-checkins" class="<?php if( !bp_action_variable( 0 ) || 'checkins'== bp_action_variable( 0 ) ) echo 'selected'?>"><a href="<?php echo $current_url;?>" id="checkins-area"><?php _e( 'Checkins' , 'bp-checkins');?></a></li>
				
				<li id="group-places" class="<?php if( 'places'== bp_action_variable( 0 ) ) echo 'selected'?>"><a href="<?php echo $current_url . 'places/' ;?>" id="places-area"><?php _e( 'Places' , 'bp-checkins');?></a></li>
				
				<?php do_action('bp_checkins_group_nav'); ?>
				
				<?php if( !bp_action_variable( 0 ) || 'checkins'== bp_action_variable( 0 ) ):?>

				<li id="checkins-filter-select" class="last">

					<label for="checkins-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label>
					<select id="checkins-filter-by" name="_checkins_filter_by">
						<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
						<option value="friends_checkin"><?php _e( 'Friends checkins', 'bp-checkins' ); ?></option>
						<option value="activity_checkin"><?php _e( 'Activity checkins', 'bp-checkins' ); ?></option>
						<option value="place_checkin"><?php _e( 'Place checkins', 'bp-checkins' ); ?></option>
						
						<?php do_action('bp_checkins_group_checkins_filters'); ?>
						
					</select>
				</li>
				
				<?php else:?>
					
					<li id="places-filter-select" class="last">

						<label for="places-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label>
						<select id="places-filter-by">
							<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
							<option value="all_live_places"><?php _e( 'Live Places', 'bp-checkins' ) ;?></option>
							<option value="upcoming_places"><?php _e( 'Upcoming Places', 'bp-checkins' ) ;?></option>
							<?php if( is_user_logged_in() ):?>
								<option value="places_around"><?php _e( 'Places around', 'bp-checkins' ) ;?></option>
							<?php endif;?>

							<?php do_action('bp_checkins_group_places_filters'); ?>

						</select>
					</li>
					
				<?php endif;?>
				
			</ul>
			</form>
		</div>
		<?php if( !bp_action_variable( 0 ) || 'checkins'== bp_action_variable( 0 ) ):?>
		
			<?php do_action( 'bp_before_group_checkins_post_form' ) ?>

			<?php if ( is_user_logged_in() && bp_group_is_member() ) : ?>
				<?php bp_checkins_load_template_choose( 'bp-checkins-post-form' ); ?>
			<?php endif; ?>

			<?php do_action( 'bp_after_group_checkins_post_form' ) ?>
			<?php do_action( 'bp_before_group_checkins_content' ) ?>

			<div class="activity single-group" role="main">
				<?php bp_checkins_locate_template_choose( 'activity/activity-loop' ); ?>
			</div><!-- .activity.single-group -->

			<?php do_action( 'bp_after_group_checkins_content' ) ?>
			
		<?php else:?>
			
			<?php do_action( 'bp_before_group_places_post_form' ) ?>

			<?php if ( is_user_logged_in() && bp_group_is_member() ) : ?>
				<?php bp_checkins_load_template_choose( 'bp-checkins-places-form' ); ?>
			<?php endif; ?>

			<?php do_action( 'bp_after_group_places_post_form' ) ?>
			<?php do_action( 'bp_before_group_places_content' ) ?>

			<div class="activity single-group" role="main">
				<?php bp_checkins_load_template_choose( 'bp-checkins-places-loop' ); ?>
			</div><!-- .activity.single-group -->

			<?php do_action( 'bp_after_group_places_content' ) ?>
			
		<?php endif;?>
		
		<?php
	}

	function widget_display() {
		return false;
	}
	
	function enable_nav_item() {
		global $bp;
		
		if( empty( $bp->groups->current_group->id ) )
			return false;
		
		if ( groups_get_groupmeta( $bp->groups->current_group->id, 'checkins_ok' ) )
			return true;
		else
			return false;
	}
}


bp_register_group_extension( 'Bp_Checkins_Group' );

?>