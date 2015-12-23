<?php

/**
 * BP Checkins - Template my places
 *
 * @package BP Checkins
 */
/* BP Theme compat feature needs to be used so let's adapt templates to it */
if( bp_checkins_is_bp_default() ):
?>

<?php get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_member_places_content' ); ?>

			<div id="item-header" role="complementary">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php bp_get_displayed_user_nav(); ?>

						<?php do_action( 'bp_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->
			
<?php endif;?>

			<div id="item-body">
				
				<div class="item-list-tabs checkins-type-tabs no-ajax" id="subnav">
					<form action="" method="get" id="checkins-form-filter">
					<ul>
						<?php do_action( 'bp_checkins_member_before_nav' ); ?>
						
						<?php bp_get_options_nav() ?>
						
						<?php do_action( 'bp_checkins_member_before_filter' ); ?>

						<li id="places-filter-select" class="last">

							<label for="places-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label>
							<select id="places-filter-by">
								<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
								<option value="all_live_places"><?php _e( 'Live Places', 'bp-checkins' ) ;?></option>
								<option value="upcoming_places"><?php _e( 'Upcoming Places', 'bp-checkins' ) ;?></option>
								<?php if( is_user_logged_in() ):?>
									<option value="places_around"><?php _e( 'Places around', 'bp-checkins' ) ;?></option>
								<?php endif;?>

								<?php do_action('bp_checkins_member_places_filters'); ?>

							</select>
						</li>
					</ul>
					</form>
				</div>
				
				<?php if ( is_user_logged_in() && bp_is_my_profile() ) : ?>
					<?php bp_checkins_load_template_choose( 'bp-checkins-places-form' ); ?>
				<?php endif; ?>

				<?php do_action( 'bp_after_member_places_post_form' ) ?>
				<?php do_action( 'bp_before_member_places_body' );?>

				<div class="activity single-member" role="main">
					<?php bp_checkins_load_template_choose( 'bp-checkins-places-loop' ); ?>
				</div><!-- .activity.single-group -->

				<?php do_action( 'bp_after_member_places_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_places_content' ); ?>

<?php if( bp_checkins_is_bp_default() ):?>
		</div><!-- .padder -->
	</div><!-- #content -->

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); 
endif;
?>
