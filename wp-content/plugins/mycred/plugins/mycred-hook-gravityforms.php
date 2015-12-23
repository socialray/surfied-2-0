<?php

/**
 * Gravity Forms
 * @since 1.4
 * @version 1.2
 */
if ( defined( 'myCRED_VERSION' ) ) {

	/**
	 * Register Hook
	 * @since 1.4
	 * @version 1.0
	 */
	add_filter( 'mycred_setup_hooks', 'gravity_forms_myCRED_Hook' );
	function gravity_forms_myCRED_Hook( $installed ) {
		$installed['gravityform'] = array(
			'title'       => __( 'Gravityform Submissions', 'mycred' ),
			'description' => __( 'Awards %_plural% for successful form submissions.', 'mycred' ),
			'callback'    => array( 'myCRED_Gravity_Forms' )
		);
		return $installed;
	}

	/**
	 * Gravity Forms Hook
	 * @since 1.4
	 * @version 1.1
	 */
	if ( ! class_exists( 'myCRED_Gravity_Forms' ) && class_exists( 'myCRED_Hook' ) ) {
		class myCRED_Gravity_Forms extends myCRED_Hook {

			/**
			 * Construct
			 */
			function __construct( $hook_prefs, $type = 'mycred_default' ) {
				parent::__construct( array(
					'id'       => 'gravityform',
					'defaults' => array()
				), $hook_prefs, $type );
			}

			/**
			 * Run
			 * @since 1.4
			 * @version 1.0
			 */
			public function run() {
				add_action( 'gform_after_submission', array( $this, 'form_submission' ), 10, 2 );
			}

			/**
			 * Successful Form Submission
			 * @since 1.4
			 * @version 1.1
			 */
			public function form_submission( $lead, $form ) {
				// Login is required
				if ( ! is_user_logged_in() || ! isset( $lead['form_id'] ) ) return;

				// Prep
				$user_id = absint( $lead['created_by'] );
				$form_id = absint( $lead['form_id'] );

				// Make sure form is setup and user is not excluded
				if ( ! isset( $this->prefs[ $form_id ] ) || $this->core->exclude_user( $user_id ) ) return;

				// Limit
				if ( $this->over_hook_limit( $form_id, 'gravity_form_submission' ) ) return;

				// Default values
				$amount = $this->prefs[ $form_id ]['creds'];
				$entry = $this->prefs[ $form_id ]['log'];

				// See if the form contains myCRED fields that override these defaults
				if ( isset( $form['fields'] ) && ! empty( $form['fields'] ) ) {
					foreach ( (array) $form['fields'] as $field ) {

						// Amount override
						if ( $field['label'] == 'mycred_amount' ) {
							$amount = $this->core->number( $field['defaultValue'] );
						}

						// Entry override
						if ( $field['label'] == 'mycred_entry' ) {
							$entry = sanitize_text_field( $field['defaultValue'] );
						}

					}
				}

				// Amount can not be zero
				if ( $amount == 0 ) return;

				// Execute
				$this->core->add_creds(
					'gravity_form_submission',
					$user_id,
					$amount,
					$entry,
					$form_id,
					'',
					$this->mycred_type
				);
			}

			/**
			 * Preferences for Gravityforms Hook
			 * @since 1.4
			 * @version 1.0
			 */
			public function preferences() {
				$prefs = $this->prefs;
				$forms = RGFormsModel::get_forms();

				// No forms found
				if ( empty( $forms ) ) {
					echo '<p>' . __( 'No forms found.', 'mycred' ) . '</p>';
					return;
				}

				// Loop though prefs to make sure we always have a default setting
				foreach ( $forms as $form ) {
					if ( ! isset( $prefs[ $form->id ] ) ) {
						$prefs[ $form->id ] = array(
							'creds' => 1,
							'log'   => '',
							'limit' => '0/x'
						);
					}

					if ( ! isset( $prefs[ $form->id ]['limit'] ) )
						$prefs[ $form->id ]['limit'] = '0/x';
				}

				// Set pref if empty
				if ( empty( $prefs ) ) $this->prefs = $prefs;

				// Loop for settings
				foreach ( $forms as $form ) { ?>

<label for="<?php echo $this->field_id( array( $form->id, 'creds' ) ); ?>" class="subheader"><?php echo $form->title; ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $form->id, 'creds' ) ); ?>" id="<?php echo $this->field_id( array( $form->id, 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs[ $form->id ]['creds'] ); ?>" size="8" /></div>
	</li>
	<li>
		<label for="<?php echo $this->field_id( array( $form->id, 'limit' ) ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
		<?php echo $this->hook_limit_setting( $this->field_name( array( $form->id, 'limit' ) ), $this->field_id( array( $form->id, 'limit' ) ), $prefs[ $form->id ]['limit'] ); ?>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<label for="<?php echo $this->field_id( array( $form->id, 'log' ) ); ?>"><?php _e( 'Log template', 'mycred' ); ?></label>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( $form->id, 'log' ) ); ?>" id="<?php echo $this->field_id( array( $form->id, 'log' ) ); ?>" value="<?php echo esc_attr( $prefs[ $form->id ]['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
	</li>
</ol>
<?php			}
			}
			
			/**
			 * Sanitise Preferences
			 * @since 1.6
			 * @version 1.0
			 */
			function sanitise_preferences( $data ) {

				$forms = RGFormsModel::get_forms();
				foreach ( $forms as $form ) {

					if ( isset( $data[ $form->id ]['limit'] ) && isset( $data[ $form->id ]['limit_by'] ) ) {
						$limit = sanitize_text_field( $data[ $form->id ]['limit'] );
						if ( $limit == '' ) $limit = 0;
						$data[ $form->id ]['limit'] = $limit . '/' . $data[ $form->id ]['limit_by'];
						unset( $data[ $form->id ]['limit_by'] );
					}

				}

				return $data;

			}
		}
	}
}
?>