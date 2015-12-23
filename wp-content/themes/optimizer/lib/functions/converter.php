<?php
add_action( 'init', 'optimizer_convert_redux' );
function optimizer_convert_redux() {

	
if(isset($_POST['convert']) && check_admin_referer( 'optimizer_convert', 'optimizer_convert' ) ) {
	$optimizer = get_option('optimizer');
	$active_widgets = get_option( 'sidebars_widgets' );
	$home_blocks = $optimizer['home_sort_id'];

if ($home_blocks):

foreach ($home_blocks as $key=>$value) {

    switch($key) {
	//About Section
    case 'about':
		if(!empty($home_blocks['about'])){
				//ABOUT SECTION--------------------------------------------
				$active_widgets[ 'front_sidebar' ][] = 'optimizer_front_about-1';
				// The latest 15 questions from WordPress Stack Exchange.
				$about_content[ 1 ] = array (
					'title' => $optimizer['about_header_id'],
					'subtitle' => $optimizer['about_preheader_id'],
					'content' => $optimizer['about_content_id'],
					'divider' => $optimizer['divider_icon'],
					'title_color' => $optimizer['about_header_color'],
					'content_color' => $optimizer['about_text_color'],
					'content_bg' => $optimizer['about_bg_color'],
				);
				update_option( 'widget_optimizer_front_about', $about_content );
		}
	
	break;
    case 'blocks':
		if(!empty($home_blocks['blocks'])){
				//BLOCKS SECTION--------------------------------------------
				$active_widgets[ 'front_sidebar' ][] = 'optimizer_front_blocks-1';
				// The latest 15 questions from WordPress Stack Exchange.
				$blocks_content[ 1 ] = array (
					'block1title' => $optimizer['block1_text_id'],
					'block1img' => $optimizer['block1_image']['url'],
					'block1content' =>  $optimizer['block1_textarea_id'],
					'block2title' => $optimizer['block2_text_id'],
					'block2img' => $optimizer['block2_image']['url'],
					'block2content' =>  $optimizer['block2_textarea_id'],
					'block3title' => $optimizer['block3_text_id'],
					'block3img' => $optimizer['block3_image']['url'],
					'block3content' => $optimizer['block3_textarea_id'],
					'block4title' => $optimizer['block4_text_id'],
					'block4img' => $optimizer['block4_image']['url'],
					'block4content' => $optimizer['block4_textarea_id'],
					'block5title' => $optimizer['block5_text_id'],
					'block5img' => $optimizer['block5_image']['url'],
					'block5content' => $optimizer['block5_textarea_id'],
					'block6title' => $optimizer['block6_text_id'],
					'block6img' => $optimizer['block6_image']['url'],
					'block6content' => $optimizer['block6_textarea_id'],
					
					'blockstitlecolor' => $optimizer['blocktitle_color_id'],
					'blockstxtcolor' => $optimizer['blocktxt_color_id'],
					'blocksbgcolor' => $optimizer['midrow_color_id'],
				);
				update_option( 'widget_optimizer_front_blocks', $blocks_content );
				
		}
	break;
	
	
	case 'welcome-text':
		if(!empty($home_blocks['welcome-text'])){
				//WELCOME TEXT SECTION--------------------------------------------
				$active_widgets[ 'front_sidebar' ][] = 'optimizer_front_text-1';
				// The latest 15 questions from WordPress Stack Exchange.
				$text_content[ 1 ] = array (
					'title' => __('Welcome Text','optimizer'),
					'content' => $optimizer['welcm_textarea_id'],
					'padtopbottom' => '2',
					'paddingside' => '2',
					'parallax' => '',
					'content_color' => $optimizer['welcometxt_color_id'],
					'content_bg' => $optimizer['welcome_color_id'],
					'content_bgimg' => $optimizer['welcome_bg_image']['url'],
				);
				//Updated Below --With Newsletter Widget
				if(empty($home_blocks['newsletter'])){
					update_option( 'widget_optimizer_front_text', $text_content );	
				}
				
		}
	break;
	
	
	case 'posts':
		if(!empty($home_blocks['posts'])){
				//POSTS SECTION--------------------------------------------
				$active_widgets[ 'front_sidebar' ][] = 'optimizer_front_posts-1';
				// The latest 15 questions from WordPress Stack Exchange.
				$posts_content[ 1 ] = array (
					'title' => $optimizer['posts_title_id'],
					'subtitle' => $optimizer['posts_subtitle_id'],
					'layout' => $optimizer['front_layout_id'],
					'type' => $optimizer['n_posts_type_id'],
					'count' => $optimizer['n_posts_field_id'],
					'category' => $optimizer['posts_cat_id'],
					'divider' => $optimizer['divider_icon'],
					'navigation' => $optimizer['navigation_type'],
					'postbgcolor' => $optimizer['frontposts_color_id'],
					'titlecolor' => $optimizer['frontposts_title_color'],
					'secbgcolor' => $optimizer['frontposts_bg_color'],
				);
				update_option( 'widget_optimizer_front_posts', $posts_content );
		}
	break;
	
	
	
    } //end of SWITCH

} //end of FOREACH

endif;
		
		//Assign Widgets to frontpage sidebar
		update_option( 'sidebars_widgets', $active_widgets );
		//Update convert =1  and Nivo/Accordion Slides
		$optimizer['converted'] = '1';
		update_option( 'optimizer', $optimizer );
	
		//After Conversion Redirect to Customizer
        $redirect = admin_url('/customize.php'); 
		wp_redirect( $redirect);
	}
}