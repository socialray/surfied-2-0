<?php

/**
 * BP Checkins - Place Home
 *
 *
 * @package BP Checkins
 */
/* BP Theme compat feature needs to be used so let's adapt templates to it */
if( bp_checkins_is_bp_default() ):
?>

<?php get_header( 'buddypress' ); ?>

	<?php do_action( 'bp_before_home_bp_checkins_page' ); ?>

	<div id="content">
		<div class="padder">
			
		<?php do_action( 'template_notices' ); ?>
		
		<div id="places-src-container">
			
			<h3><?php _e('Search Places', 'bp-checkins');?></h3>
			
<?php else:?>
	
	<div id="buddypress">
			
		<?php do_action( 'template_notices' ); ?>
		
		<div id="places-src-container">
	
<?php endif;?>

			<form id="form-places-search" class="standard-form">
				<input type="text" id="places-search" name="places_search">
			</form>
			
		</div>
		
		<div id="places-brz-categories">
			
			<h3><?php _e('Or browse places by category', 'bp-checkins');?></h3>
			
			<?php bp_checkins_places_browse_cats();?>
			
		</div>
		
		<?php do_action( 'bp_after_home_bp_checkins_page_content' ); ?>

<?php if( bp_checkins_is_bp_default() ):?>
		
	</div><!-- .padder -->
</div><!-- #content -->

<?php do_action( 'bp_after_home_bp_checkins_page' ); ?>

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>

<?php else:?>
	
	</div><!-- #buddypress -->

<?php do_action( 'bp_after_home_bp_checkins_page' ); ?>

<?php endif;?>