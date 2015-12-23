<?php

/**
 * Events Manager
 * @since 1.1
 * @version 1.3
 */
if ( defined( 'myCRED_VERSION' ) ) {

	/**
	 * Register Hook
	 * @since 1.1
	 * @version 1.0
	 */
	add_filter( 'mycred_setup_hooks', 'Events_Manager_myCRED_Hook' );
	function Events_Manager_myCRED_Hook( $installed ) {
		$installed['eventsmanager'] = array(
			'title'       => __( 'Events Manager', 'mycred' ),
			'description' => __( 'Awards %_plural% for users attending events.', 'mycred' ),
			'callback'    => array( 'myCRED_Hook_Events_Manager' )
		);
		return $installed;
	}

	/**
	 * Events Manager Hook
	 * @since 1.1
	 * @version 1.3
	 */
	if ( ! class_exists( 'myCRED_Hook_Events_Manager' ) && class_exists( 'myCRED_Hook' ) ) {
		class myCRED_Hook_Events_Manager extends myCRED_Hook {

			/**
			 * Construct
			 */
			function __construct( $hook_prefs, $type = 'mycred_default' ) {
				parent::__construct( array(
					'id'       => 'eventsmanager',
					'defaults' => array(
						'attend' => array(
							'creds' => 1,
							'log'   => '%plural% for attending an event',
							'limit' => '0/x'
						),
						'cancel' => array(
							'creds' => 1,
							'log'   => '%plural% for cancelled attendance an event'
						)
					)
				), $hook_prefs, $type );
			}

			/**
			 * Run
			 * @since 1.1
			 * @version 1.2
			 */
			public function run() {

				if ( get_option( 'dbem_multiple_bookings' ) )
					add_action( 'em_bookings_added', array( $this, 'multiple_bookings' ) );
				else
					add_filter( 'em_bookings_add',   array( $this, 'new_booking' ), 10, 2 );

				add_filter( 'em_booking_set_status', array( $this, 'adjust_booking' ), 10, 2 );

			}

			/**
			 * New Booking
			 * When users can make their own bookings.
			 * @since 1.1
			 * @version 1.3
			 */
			public function new_booking( $result, $booking ) {
				// If bookings get automatically approved and booking was successfully added, add points
				if ( get_option( 'dbem_bookings_approval' ) == 0 && $result === true ) {

					// Check for exclusion
					if ( $this->core->exclude_user( $booking->person_id ) ) return $result;

					// Limit
					if ( ! $this->over_hook_limit( 'attend', 'event_booking', $booking->person_id ) )
						$this->core->add_creds(
							'event_booking',
							$booking->person_id,
							$this->prefs['attend']['creds'],
							$this->prefs['attend']['log'],
							$booking->event->post_id,
							array( 'ref_type' => 'post' ),
							$this->mycred_type
						);

				}

				return $result;
			}

			/**
			 * New Multiple Bookings
			 * @since 1.5.4
			 * @version 1.1
			 */
			public function multiple_bookings( $bookings ) {

				foreach ( $bookings->get_bookings() as $EM_Booking ) {

					// Check for exclusion
					if ( $this->core->exclude_user( $EM_Booking->person_id ) ) continue;

					// Limit
					if ( ! $this->over_hook_limit( 'attend', 'event_booking', $EM_Booking->person_id ) )
						$this->core->add_creds(
							'event_booking',
							$EM_Booking->person_id,
							$this->prefs['attend']['creds'],
							$this->prefs['attend']['log'],
							$EM_Booking->event->post_id,
							array( 'ref_type' => 'post' ),
							$this->mycred_type
						);

				}

			}

			/**
			 * Adjust Booking
			 * Incase an administrator needs to approve bookings first or if booking gets
			 * cancelled.
			 * @since 1.1
			 * @version 1.3
			 */
			public function adjust_booking( $result, $booking ) {

				// Check for exclusion
				if ( $this->core->exclude_user( $booking->person_id ) ) return $result;

				// If the new status is 'approved', add points
				if ( $booking->booking_status == 1 && $booking->previous_status != 1 ) {
					// If we do not award points for attending an event bail now
					if ( $this->prefs['attend']['creds'] == 0 ) return $result;

					// Limit
					if ( ! $this->over_hook_limit( 'attend', 'event_attendance', $booking->person_id ) )
						$this->core->add_creds(
							'event_attendance',
							$booking->person_id,
							$this->prefs['attend']['creds'],
							$this->prefs['attend']['log'],
							$booking->event->post_id,
							array( 'ref_type' => 'post' ),
							$this->mycred_type
						);
				}

				// Else if status got changed from previously 'approved', remove points given
				elseif ( $booking->booking_status != 1 && $booking->previous_status == 1 ) {
					// If we do not deduct points for cancellation bail now
					if ( $this->prefs['cancel']['creds'] == 0 ) return $result;

					// Execute
					$this->core->add_creds(
						'cancelled_event_attendance',
						$booking->person_id,
						$this->prefs['cancel']['creds'],
						$this->prefs['cancel']['log'],
						$booking->event->post_id,
						array( 'ref_type' => 'post' ),
						$this->mycred_type
					);
				}

				return $result;

			}

			/**
			 * Preferences for Events Manager
			 * @since 1.1
			 * @version 1.0.1
			 */
			public function preferences() {
				$prefs = $this->prefs; ?>

<label class="subheader" for="<?php echo $this->field_id( array( 'attend' => 'creds' ) ); ?>"><?php _e( 'Attending Event', 'mycred' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'attend' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'attend' => 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['attend']['creds'] ); ?>" size="8" /></div>
	</li>
	<li>
		<label for="<?php echo $this->field_id( array( 'attend', 'limit' ) ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
		<?php echo $this->hook_limit_setting( $this->field_name( array( 'attend', 'limit' ) ), $this->field_id( array( 'attend', 'limit' ) ), $prefs['attend']['limit'] ); ?>
	</li>
</ol>
<label class="subheader" for="<?php echo $this->field_id( array( 'attend' => 'log' ) ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'attend' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'attend' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['attend']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
</ol>
<label class="subheader" for="<?php echo $this->field_id( array( 'cancel' => 'creds' ) ); ?>"><?php _e( 'Cancelling Attendance', 'mycred' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'cancel' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'cancel' => 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['cancel']['creds'] ); ?>" size="8" /></div>
	</li>
</ol>
<label class="subheader" for="<?php echo $this->field_id( array( 'cancel' => 'log' ) ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'cancel' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'cancel' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['cancel']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
</ol>
<?php
			}
			
			/**
			 * Sanitise Preferences
			 * @since 1.6
			 * @version 1.0
			 */
			function sanitise_preferences( $data ) {

				if ( isset( $data['attend']['limit'] ) && isset( $data['attend']['limit_by'] ) ) {
					$limit = sanitize_text_field( $data['attend']['limit'] );
					if ( $limit == '' ) $limit = 0;
					$data['attend']['limit'] = $limit . '/' . $data['attend']['limit_by'];
					unset( $data['attend']['limit_by'] );
				}

				return $data;

			}
		}
	}
}
?>