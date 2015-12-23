<?php

/**
 * BP Checkins - category template
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

		<?php do_action( 'template_notices' ); ?>
		
		<div class="item-list-tabs no-ajax checkins-type-tabs" id="subnav">
			<ul>
				<li id="category-places" class="selected">
					<a id="places-area">
						<?php printf( __( 'You are browsing the archive for %1$s places.', 'bp-checkins' ), trim(wp_title( false, false )) ); ?>
					</a>
				</li>
				<li id="places-filter-select" class="last">

					<label for="places-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label>
					<select id="places-filter-by">
						<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
						<option value="all_live_places"><?php _e( 'Live Places', 'bp-checkins' ) ;?></option>
						<option value="upcoming_places"><?php _e( 'Upcoming Places', 'bp-checkins' ) ;?></option>
						<?php if( is_user_logged_in() ):?>
							<option value="places_around"><?php _e( 'Places around', 'bp-checkins' ) ;?></option>
						<?php endif;?>

						<?php do_action('bp_checkins_group_filters'); ?>

					</select>
				</li>
			</ul>
		</div>
		
<?php else:?>
	
	<?php do_action( 'bp_before_directory_bp_checkins_page' ); ?>

	<div id="buddypress">
			
		<?php do_action( 'template_notices' ); ?>
		
		<div class="item-list-tabs no-ajax checkins-type-tabs" id="subnav">
			<ul>
				<li id="category-places" class="selected">
					<a id="places-area">
						<?php printf( __( 'You are browsing the archive for %1$s places.', 'bp-checkins' ), str_replace( get_bloginfo('name'), '', trim(wp_title( false, false )) ) ); ?>
					</a>
				</li>
				<li id="places-filter-select" class="last">

					<label for="places-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label>
					<select id="places-filter-by">
						<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
						<option value="all_live_places"><?php _e( 'Live Places', 'bp-checkins' ) ;?></option>
						<option value="upcoming_places"><?php _e( 'Upcoming Places', 'bp-checkins' ) ;?></option>
						<?php if( is_user_logged_in() ):?>
							<option value="places_around"><?php _e( 'Places around', 'bp-checkins' ) ;?></option>
						<?php endif;?>

						<?php do_action('bp_checkins_group_filters'); ?>

					</select>
				</li>
			</ul>
		</div>
		
<?php endif;?>

		<?php do_action( 'bp_before_places_category_loop' ); ?>

		<div class="activity places-category" role="main">
			<?php bp_checkins_load_template_choose( 'bp-checkins-places-loop' ); ?>
		</div><!-- .activity.single-group -->

		<?php do_action( 'bp_after_places_category_loop' ); ?>


<?php if( bp_checkins_is_bp_default() ): ?>
	</div><!-- .padder -->
</div><!-- #content -->

<?php do_action( 'bp_after_category_places_page' ); ?>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>

<?php else:?>
	
	</div><!-- .buddypress -->

<?php do_action( 'bp_after_category_places_page' ); ?>

<?php endif;?>