<?php

/**
 * BP Checkins - Checkin places Stream (Single Item)
 *
 *
 * @package BP Checkins
 */

?>

<?php do_action( 'bp_before_places_entry' ); ?>

<li class="<?php //bp_activity_css_class(); ?>" id="place-<?php bp_checkins_places_id(); ?>">
	<div class="places-avatar">
	
		<?php bp_checkins_places_avatar(); ?>

	</div>

	<div class="places-content">

		<div class="places-header">

			<?php bp_checkins_places_action(); ?>

		</div>

			<div class="places-inner">
				
				<?php bp_checkins_places_featured_image();?>

				<h4><a href="<?php bp_checkins_places_the_permalink();?>" title="<?php bp_checkins_places_title(); ?>"><?php bp_checkins_places_title(); ?></a> <?php if( bp_checkins_places_is_live() ) : bp_checkins_places_live_status(); endif;?></h4>
				
				<p class="place-excerpt"><?php bp_checkins_places_excerpt();?></p>

			</div>

		<?php do_action( 'bp_places_entry_content' ); ?>

		<?php if ( is_user_logged_in() ) : ?>

			<div class="places-meta">
				
				<?php if ( bp_checkins_places_can_comment() ) : ?>

					<a href="<?php bp_get_checkins_places_comment_link(); ?>" class="button bp-primary-action" id="comment-<?php bp_checkins_places_id(); ?>"><?php printf( __( 'Checkin <span>%s</span> &amp; Comment <span>%s</span>', 'bp-checkins' ), bp_checkins_places_get_checkins_count(), bp_checkins_places_get_comment_count() ); ?></a>

				<?php endif; ?>

				<?php if ( bp_checkins_places_user_can_delete() ) bp_checkins_places_delete_link(); ?>

				<?php do_action( 'bp_places_entry_meta' ); ?>

			</div>

		<?php endif; ?>

	</div>
	
	<div class="clear"></div>

</li>

<?php do_action( 'bp_after_places_entry' ); ?>
