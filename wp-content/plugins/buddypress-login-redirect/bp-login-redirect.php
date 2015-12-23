<?php
/*
Plugin Name: BP Login Redirect
Description: allows the buddypress site admins to decide where to redirect their users after login. Now logout redirection is set to Homepage.
Contributors: j_p_s
Author: Jatinder Pal Singh
Author URI: http://www.jpsays.com
Version: 2.2
Stable Tag: 2.2
Tested up t0: 4.1

*/
?>
<?php
add_action( 'admin_menu', 'blr_add_admin_menu' );
add_action( 'admin_init', 'blr_settings_init' );
add_filter("login_redirect","bp_login_redirection",100,3);
add_action('wp_logout','blr_logout_redirect');

function blr_add_admin_menu(  ) { 

	add_options_page( 'BP Login Redirection', 'BP Login Redirection', 'manage_options', 'bp_login_redirection', 'bp_login_redirection_options_page' );

}


function blr_settings_init(  ) { 

	register_setting( 'pluginPage', 'blr_settings' );

	add_settings_section(
		'blr_pluginPage_section', 
		__( '<a href="http://www.jpsays.com" alt="www.jpsays.com">By Jatinder Pal Singh</a>', 'wordpress' ), 
		'blr_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'blr_radio_field_0', 
		__( 'Redirection Type', 'wordpress' ), 
		'blr_radio_field_0_render', 
		'pluginPage', 
		'blr_pluginPage_section' 
	);

	add_settings_field( 
		'blr_text_field_1', 
		__( 'custom url', 'wordpress' ), 
		'blr_text_field_1_render', 
		'pluginPage', 
		'blr_pluginPage_section' 
	);


}


function blr_radio_field_0_render(  ) { 

	$options = get_option( 'blr_settings' );
	?>
	<table>
	<tr><td>Personal Profile / Personal Activity:</td><td><input type='radio' name='blr_settings[blr_radio_field_0]' <?php checked( 1,$options['blr_radio_field_0'], true ); ?> value='1'></td></tr>
	<tr><td>Site Wide Activity:</td><td><input type='radio' name='blr_settings[blr_radio_field_0]' <?php checked( 2,$options['blr_radio_field_0'], true ); ?> value='2'></td></tr>
	<tr><td>Friends' Activity:</td><td><input type='radio' name='blr_settings[blr_radio_field_0]' <?php checked( 3,$options['blr_radio_field_0'], true); ?> value='3'></td></tr>
	<tr><td>Custom URL:</td><td><input type='radio' name='blr_settings[blr_radio_field_0]' <?php checked( 4,$options['blr_radio_field_0'], true); ?> value='4'></td></tr>
	</table>
	<?php

}


function blr_text_field_1_render(  ) { 

	$options = get_option( 'blr_settings' );
	?>
	<input type='text' name='blr_settings[blr_text_field_1]' value='<?php echo $options['blr_text_field_1']; ?>'>
	<?php

}


function blr_settings_section_callback(  ) { 

	echo __( '<b><u>Note:</u></b> Custom URL will not work with any external link. Custom URL must be from your wordpress site only', 'wordpress' );

}


function bp_login_redirection_options_page(  ) { 

	?>
	<form action='options.php' method='post'>
		
		<h2>BP Login Redirection</h2>
		
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
		
	</form>
	<?php

}
function bp_login_redirection($redirect_url,$request_url,$user)
{
	global $bp;
	$options = get_option( 'blr_settings' );
	$selected_option = $options['blr_radio_field_0'];
	if($selected_option == '1')
	{
		return bp_core_get_user_domain($user->ID);
	}
	elseif($selected_option=='2')
	{
		$activity_slug = bp_get_activity_root_slug();
		$redirect_url = $bp->root_domain."/".$activity_slug;
		return $redirect_url;
	}
	elseif($selected_option=='4')
	{
		//$activity_slug = bp_get_activity_root_slug();
		//$redirect_url = $bp->root_domain."/".$activity_slug;
		$redirect_url = $options['blr_text_field_1'];
		return $redirect_url;
	}
	else
	{
		$activity_slug = bp_get_activity_root_slug();
		$friends_activity = 	($user->ID).$activity_slug."/friends/";
		return $friends_activity;
	}
}
function blr_logout_redirect(){
  wp_redirect( home_url() );
  exit();
}
?>