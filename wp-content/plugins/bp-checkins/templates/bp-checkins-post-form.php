<?php

/**
 * BP Checkins - Checkin Post Form
 *
 * @package BP Checkins
 */

?>
<form action="" method="post" id="whats-new-form" name="whats-new-form" class="checkins-new bp-ci-form" role="complementary">

	<?php do_action( 'bp_checkins_before_checkin_post_form' ); ?>

	<div id="whats-new-avatar">
		<a href="<?php echo bp_loggedin_user_domain(); ?>">
			<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
		</a>
	</div>

<?php if( bp_checkins_is_bp_default() ):?>
	
	<h5><?php _e('Where are you ?', 'bp-checkins')?></h5>
	
<?php endif;?>

	<div id="whats-new-content">
		<div id="whats-new-textarea">
			<noscript>
				<p><?php _e('Javascript is necessary for this component, please enable it in your browser options', 'bp-checkins')?></p>
			</noscript>
			<div id="whats-new" class="bp-checkins-whats-new" contenteditable="false"></div>
		</div>

		<div id="whats-new-options">
			<div id="whats-new-submit">
				<input type="submit" name="bpci-whats-new-submit" id="bpci-whats-new-submit" value="<?php _e( 'Post Checkin', 'bp-checkins' ); ?>" />
			</div>

			<?php if ( bp_is_active( 'groups' ) && !bp_is_my_profile() && !bp_is_group() ) : ?>

				<div id="whats-new-post-in-box">

					<?php _e( 'Post in', 'bp-checkins' ) ?>:

					<select id="whats-new-post-in" name="whats-new-post-in">
						<option selected="selected" value="0"><?php _e( 'My Profile', 'bp-checkins' ); ?></option>

						<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) :
							while ( bp_groups() ) : bp_the_group(); ?>
							
								<?php if( bp_checkins_group_can_checkin() ):?>
									<option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>
								<?php endif;?>
								
							<?php endwhile;
						endif; ?>

					</select>
				</div>
				<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />

			<?php elseif ( bp_checkins_is_group_checkins_area() ) : ?>

				<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
				<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_group_id(); ?>" />

			<?php endif; ?>

			<?php do_action( 'bp_checkins_post_form_options' ); ?>

		</div><!-- #whats-new-options -->
	</div><!-- #whats-new-content -->

	<?php wp_nonce_field( 'post_checkin', '_wpnonce_post_checkin' ); ?>
	<?php do_action( 'bp_checkins_after_checkin_post_form' ); ?>

</form><!-- #whats-new-form -->