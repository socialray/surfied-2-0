<?php
/* sidebar widgets */
// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

/* Register widgets for BP Checkins */
function bp_checkins_places_register_widgets() {
	global $blog_id;
	
	if( $blog_id == BP_ROOT_BLOG )
		add_action('widgets_init', create_function('', 'return register_widget("BP_Checkins_Places_Widget");') );
	
}
add_action( 'plugins_loaded', 'bp_checkins_places_register_widgets', 9 );

/*** PLACES WIDGET *****************/

class BP_Checkins_Places_Widget extends WP_Widget {

	function bp_checkins_places_widget() {
		$this->__construct();
	}

	function __construct() {
		$widget_ops = array( 'description' => __( 'Displays live, upcoming or regular community places', 'bp-checkins' ) );
		parent::__construct( false, $name = __( 'Community Places', 'bp-checkins' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		global $bp;

		extract( $args );
		
		if( bp_checkins_is_group_places_area() || ( bp_checkins_is_user_area() && bp_is_current_action('places-area') ) || bp_checkins_if_single_place() )
			return false;

		if ( !$instance['max_places'] )
			$instance['max_places'] = 5;
			
		if ( !$instance['places_type'] )
			$instance['places_type'] = -1;
		
		if ( $instance['dynamic'] === false )
			$instance['dynamic'] = 1;

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title; ?>
		
		<?php
		
		$widget_args = array( 'per_page' => $instance['max_places'], 'type' => $instance['places_type'] );
		
		
		if( (int)$instance['dynamic'] == 1 && bp_displayed_user_id() )
			$widget_args['user_id'] = bp_displayed_user_id();
			
		if( (int)$instance['dynamic'] == 1 && bp_is_groups_component() && bp_is_single_item() && $bp->groups->current_group->status =='public')
			$widget_args['group_id'] = $bp->groups->current_group->id;
		
		?>
		
		<?php if( !empty( $widget_args['group_id'] ) || !empty( $widget_args['user_id'] ) ):?>
			
			<div class="item-options">
				
				<?php 
					
					if( bp_displayed_user_id() ){
						printf( __('%s&#039;s places', 'bp-checkins'), bp_core_fetch_avatar( array( 'item_id' => bp_displayed_user_id(), 'object' => 'user', 'type' => 'thumb', 'width' => 20, 'height' => 20) ) . bp_core_get_userlink( bp_displayed_user_id() ) );
					}
					
					if( bp_is_groups_component() && bp_is_single_item() ) {
						 printf( __('%s&#039;s places', 'bp-checkins'), bp_core_fetch_avatar( array( 'item_id' => $bp->groups->current_group->id, 'object' => 'group', 'type' => 'thumb', 'width' => 20, 'height' => 20) ) . '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>');
					}
					
				?>	
				
			</div>
		
		<?php endif;?>

		<?php if ( bp_checkins_has_places( $widget_args ) ) : ?>
			
			<ul id="widget-places-list" class="item-list">
				<?php while ( bp_checkins_has_places() ) : bp_checkins_the_place(); ?>

					<li>
						<div class="item-avatar">
							<?php bp_checkins_places_avatar();?>
						</div>
						<div class="item-title">
							<a href="<?php bp_checkins_places_the_permalink();?>" title="<?php bp_checkins_places_title(); ?>"><?php bp_checkins_places_title(); ?></a>
						</div>
						<?php if( bp_checkins_places_is_live() ) :?>
							<div class="item-meta">
								<span class="activity"><?php bp_checkins_places_live_status();?></span>
							</div>
						<?php endif;?>
						<div class="clear"></div>
					</li>

				<?php endwhile; ?>

		<?php else : ?>

			<div class="widget-error">
				<p><?php _e( 'Sorry, there was no places found.', 'bp-checkins' ); ?></p>
			</div>

		<?php endif; ?>


		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_places'] = strip_tags( $new_instance['max_places'] );
		$instance['places_type'] = strip_tags( $new_instance['places_type'] );
		$instance['dynamic'] = strip_tags( $new_instance['dynamic'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => __( 'Community Places', 'bp-checkins' ),
			'max_places' => 5,
			'places_type' => -1,
			'dynamic' => 1
			
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = strip_tags( $instance['title'] );
		$max_places = strip_tags( $instance['max_places'] );
		$places_type = strip_tags( $instance['places_type'] );
		$dynamic = intval( $instance['dynamic'] );
		?>

		<p><label for="bp-checkins-places-widget-title"><?php _e('Title:', 'bp-checkins'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="bp-checkins-places-widget-max-places"><?php _e('Max places to show:', 'bp-checkins'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_snippets' ); ?>" name="<?php echo $this->get_field_name( 'max_places' ); ?>" type="text" value="<?php echo esc_attr( $max_places ); ?>" style="width: 30%" /></label></p>
		
		<p><label for="bp-checkins-places-widget-places-type"><?php _e('Places type:', 'bp-checkins'); ?></label><br/>
			<input type="radio" style="width:10%" name="<?php echo $this->get_field_name( 'places_type' ); ?>" value="-1" <?php checked('-1', $places_type );?>><?php _e('Everything', 'bp-checkins');?><br/>
			<input type="radio" style="width:10%" name="<?php echo $this->get_field_name( 'places_type' ); ?>" value="all_live_places" <?php checked('all_live_places', $places_type );?>><?php _e('Live Places', 'bp-checkins');?><br/>
			<input type="radio" style="width:10%" name="<?php echo $this->get_field_name( 'places_type' ); ?>" value="upcoming_places" <?php checked('upcoming_places', $places_type );?>><?php _e('Upcoming Places', 'bp-checkins');?><br/>
			</select>
				
		</p>

		<p><label for="bp-checkins-places-widget-dynamic"><?php _e('Show places from the user or the group displayed:', 'bp-checkins'); ?></label><br/>
			<input id="<?php echo $this->get_field_id( 'dynamic' ); ?>-yes" name="<?php echo $this->get_field_name( 'dynamic' ); ?>" type="radio" value="1" <?php checked(1, $dynamic);?>/><?php _e('Yes', 'bp-checkins');?>
			<input id="<?php echo $this->get_field_id( 'dynamic' ); ?>-no" name="<?php echo $this->get_field_name( 'dynamic' ); ?>" type="radio" value="0" <?php checked(0, $dynamic);?>/><?php _e('No', 'bp-checkins');?>
		</p>

	<?php
	}
}
?>