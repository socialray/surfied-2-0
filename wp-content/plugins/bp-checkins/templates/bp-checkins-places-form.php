<?php

/**
 * BP Checkins - Places Form
 *
 * @package BP Checkins
 */

$class = apply_filters('bp_checkins_places_form_show', 'bp-checkins-hide');

?>

<form id="places-form" name="places-form" action="" method="POST" class="places-new <?php echo $class;?> bp-ci-form" role="complementary">
	<?php do_action( 'bp_checkins_before_places_post_form' ); ?>

	<div id="new-place-avatar">
		<a href="<?php echo bp_loggedin_user_domain(); ?>">
			<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
		</a>
	</div>
	
<?php if( bp_checkins_is_bp_default() ):?>

	<h5><?php _e('What about sharing a new place with the community ?', 'bp-checkins')?></h5>
	
<?php endif;?>

	<div id="new-place-content">
		
		<?php bp_checkins_place_title_field();?>
		
		<div id="new-place-detailed-content">
			<h5 class="place-form-label"><label id="_place_description_label" for="_place_description"><?php _e('Description of your place', 'bp-checkins')?></label></h5>

			<?php bp_checkins_display_wp_editor();?>

			<input type="hidden" name="_bp_checkins_attachment_ids" id="bp_checkins_attachment_ids"/>
			<input type="hidden" name="_bp_checkins_featured_image_id" id="bp_checkins_featured_image_id"/>

			<div id="bp_checkins_featured_image" style="display:none">
				<a href="#" id="bp_checkins_remove_featured" title="Remove"><?php _e('Remove Featured Image', 'bp-checkins');?></a>
				<div class="clear"></div>
			</div>
			
			<div id="bp-checkins-geo" class="bp-checkins-places-field">
				<?php bp_checkins_place_geolocate();?>
			</div>
			
			<div id="bp-checkins-cat" class="bp-checkins-places-field">
				<?php bp_checkins_place_display_cats();?>
			</div>
			
			<div id="bp-checkins-type" class="bp-checkins-places-field">
				<?php bp_checkins_place_type();?>
			</div>

			<div id="new-place-options">
				<div id="new-place-submit">
					<input type="submit" name="bpci-place-new-submit" id="bpci-place-new-submit" value="<?php _e( 'Post Place', 'bp-checkins' ); ?>" />
				</div>

				<?php if ( bp_is_active( 'groups' ) && !bp_is_my_profile() && !bp_is_group() ) : ?>

					<div id="new-place-post-in-box">

						<?php _e( 'Post in', 'bp-checkins' ) ?>:

						<select id="new-place-post-in" name="new-place-post-in">
							<option selected="selected" value="0"><?php _e( 'Places Directory', 'bp-checkins' ); ?></option>

							<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) :
								while ( bp_groups() ) : bp_the_group(); ?>
								
									<?php if( bp_checkins_group_can_checkin() ):?>
										<option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>
									<?php endif;?>

								<?php endwhile;
							endif; ?>

						</select>
					</div>
					<input type="hidden" id="new-post-place-object" name="new-post-place-object" value="groups" />

				<?php elseif ( bp_checkins_is_group_places_area() ) : ?>

					<input type="hidden" id="new-place-post-object" name="new-place-post-object" value="groups" />
					<input type="hidden" id="new-place-post-in" name="new-place-post-in" value="<?php bp_group_id(); ?>" />

				<?php endif; ?>

				<?php do_action( 'bp_checkins_place_form_options' ); ?>

			</div><!-- #whats-new-options -->
		</div>
		
	</div>
	
	<?php wp_nonce_field( 'post_places', '_wpnonce_post_places' ); ?>
	<?php do_action( 'bp_checkins_after_places_post_form' ); ?>
</form>