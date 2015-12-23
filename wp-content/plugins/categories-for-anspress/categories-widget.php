<?php
class AnsPress_Category_Widget extends WP_Widget {

	public function AnsPress_Category_Widget() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Categories', array('description', __('Display AnsPress categories', 'ap')) );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array(
			'parent' 		=> $instance['parent'],
			'number'		=> $instance['number'],
			'hide_empty'    => $instance['hide_empty'],
			'orderby'       => $instance['orderby'],
			'order'         => $instance['order'],
		);

		$categories = get_terms( 'question_category' , $cat_args);
		?>

		<ul id="ap-categories-widget" class="ap-cat-wid clearfix">
			<?php
			foreach($categories as $key => $category) :
				$sub_cat_count = count(get_term_children( $category->term_id, 'question_category' ));
			?>
				<li class="clearfix">
					<a class="ap-cat-image" href="<?php echo get_category_link( $category );?>"><?php echo ap_get_category_image($category->term_id); ?></a>
					<a class="ap-cat-wid-title" href="<?php echo get_category_link( $category );?>">
						<?php echo $category->name; ?>
					</a>
					<div class="ap-cat-count">
						<span><?php printf(_n('%d Question', '%d Questions', $category->count), $category->count); ?></span>
						<?php if($sub_cat_count > 0) : ?>
							<span><?php printf(__('%d Child', 'ap'), $sub_cat_count); ?></span>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];
		else
			$title = __( 'Categories', 'ap' );

		if ( isset( $instance[ 'hide_empty' ] ) )
			$hide_empty = $instance[ 'hide_empty' ];
		else
			$hide_empty = false;

		if ( isset( $instance[ 'parent' ] ) )
			$parent = $instance[ 'parent' ];
		else
			$parent = 0;

		if ( isset( $instance[ 'number' ] ) )
			$number = $instance[ 'number' ];
		else
			$number = 10;

		if ( isset( $instance[ 'orderby' ] ) )
			$orderby = $instance[ 'orderby' ];
		else
			$orderby = 'count';

		if ( isset( $instance[ 'order' ] ) )
			$order = $instance[ 'order' ];
		else
			$order = 'DESC';

		$cat_args = array(
			'hide_empty'    => false,
			'orderby'       => 'count',
			'order'         => 'DESC',
		);
		$categories = get_terms( 'question_category' , $cat_args);

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ap' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide empty:', 'ap' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" value="1" <?php checked( true, $hide_empty);?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'parent' ); ?>"><?php _e( 'Parent:', 'ap' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'parent' ); ?>" name="<?php echo $this->get_field_name( 'parent' ); ?>">
				<option value="0"><?php _e('Top level', 'ap'); ?></option>
				<?php
					if($categories)
					foreach($categories as $c)
						echo '<option value="'.$c->term_id.'" '.selected($parent, $c->term_id ).'>'.$c->name.'</option>';

				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number:', 'ap' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By:', 'ap' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
				<option value="none" <?php echo selected($orderby, 'none' ); ?>><?php _e('None', 'ap'); ?></option>
				<option value="count" <?php echo selected($orderby, 'count' ); ?>><?php _e('Count', 'ap'); ?></option>
				<option value="id" <?php echo selected($orderby, 'id' ); ?>><?php _e('ID', 'ap'); ?></option>
				<option value="name" <?php echo selected($orderby, 'name' ); ?>><?php _e('Name', 'ap'); ?></option>
				<option value="slug" <?php echo selected($orderby, 'slug' ); ?>><?php _e('Slug', 'ap'); ?></option>
				<option value="term_group" <?php echo selected($orderby, 'term_group' ); ?>><?php _e('Term group', 'ap'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', 'ap' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
				<option value="DESC" <?php echo selected($order, 'DESC' ); ?>><?php _e('DESC', 'ap'); ?></option>
				<option value="ASC" <?php echo selected($order, 'ASC' ); ?>><?php _e('ASC', 'ap'); ?></option>
			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['hide_empty'] = ( ! empty( $new_instance['hide_empty'] ) ) ? strip_tags( $new_instance['hide_empty'] ) : false;
		$instance['parent'] = ( ! empty( $new_instance['parent'] ) ) ? strip_tags( $new_instance['parent'] ) : '0';
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : '5';
		$instance['orderby'] = ( ! empty( $new_instance['orderby'] ) ) ? strip_tags( $new_instance['orderby'] ) : 'count';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : 'DESC';

		return $instance;
	}
}
