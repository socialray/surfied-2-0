<?php

/**
 * BadgeOS
 * @since 1.0.8
 * @version 1.1
 */
if ( defined( 'myCRED_VERSION' ) ) {

	/**
	 * Register Hook
	 * @since 1.0.8
	 * @version 1.2
	 */
	add_filter( 'mycred_setup_hooks', 'badgeOS_myCRED_Hook', 10, 2 );
	function badgeOS_myCRED_Hook( $installed, $type = 'mycred_default' ) {
		$installed['badgeos'] = array(
			'title'       => __( 'BadgeOS', 'mycred' ),
			'description' => __( 'Default settings for each BadgeOS Achievement type. These settings may be overridden for individual achievement type.', 'mycred' ),
			'callback'    => array( 'myCRED_Hook_BadgeOS' )
		);
		return $installed;
	}

	/**
	 * Exclude BadgeOS Post Types
	 * @since 1.0.8
	 * @version 1.0
	 */
	add_filter( 'mycred_post_type_excludes', 'mycred_exclude_post_type_badgeOS' );
	function mycred_exclude_post_type_badgeOS( $excludes ) {
		$excludes = array_merge( $excludes, badgeos_get_achievement_types_slugs() );
		return $excludes;
	}

	/**
	 * BadgeOS Hook
	 * @since 1.0.8
	 * @version 1.1
	 */
	if ( ! class_exists( 'myCRED_Hook_BadgeOS' ) && class_exists( 'myCRED_Hook' ) ) {
		class myCRED_Hook_BadgeOS extends myCRED_Hook {

			/**
			 * Construct
			 */
			function __construct( $hook_prefs, $type = 'mycred_default' ) {
				parent::__construct( array(
					'id'       => 'badgeos',
					'defaults' => ''
				), $hook_prefs, $type );

				$key = '_mycred_values';
				if ( ! $this->is_main_type )
					$key .= '_' . $this->mycred_type;

				$this->metakey = $key;
			}

			/**
			 * Run
			 * @since 1.0.8
			 * @version 1.0
			 */
			public function run() {
				add_action( 'add_meta_boxes',             array( $this, 'add_metaboxes' )             );
				add_action( 'save_post',                  array( $this, 'save_achivement_data' )      );

				add_action( 'badgeos_award_achievement',  array( $this, 'award_achievent' ), 10, 2    );
				add_action( 'badgeos_revoke_achievement', array( $this, 'revoke_achievement' ), 10, 2 );
			}

			/**
			 * Add Metaboxes
			 * @since 1.0.8
			 * @version 1.0
			 */
			public function add_metaboxes() {
				// Get all Achievement Types
				$badge_post_types = badgeos_get_achievement_types_slugs();
				foreach ( $badge_post_types as $post_type ) {
					// Add Meta Box
					add_meta_box(
						'mycred_badgeos_' . $post_type . '_' . $this->mycred_type,
						$this->core->plural(),
						array( $this, 'render_meta_box' ),
						$post_type,
						'side',
						'core'
					);
				}
			}

			/**
			 * Render Meta Box
			 * @since 1.0.8
			 * @version 1.1
			 */
			public function render_meta_box( $post ) {
				// Setup is needed
				if ( ! isset( $this->prefs[ $post->post_type ] ) ) {
					$page = 'myCRED_page_hooks';
					if ( ! $this->is_main_type )
						$page = 'myCRED_' . $this->mycred_type . '_page_hooks';
					$message = sprintf( __( 'Please setup your <a href="%s">default settings</a> before using this feature.', 'mycred' ), admin_url( 'admin.php?page=' . $page ) );
					echo '<p>' . $message . '</p>';
					return;
				}

				$post_key = 'mycred_values' . $this->mycred_type;

				// Prep Achievement Data
				$prefs = $this->prefs;
				$achievement_data = get_post_meta( $post->ID, $this->metakey, true );
				if ( $achievement_data == '' )
					$achievement_data = $prefs[ $post->post_type ]; ?>

<p><strong><?php echo $this->core->template_tags_general( __( '%plural% to Award', 'mycred' ) ); ?></strong></p>
<p>
	<label class="screen-reader-text" for="mycred-values-<?php echo $this->mycred_type; ?>-creds"><?php echo $this->core->template_tags_general( __( '%plural% to Award', 'mycred' ) ); ?></label>
	<input type="text" name="<?php echo $post_key; ?>[creds]" id="mycred-values-<?php echo $this->mycred_type; ?>-creds" value="<?php echo $this->core->number( $achievement_data['creds'] ); ?>" size="8" />
	<span class="description"><?php _e( 'Use zero to disable', 'mycred' ); ?></span>
</p>
<p><strong><?php _e( 'Log Template', 'mycred' ); ?></strong></p>
<p>
	<label class="screen-reader-text" for="mycred-values-<?php echo $this->mycred_type; ?>-log"><?php _e( 'Log Template', 'mycred' ); ?></label>
	<input type="text" name="<?php echo $post_key; ?>[log]" id="mycred-values-<?php echo $this->mycred_type; ?>-log" value="<?php echo esc_attr( $achievement_data['log'] ); ?>" style="width:99%;" />
</p>
<?php
			// If deduction is enabled
			if ( $this->prefs[ $post->post_type ]['deduct'] == 1 ) { ?>

<p><strong><?php _e( 'Deduction Log Template', 'mycred' ); ?></strong></p>
<p>
	<label class="screen-reader-text" for="mycred-values-<?php echo $this->mycred_type; ?>-log"><?php _e( 'Log Template', 'mycred' ); ?></label>
	<input type="text" name="<?php echo $post_key; ?>[deduct_log]" id="mycred-values-deduct-<?php echo $this->mycred_type; ?>-log" value="<?php echo esc_attr( $achievement_data['deduct_log'] ); ?>" style="width:99%;" />
</p>
<?php
				}
			}

			/**
			 * Save Achievement Data
			 * @since 1.0.8
			 * @version 1.2
			 */
			public function save_achivement_data( $post_id ) {
				// Post Type
				$post_type = get_post_type( $post_id );

				// Make sure this is a BadgeOS Object
				if ( ! in_array( $post_type, badgeos_get_achievement_types_slugs() ) ) return;

				$post_key = 'mycred_values' . $this->mycred_type;

				// Make sure preference is set
				if ( ! isset( $this->prefs[ $post_type ] ) || ! isset( $_POST[ $post_key ]['creds'] ) || ! isset( $_POST[ $post_key ]['log'] ) )
					return;

				// Only save if the settings differ, otherwise we default
				if ( $_POST[ $post_key ]['creds'] == $this->prefs[ $post_type ]['creds'] &&
					 $_POST[ $post_key ]['log'] == $this->prefs[ $post_type ]['log'] ) {
				
					delete_post_meta( $post_id, $this->metakey );
					return;	 
				}

				$data = array();

				// Creds
				if ( ! empty( $_POST[ $post_key ]['creds'] ) && $_POST[ $post_key ]['creds'] != $this->prefs[ $post_type ]['creds'] )
					$data['creds'] = $this->core->number( $_POST[ $post_key ]['creds'] );
				else
					$data['creds'] = $this->core->number( $this->prefs[ $post_type ]['creds'] );

				// Log template
				if ( ! empty( $_POST[ $post_key ]['log'] ) && $_POST[ $post_key ]['log'] != $this->prefs[ $post_type ]['log'] )
					$data['log'] = strip_tags( $_POST[ $post_key ]['log'] );
				else
					$data['log'] = strip_tags( $this->prefs[ $post_type ]['log'] );

				// If deduction is enabled save log template
				if ( $this->prefs[ $post_type ]['deduct'] == 1 ) {
					if ( ! empty( $_POST[ $post_key ]['deduct_log'] ) && $_POST[ $post_key ]['deduct_log'] != $this->prefs[ $post_type ]['deduct_log'] )
						$data['deduct_log'] = strip_tags( $_POST[ $post_key ]['deduct_log'] );
					else
						$data['deduct_log'] = strip_tags( $this->prefs[ $post_type ]['deduct_log'] );
				}

				// Update sales values
				update_post_meta( $post_id, $this->metakey, $data );
			}

			/**
			 * Award Achievement
			 * Run by BadgeOS when ever needed, we make sure settings are not zero otherwise
			 * award points whenever this hook fires.
			 * @since 1.0.8
			 * @version 1.1
			 */
			public function award_achievent( $user_id, $achievement_id ) {
				$post_type = get_post_type( $achievement_id );
				// Settings are not set
				if ( ! isset( $this->prefs[ $post_type ]['creds'] ) ) return;

				// Get achievemen data
				$achievement_data = get_post_meta( $achievement_id, $this->metakey, true );
				if ( $achievement_data == '' )
					$achievement_data = $this->prefs[ $post_type ];

				// Make sure its not disabled
				if ( $achievement_data['creds'] == 0 ) return;

				// Execute
				$post_type_object = get_post_type_object( $post_type );
				$this->core->add_creds(
					$post_type_object->labels->name,
					$user_id,
					$achievement_data['creds'],
					$achievement_data['log'],
					$achievement_id,
					array( 'ref_type' => 'post' ),
					$this->mycred_type
				);
			}

			/**
			 * Revoke Achievement
			 * Run by BadgeOS when a users achievement is revoed.
			 * @since 1.0.8
			 * @version 1.2
			 */
			public function revoke_achievement( $user_id, $achievement_id ) {
				$post_type = get_post_type( $achievement_id );
				// Settings are not set
				if ( ! isset( $this->prefs[ $post_type ]['creds'] ) ) return;

				// Get achievemen data
				$achievement_data = get_post_meta( $achievement_id, $this->metakey, true );
				if ( $achievement_data == '' )
					$achievement_data = $this->prefs[ $post_type ];

				// Make sure its not disabled
				if ( $achievement_data['creds'] == 0 ) return;

				// Execute
				$post_type_object = get_post_type_object( $post_type );
				$this->core->add_creds(
					$post_type_object->labels->name,
					$user_id,
					0-$achievement_data['creds'],
					$achievement_data['deduct_log'],
					$achievement_id,
					array( 'ref_type' => 'post' ),
					$this->mycred_type
				);
			}

			/**
			 * Preferences for BadgeOS
			 * @since 1.0.8
			 * @version 1.0
			 */
			public function preferences() {
				$prefs = $this->prefs;
				$badge_post_types = badgeos_get_achievement_types_slugs();
				foreach ( $badge_post_types as $post_type ) {
					if ( in_array( $post_type, apply_filters( 'mycred_badgeos_excludes', array( 'step' ) ) ) ) continue;
					if ( ! isset( $prefs[ $post_type ] ) )
						$prefs[ $post_type ] = array(
							'creds'      => 10,
							'log'        => '',
							'deduct'     => 1,
							'deduct_log' => '%plural% deduction'
						);

					$post_type_object = get_post_type_object( $post_type );
					$title = sprintf( __( 'Default %s for %s', 'mycred' ), $this->core->plural(), $post_type_object->labels->singular_name ); ?>

<label for="<?php echo $this->field_id( array( $post_type, 'creds' ) ); ?>" class="subheader"><?php echo $title; ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $post_type, 'creds' ) ); ?>" id="<?php echo $this->field_id( array( $post_type, 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs[ $post_type ]['creds'] ); ?>" size="8" /></div>
		<span class="description"><?php echo $this->core->template_tags_general( __( 'Use zero to disable users gaining %_plural%', 'mycred' ) ); ?></span>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<label for="<?php echo $this->field_id( array( $post_type, 'log' ) ); ?>"><?php _e( 'Default Log template', 'mycred' ); ?></label>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $post_type, 'log' ) ); ?>" id="<?php echo $this->field_id( array( $post_type, 'log' ) ); ?>" value="<?php echo esc_attr( $prefs[ $post_type ]['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
	<li>
		<input type="checkbox" name="<?php echo $this->field_name( array( $post_type, 'deduct' ) ); ?>" id="<?php echo $this->field_id( array( $post_type, 'deduct' ) ); ?>" <?php checked( $prefs[ $post_type ]['deduct'], 1 ); ?> value="1" />
		<label for="<?php echo $this->field_id( array( $post_type, 'deduct' ) ); ?>"><?php echo $this->core->template_tags_general( __( 'Deduct %_plural% if user looses ' . $post_type_object->labels->singular_name, 'mycred' ) ); ?></label>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<label for="<?php echo $this->field_id( array( $post_type, 'deduct_log' ) ); ?>"><?php _e( 'Log template', 'mycred' ); ?></label>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $post_type, 'deduct_log' ) ); ?>" id="<?php echo $this->field_id( array( $post_type, 'deduct_log' ) ); ?>" value="<?php echo esc_attr( $prefs[ $post_type ]['deduct_log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
</ol>
<?php			}
			}
		}
	}
}
?>