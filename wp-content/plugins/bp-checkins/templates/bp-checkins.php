<?php

/**
 * BP Checkins - Checkins & Places Directory
 *
 * @package BBP Checkins
 */
if( bp_checkins_is_bp_default() ):
?>

<?php get_header( 'buddypress' ); ?>

	<?php do_action( 'bp_before_directory_bp_checkins_page' ); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_directory_checkins' ); ?>

			<?php if ( !is_user_logged_in() ) : ?>

				<h3><?php _e( 'Checkins & Places', 'bp-checkins' ); ?></h3>

			<?php endif; ?>
			
<?php else:?>
	
<?php do_action( 'bp_before_directory_bp_checkins_page' ); ?>

	<div id="buddypress">

			<?php do_action( 'bp_before_directory_checkins' ); ?>

<?php endif;?>

			<?php do_action( 'bp_before_directory_checkins_content' ); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php bp_checkins_load_template_choose( 'bp-checkins-post-form' ); ?>
				
				<?php bp_checkins_load_template_choose( 'bp-checkins-places-form' ); ?>

			<?php endif; ?>

			<?php do_action( 'template_notices' ); ?>

			<div class="item-list-tabs checkins-type-tabs" role="navigation">
				<ul>
					<?php do_action( 'bp_before_checkins_type_tab_all' ); ?>

					<li class="selected"><a href="#" id="checkins-area"><?php _e('Checkins', 'bp-checkins');?></a><li>
					<li><a href="#" id="places-area"><?php _e('Community Places', 'bp-checkins');?></a></li>
				</ul>
			</div><!-- .item-list-tabs -->

			<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
				<ul>
					<!--<li class="feed"><a href="#" title="<?php //_e( 'RSS Feed', 'bp-checkins' ); ?>"><?php //_e( 'RSS', 'bp-checkins' ); ?></a></li> Maybe in next version if asked !-->

					<?php do_action( 'bp_checkins_syndication_options' ); ?>

					<li id="checkins-filter-select" class="last">
						<label for="checkins-filter-by"><?php _e( 'Show:', 'bp-checkins' ); ?></label> 
						<select id="checkins-filter-by">
							<option value="-1"><?php _e( 'Everything', 'bp-checkins' ); ?></option>
							<option value="friends_checkin"><?php _e( 'Friends checkins', 'bp-checkins' ); ?></option>
							<option value="activity_checkin"><?php _e( 'Activity checkins', 'bp-checkins' ); ?></option>
							<option value="place_checkin"><?php _e( 'Place checkins', 'bp-checkins' ); ?></option>

							<?php do_action( 'bp_checkins_filter_options' ); ?>

						</select>
					</li>
				</ul>
			</div><!-- .item-list-tabs -->

			<?php do_action( 'bp_before_directory_checkins_list' ); ?>

			<div class="activity" role="main">
				
				<?php bp_checkins_locate_template_choose( 'activity/activity-loop' ); ?>

			</div><!-- .checkins -->

			<?php do_action( 'bp_after_directory_checkins_list' ); ?>

			<?php do_action( 'bp_directory_checkins_content' ); ?>

			<?php do_action( 'bp_after_directory_checkins_content' ); ?>

			<?php do_action( 'bp_after_directory_checkins' ); ?>

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