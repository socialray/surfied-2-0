<?php

/**
 * BP Checkins - Places single
 *
 *
 * @package BP Checkins
 */
/* BP Theme compat feature needs to be used so let's adapt templates to it */
if( bp_checkins_is_bp_default() ):
?>

<?php get_header( 'buddypress' ); ?>

	<?php do_action( 'bp_before_directory_bp_checkins_page' ); ?>

	<div id="content">
		<div class="padder">
			
<?php else:?>
	
	<?php do_action( 'bp_before_directory_bp_checkins_page' ); ?>

		<div id="buddypress">
	
<?php endif;?>

		<?php do_action( 'bp_before_places_single_loop' ); ?>
		<?php do_action( 'template_notices' ); ?>

		<?php if ( bp_checkins_has_places( bp_checkins_ajax_querystring('places') ) ) : ?>


			<?php while ( bp_checkins_has_places() ) : bp_checkins_the_place(); ?>
				
				<article id="place-single-<?php bp_checkins_places_id();?>" class="places-cpt">

					<header class="places-header">
						
						<div class="places-avatar">

								<?php bp_checkins_places_avatar(); ?>
						
						</div>
						
						<div class="places-header-action">

							<?php bp_checkins_places_action(); ?>

						</div>
						
						<?php if( bp_checkins_is_bp_default() ):?>
						
						<div class="places-title-area">
							
							<h2><?php bp_checkins_places_title(); ?></h2>
							
						</div>
						
						<?php endif;?>
						
						<?php if ( is_user_logged_in() ) : ?>

							<div class="places-meta">
								
								<?php if ( bp_checkins_places_can_comment() ) : ?>

									<a href="<?php bp_get_checkins_places_comment_link(); ?>" class="button bp-primary-action" id="comment-<?php bp_checkins_places_id(); ?>"><?php printf( __( 'Checkin <span>%s</span> &amp; Comment <span>%s</span>', 'bp-checkins' ), bp_checkins_places_get_checkins_count(), bp_checkins_places_get_comment_count() ); ?></a>

								<?php endif; ?>

								<?php if ( bp_checkins_places_user_can_delete() ) bp_checkins_places_delete_link(); ?>

								<?php do_action( 'bp_places_single_meta' ); ?>
								
								<?php bp_checkins_friends_checkedin();?>

							</div>

						<?php endif; ?>
						
					</header>

					<div class="places-content">

							<div id="bpci-map" style="width:100%">
								
							</div>
							
							<?php do_action('bpci_map_single');?>

							<div class="places-inner">

								<?php bp_checkins_places_content(); ?>
								
								<div style="clear:both"></div>

							</div>
						
						<?php do_action( 'bp_places_single_content' ); ?>

					</div>
				
				</article>
				
				<?php comments_template(); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<div id="message" class="info">
				<p><?php _e( 'Sorry, there was no places found. Please try a different filter.', 'bp-checkins' ); ?></p>
			</div>

		<?php endif; ?>

	<?php do_action( 'bp_after_places_single_loop' ); ?>

<?php if( bp_checkins_is_bp_default() ):?>
	</div><!-- .padder -->
</div><!-- #content -->

<?php do_action( 'bp_after_directory_checkins_page' ); ?>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>

<?php else:?>
	
	</div>

<?php do_action( 'bp_after_directory_checkins_page' ); ?>

<?php endif;?>