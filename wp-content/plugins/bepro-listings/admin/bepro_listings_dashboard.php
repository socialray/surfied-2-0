<?php
/**
 * BePro Listings dashboard page
 */
 
?>
<div class="wrap about-wrap">

	<h1><?php _e( 'Welcome to BePro Listings!', "bepro-listings"); ?></h1>
	
	<div class="about-text">
		<?php _e('Congratulations, you are now using the latest version of BePro Listings. With lots of ways to customize, this software is ideal for creating your custom listing needs. This page shows a few of the recent enhancements to the plugin.', "bepro-listings" ); ?>
	</div>
	
	<h2 class="nav-tab-wrapper">
		<a href="#" class="nav-tab nav-tab-active">
			<?php _e( "What's New", "bepro-listings" ); ?>
		</a>
	</h2>
	
	<div class="changelog">
		<h3><?php _e( 'You are using', "bepro-listings" ); _e( 'BePro Listings Version:', "bepro-listings" ); echo " ".BEPRO_LISTINGS_VERSION; ?>   </h3>
	
		<div class="feature-section images-stagger-right">
			<h4><?php _e( 'Refinements to registration', "bepro-listings" ); ?></h4>
			<p><?php _e( 'We fixed and enhanced aspects of user registration during their initial submission. One major benefit is the ability for plugins like BePro Email to send users an email after a successful registration/submission', "bepro-listings" ); ?></p>
			
			<h4><?php _e( 'Various Search Result Enhancements', "bepro-listings" ); ?></h4>
			<p><?php _e( 'We just rewrote a lot of the code which generates search results. The new enhancements should make searches faster and be more informative to end users. We introduced the ability to choose between mile and km distance searches. Also, developers can now tie into our ajax calls via wordpress hooks, perfect for creating custom addons', "bepro-listings" ); ?></p>
			
			<h4><?php _e( 'Translations', "bepro-listings" ); ?></h4>
			<p><?php _e( 'We have introduced a .POT file for those interested in creating their own translations. We have removed all tranlation files, except for the POT file from the plugin. This has reduced the plugin size while still allowing users to create the needed translations.', "bepro-listings" ); ?></p>
			
			<h4><?php _e( 'Support Us', "bepro-listings" ); ?></h4>
			<p><?php _e( 'Hopefully you like BePro Listings. Consider sharing your experience with other users by leaving a <a href="http://wordpress.org/support/view/plugin-reviews/bepro-listings" target="_blank">review on wordpress.org</a>. Your feedback helps to support development of this free solution and informs fellow wordpress users of its usefulness.', "bepro-listings" ); ?></p>
		</div>
	</div>

</div>