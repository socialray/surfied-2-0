<?php

/**
 * BP Checkins - Places Loop
 *
 * Querystring is set via AJAX in bp-checkins-ajax.php.
 * Inspired by BuddyPress Activity Loop
 *
 * @package BP Checkins
 */

?>

<?php do_action( 'bp_before_places_loop' ); ?>

<?php if ( bp_checkins_has_places( bp_checkins_ajax_querystring('places') ) ) : ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		<ul id="places-stream" class="places-list item-list">

	<?php endif; ?>

	<?php while ( bp_checkins_has_places() ) : bp_checkins_the_place(); ?>

		<?php bp_checkins_load_template_choose( 'bp-checkins-places-entry', false ); ?>

	<?php endwhile; ?>

	<?php if ( bp_checkins_has_more_places() ) : ?>

		<li class="bpci-place-load-more">
			<a href="#more-places"><?php _e( 'Load More', 'bp-checkins' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there was no places found. Please try a different filter.', 'bp-checkins' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_places_loop' ); ?>

<form action="" name="places-loop-form" id="places-loop-form" method="post">

	<?php wp_nonce_field( 'places_filter', '_wpnonce_places_filter' ); ?>

</form>