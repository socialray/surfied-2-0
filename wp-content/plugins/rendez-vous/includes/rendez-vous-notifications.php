<?php
/**
 * Rendez Vous Notifications.
 *
 * Screen and Email notification functions
 *
 * @package Rendez Vous
 * @subpackage Notifications
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays a checkbox to allow notifications to be sent
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_notifications_edit_form() {
	?>
	<p>
		<label for="rendez-vous-edit-notify" class="normal">
			<input type="checkbox" id="rendez-vous-edit-notify" name="_rendez_vous_edit[notify]" value="1" checked="true"> <?php esc_html_e( 'Notify attendees of changes about this rendez-vous', 'rendez-vous' );?>
		</label>
	</p>
	<?php
}
add_action( 'rendez_vous_edit_form_after_dates', 'rendez_vous_notifications_edit_form', 9 );

/**
 * Let the user customize email settings
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_screen_notification_settings() {
	$user_id = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();

	$schedule_notifications = get_user_meta( $user_id, 'notification_rendez_vous_schedule', true );
	$attend_notifications   = get_user_meta( $user_id, 'notification_rendez_vous_attend', true );

	if ( empty( $schedule_notifications ) ) {
		$schedule_notifications = 'yes';
	}

	if ( empty( $attend_notifications ) ) {
		$attend_notifications = 'yes';
	}
	?>
	<table class="notification-settings" id="rendez-vous-notification-settings">

		<thead>
		<tr>
			<th class="icon"></th>
			<th class="title"><?php esc_html_e( 'Rendez-Vous', 'rendez-vous' ) ?></th>
			<th class="yes"><?php esc_html_e( 'Yes', 'rendez-vous' ) ?></th>
			<th class="no"><?php esc_html_e( 'No', 'rendez-vous' )?></th>
		</tr>
		</thead>

		<tbody>
		<tr>
			<td></td>
			<td><?php esc_html_e( 'An attendee replyed about a rendez-vous you scheduled', 'rendez-vous' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_rendez_vous_schedule]" value="yes" <?php checked( 'yes', $schedule_notifications ) ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_rendez_vous_schedule]" value="no" <?php checked( 'no', $schedule_notifications ) ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php esc_html_e( 'You are invited to participate to a rendez-vous', 'rendez-vous' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_rendez_vous_attend]" value="yes" <?php checked( 'yes', $attend_notifications ) ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_rendez_vous_attend]" value="no" <?php checked( 'no', $attend_notifications ) ?>/></td>
		</tr>

		<?php do_action( 'rendez_vous_screen_notification_settings' ); ?>

		</tbody>
	</table>
<?php
}
add_action( 'bp_notification_settings', 'rendez_vous_screen_notification_settings' );

/**
 * Send a screen notifications & email one when a rendez vous is published
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_published_notification( $id = 0, $args = array(), $notify = 0 ) {
	$bp = buddypress();

	if ( empty( $id ) || empty( $notify ) ) {
		return;
	}

	$rendez_vous = rendez_vous_get_item( $id );
	$attendees = $rendez_vous->attendees;

	if ( empty( $attendees ) ) {
		return;
	}

	// Remove the organizer
	if ( in_array( $rendez_vous->organizer, $attendees ) ) {
		$attendees = array_diff( $attendees, array( $rendez_vous->organizer ) );
	}

	$organizer_name = bp_core_get_user_displayname( $rendez_vous->organizer );
	$rendez_vous_link = rendez_vous_get_single_link( $id, $rendez_vous->organizer );
	$rendez_vous_content = stripslashes( $rendez_vous->title ) . "\n\n" . stripslashes( $rendez_vous->description );
	$organizer_name = stripslashes( $organizer_name );

	// Append the custom message, if any
	if ( ! empty( $args['message'] ) ) {
		$rendez_vous_content .= "\n\n" . $args['message'];
	}

	$rendez_vous_content = wp_kses( $rendez_vous_content, array() );

	$message = sprintf(
		__( "%s is scheduling a new rendez-vous: %s\n\nTo help him fix the date, please log in and visit: %s\n\n---------------------\n", 'rendez-vous' ),
		$organizer_name,
		$rendez_vous_content,
		esc_url( $rendez_vous_link )
	);

	$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s invited you to a new rendez-vous', 'rendez-vous' ), $organizer_name ) ) );

	// This way we'll have all needed users data at one and we will avoid spam users
	$users = bp_core_get_users( array(
		'type' => 'alphabetical',
		'include' => implode( ',', $attendees )
	) );

	foreach ( $users['users'] as $attendee ) {

		$mail_content = $message;

		// Screen Notification
		bp_notifications_add_notification( array(
			'user_id'           => $attendee->ID,
			'item_id'           => $id,
			'secondary_item_id' => $rendez_vous->organizer,
			'component_name'    => $bp->rendez_vous->id,
			'component_action'  => 'rendez_vous_attend',
		) );

		// Sending Emails
		if ( 'no' == get_user_meta( $attendee->ID, 'notification_rendez_vous_attend', true ) ) {
			continue;
		}

		$user_settings = false;
		$user_fullname = ! empty( $attendee->fullname ) ? $attendee->fullname : $attendee->display_name;
		$user_profile = bp_core_get_user_domain( $attendee->ID, $attendee->user_nicename, $attendee->user_login );
		$user_email = $attendee->user_email;

		// Set up and send the message
		$to = $attendee->user_email;

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$user_settings = trailingslashit( $user_profile . bp_get_settings_slug() . '/notifications' );
			$mail_content .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'rendez-vous' ), esc_url( $user_settings ) );
		}

		/* Send the message */
		$to           = apply_filters( 'rendez_vous_published_notify_attendee', $to );
		$subject      = apply_filters( 'rendez_vous_published_notify_subject', $subject, $organizer_name );
		$mail_content = apply_filters( 'rendez_vous_published_notify_message', $mail_content, $rendez_vous, $organizer_name, $rendez_vous_content, $rendez_vous_link, $user_settings );

		wp_mail( $to, $subject, $mail_content );
	}

}
add_action( 'rendez_vous_after_publish', 'rendez_vous_published_notification', 10, 3 );

/**
 * Help to check if a notification needs to be send
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_maybe_notify_updates( $args = array(), $notify = 0 ) {
	$rendez_vous = rendez_vous();

	if ( empty( $notify ) || empty( $args ) ) {
		return;
	}

	$rendez_vous->item = rendez_vous_get_item( absint( $args['id'] ) );

	if ( empty( $rendez_vous->item ) ) {
		return;
	}

	if ( empty( $rendez_vous->item->def_date ) && ! empty( $args['def_date'] ) ) {
		$rendez_vous->item->date_fixed = $args['def_date'];
	}

	if ( empty( $rendez_vous->item->report ) && ! empty( $args['report'] ) ) {
		$rendez_vous->item->report_created = 1;
	}
}
add_action( 'rendez_vous_before_update', 'rendez_vous_maybe_notify_updates', 10, 2 );

/**
 * Send a screen notifications & email one when a rendez vous is updated
 *
 * Date set or report created
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_updated_notification( $id = 0, $args = array(), $notify = 0 ) {
	$bp = buddypress();
	$rdv = rendez_vous();

	if ( empty( $id ) || empty( $notify ) || empty( $rdv->item ) || $id != $rdv->item->id || empty( $args['message'] ) ) {
		return;
	}

	$has_updated = ! empty( $rdv->item->date_fixed ) || ! empty( $rdv->item->report_created ) ? true : false ;

	if ( empty( $has_updated ) && empty( $args['message'] ) ) {
		return;
	}

	// Only allow 1 message per day.
	if ( empty( $has_updated )  && 1 == get_transient( 'rendez_vous_last_message_' . $id ) ) {
		return;
	}

	$rendez_vous = $rdv->item;
	$attendees = $rendez_vous->attendees;

	if ( empty( $attendees ) ) {
		return;
	}

	// Remove the organizer
	if ( in_array( $rendez_vous->organizer, $attendees ) ) {
		$attendees = array_diff( $attendees, array( $rendez_vous->organizer ) );
	}

	$organizer_name = bp_core_get_user_displayname( $rendez_vous->organizer );
	$rendez_vous_link = rendez_vous_get_single_link( $id, $rendez_vous->organizer );

	$rendez_vous_content = stripslashes( $rendez_vous->title );
	$rdv_updated_action = false;
	$component_action = false;

	if ( ! empty( $rdv->item->date_fixed ) ) {
		if ( is_numeric( $rdv->item->date_fixed ) ) {
			$rendez_vous_content .= "\n\n" . sprintf( __( 'Date fixed to %1$s at %2$s', 'rendez-vous' ),
				date_i18n( get_option('date_format'), $rdv->item->date_fixed ),
				date_i18n( get_option('time_format'), $rdv->item->date_fixed )
			);
			$rendez_vous_content .= "\n\n" . sprintf( __( 'Download the Calendar file %s', 'rendez-vous' ), rendez_vous_get_ical_link( $id, $rendez_vous->organizer ) );
		} else {
			$rendez_vous_content .= "\n\n" . sprintf( __( 'Date fixed to %s', 'rendez-vous' ),
				esc_html( $rdv->item->date_fixed )
			);
		}

		$rdv_updated_action = __( 'fixed the date', 'rendez-vous' );
		$component_action = 'rendez_vous_fixed';
	} else if ( ! empty( $rdv->item->report_created ) ) {
		$rdv_updated_action = __( 'created the report', 'rendez-vous' );
		$component_action = 'rendez_vous_report';
	} else if ( ! empty( $args['message'] ) ) {
		$rdv_updated_action = __( 'sent a message', 'rendez-vous' );
		$component_action = 'rendez_vous_message';
		set_transient( 'rendez_vous_last_message_' . $id, 1, 24 * HOUR_IN_SECONDS );
	}

	$organizer_name = stripslashes( $organizer_name );

	// Append the custom message, if any
	if ( ! empty( $args['message'] ) ) {
		$rendez_vous_content .= "\n\n" . $args['message'];
	}

	$rendez_vous_content = wp_kses( $rendez_vous_content, array() );

	$message = sprintf(
		__( "%s %s for the rendez-vous: %s\n\nTo view details, log in and visit: %s\n\n---------------------\n", 'rendez-vous' ),
		$organizer_name,
		$rdv_updated_action,
		$rendez_vous_content,
		esc_url( $rendez_vous_link )
	);

	$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s updated a rendez-vous', 'rendez-vous' ), $organizer_name ) ) );

	// This way we'll have all needed users data at one and we will avoid spam users
	$users = bp_core_get_users( array(
		'type' => 'alphabetical',
		'include' => implode( ',', $attendees )
	) );

	foreach ( $users['users'] as $attendee ) {

		$mail_content = $message;

		if ( 'rendez_vous_message' != $component_action ) {
			// Screen Notification
			bp_notifications_add_notification( array(
				'user_id'           => $attendee->ID,
				'item_id'           => $id,
				'secondary_item_id' => $rendez_vous->organizer,
				'component_name'    => $bp->rendez_vous->id,
				'component_action'  => $component_action,
			) );
		}

		// Sending Emails
		if ( 'no' == get_user_meta( $attendee->ID, 'notification_rendez_vous_attend', true ) ) {
			continue;
		}

		$user_settings = false;
		$user_fullname = ! empty( $attendee->fullname ) ? $attendee->fullname : $attendee->display_name;
		$user_profile = bp_core_get_user_domain( $attendee->ID, $attendee->user_nicename, $attendee->user_login );
		$user_email = $attendee->user_email;

		// Set up and send the message
		$to = $attendee->user_email;

		// Only show the disable notifications line if the settings component is enabled
		if ( bp_is_active( 'settings' ) ) {
			$user_settings = trailingslashit( $user_profile . bp_get_settings_slug() . '/notifications' );
			$mail_content .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'rendez-vous' ), esc_url( $user_settings ) );
		}

		/* Send the message */
		$to           = apply_filters( 'rendez_vous_updated_notify_attendee', $to );
		$subject      = apply_filters( 'rendez_vous_updated_notify_subject', $subject, $organizer_name );
		$mail_content = apply_filters( 'rendez_vous_updated_notify_message', $mail_content, $rendez_vous, $organizer_name, $rendez_vous_content, $rendez_vous_link, $user_settings );

		wp_mail( $to, $subject, $mail_content );
	}

}
add_action( 'rendez_vous_after_update', 'rendez_vous_updated_notification', 10, 3 );


/**
 * Send a screen notifications & email one when an attendee set his preferences
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_notify_organizer( $args = array(), $attendee_id = 0, $rendez_vous = null ) {
	$bp = buddypress();

	if ( empty( $attendee_id ) || empty( $rendez_vous ) || $attendee_id == $rendez_vous->organizer ) {
		return;
	}

	// Screen Notification
	bp_notifications_add_notification( array(
		'user_id'           => $rendez_vous->organizer,
		'item_id'           => $rendez_vous->id,
		'secondary_item_id' => $attendee_id,
		'component_name'    => $bp->rendez_vous->id,
		'component_action'  => 'rendez_vous_schedule',
	) );

	// Sending Emails
	if ( 'no' == get_user_meta( $rendez_vous->organizer, 'notification_rendez_vous_schedule', true ) ) {
		return;
	}

	$rendez_vous_link = rendez_vous_get_single_link( $rendez_vous->id, $rendez_vous->organizer );
	$rendez_vous_title = stripslashes( $rendez_vous->title );
	$rendez_vous_content = wp_kses( $rendez_vous_title, array() );

	$attendee_name = bp_core_get_user_displayname( $attendee_id );
	$attendee_name = stripslashes( $attendee_name );

	$organizer_settings = false;
	$organizer_profile = bp_core_get_user_domain( $rendez_vous->organizer );
	$organizer_email = bp_core_get_user_email( $rendez_vous->organizer );

	$message = sprintf(
		__( "%s set their preferences for the rendez-vous: %s\n\nTo view details, log in and visit: %s\n\n---------------------\n", 'rendez-vous' ),
		$attendee_name,
		$rendez_vous_content,
		esc_url( $rendez_vous_link )
	);

	$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s selected date(s) for a rendez-vous', 'rendez-vous' ), $attendee_name ) ) );

	// Set up and send the message
	$to = $organizer_email;

	// Only show the disable notifications line if the settings component is enabled
	if ( bp_is_active( 'settings' ) ) {
		$organizer_settings = trailingslashit( $organizer_profile . bp_get_settings_slug() . '/notifications' );
		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'rendez-vous' ), esc_url( $organizer_settings ) );
	}

	/* Send the message */
	$to           = apply_filters( 'rendez_vous_userset_notify_organizer', $to );
	$subject      = apply_filters( 'rendez_vous_userset_notify_subject', $subject, $attendee_name );
	$mail_content = apply_filters( 'rendez_vous_userset_notify_message', $message, $rendez_vous, $attendee_name, $rendez_vous_content, $rendez_vous_link, $organizer_settings );

	wp_mail( $to, $subject, $mail_content );
}
add_action( 'rendez_vous_after_attendee_prefs', 'rendez_vous_notify_organizer', 10, 3 );


/**
 * Mark screen notifications
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_remove_all_attend_notifications() {

	if ( ! isset ( $_GET['n'] ) ) {
		return;
	}

	$bp = buddypress();

	/**
	 * Would be nice if BuddyPress allowed marking notifications for an array of actions..
 	 */
	bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->rendez_vous->id, 'rendez_vous_attend' );
	bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->rendez_vous->id, 'rendez_vous_fixed' );
	bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->rendez_vous->id, 'rendez_vous_report' );
}
add_action( 'rendez_vous_attend', 'rendez_vous_remove_all_attend_notifications' );


/**
 * Mark screen notifications
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_remove_current_attend_notifications() {

	if ( ! isset ( $_GET['rdv'] ) || ! isset ( $_GET['n'] ) ) {
		return;
	}

	$bp = buddypress();

	/**
	 * Removing all for the current user & current rendez-vous
 	 */
 	bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), $_GET['rdv'], buddypress()->rendez_vous->id, false );
}

add_action( 'rendez_vous_single_screen', 'rendez_vous_remove_current_attend_notifications' );


/**
 * Mark notifications
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_remove_all_schedule_notifications() {

	if ( ! isset( $_GET['n'] ) ) {
		return;
	}

	$bp = buddypress();

	/**
	 * Would be nice if BuddyPress allowed marking notifications for an array of actions..
 	 */
	bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->rendez_vous->id, 'rendez_vous_schedule' );
}
add_action( 'rendez_vous_schedule', 'rendez_vous_remove_all_schedule_notifications' );


/**
 * Format screen notifications
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	$bp = buddypress();

	switch ( $action ) {
		case 'rendez_vous_schedule':

			if ( (int) $total_items > 1 ) {
				$rendez_vous_link = add_query_arg(
					array( 'n' => $total_items ),
					trailingslashit( bp_loggedin_user_domain() . $bp->rendez_vous->slug . '/schedule' )
				);
				$title = __( 'rendez-vous preferences updated', 'rendez-vous' );
				$text = sprintf( __( '%d rendez-vous preferences updated', 'rendez-vous' ), (int) $total_items );
				$filter = 'rendez_vous_multiple_userset_notification';
			} else {
				$rendez_vous_link = add_query_arg(
					array( 'n' => 1 ),
					rendez_vous_get_single_link( $item_id, bp_loggedin_user_id() )
				);
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id, false );
				$title = __( 'View the rendez-vous', 'rendez-vous' );
				$text =  sprintf( __( '%s set his preferences about a rendez-vous', 'rendez-vous' ), $user_fullname );
				$filter = 'rendez_vous_single_userset_notification';
			}

		break;

		case 'rendez_vous_attend':

			if ( (int) $total_items > 1 ) {
				$rendez_vous_link = add_query_arg(
					array( 'n' => $total_items ),
					trailingslashit( bp_loggedin_user_domain() . $bp->rendez_vous->slug . '/attend' )
				);
				$title = __( 'rendez-vous sheduled', 'rendez-vous' );
				$text = sprintf( __( '%d rendez-vous sheduled', 'rendez-vous' ), (int) $total_items );
				$filter = 'rendez_vous_multiple_attend_notification';
			} else {
				$rendez_vous_link = add_query_arg(
					array( 'n' => 1 ),
					rendez_vous_get_single_link( $item_id, $secondary_item_id )
				);
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id, false );
				$title = __( 'View the rendez-vous', 'rendez-vous' );
				$text =  sprintf( __( '%s scheduled a rendez-vous', 'rendez-vous' ), $user_fullname );
				$filter = 'rendez_vous_single_attend_notification';
			}

		break;

		case 'rendez_vous_fixed':

			if ( (int) $total_items > 1 ) {
				$rendez_vous_link = add_query_arg(
					array( 'n' => $total_items ),
					trailingslashit( bp_loggedin_user_domain() . $bp->rendez_vous->slug . '/attend' )
				);
				$title = __( 'rendez-vous fixed', 'rendez-vous' );
				$text = sprintf( __( '%d rendez-vous fixed', 'rendez-vous' ), (int) $total_items );
				$filter = 'rendez_vous_multiple_fixed_notification';
			} else {
				$rendez_vous_link = add_query_arg(
					array( 'n' => 1 ),
					rendez_vous_get_single_link( $item_id, $secondary_item_id )
				);
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id, false );
				$title = __( 'View the rendez-vous', 'rendez-vous' );
				$text =  sprintf( __( '%s fixed a rendez-vous', 'rendez-vous' ), $user_fullname );
				$filter = 'rendez_vous_single_fixed_notification';
			}

		break;

		case 'rendez_vous_report':

			if ( (int) $total_items > 1 ) {
				$rendez_vous_link = add_query_arg(
					array( 'n' => $total_items ),
					trailingslashit( bp_loggedin_user_domain() . $bp->rendez_vous->slug . '/attend' )
				);
				$title = __( 'rendez-vous report created', 'rendez-vous' );
				$text = sprintf( __( '%d rendez-vous reports created', 'rendez-vous' ), (int) $total_items );
				$filter = 'rendez_vous_multiple_report_notification';
			} else {
				$rendez_vous_link = add_query_arg(
					array( 'n' => 1 ),
					rendez_vous_get_single_link( $item_id, $secondary_item_id )
				);
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id, false );
				$title = __( 'View the rendez-vous', 'rendez-vous' );
				$text =  sprintf( __( '%s created a report for a rendez-vous', 'rendez-vous' ), $user_fullname );
				$filter = 'rendez_vous_single_report_notification';
			}

		break;
	}

	/**
	 * If on notifications read screen remove the n arguments to
	 * avoid re runing the mark notification function
	 */

	if ( bp_is_user_notifications() && bp_is_current_action( 'read' ) ) {
		$rendez_vous_link = remove_query_arg( 'n', $rendez_vous_link );
	}

	if ( 'string' == $format ) {
		$return = apply_filters( $filter, '<a href="' . esc_url( $rendez_vous_link ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $text ) . '</a>', $rendez_vous_link, (int) $total_items, $item_id, $secondary_item_id );
	} else {
		$return = apply_filters( $filter, array(
			'text' => $text,
			'link' => esc_url( $rendez_vous_link )
		), $rendez_vous_link, (int) $total_items, $item_id, $secondary_item_id );
	}

	do_action( 'rendez_vous_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Deletes notifications for a cancelled rendez-vous
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_delete_item_notifications( $rendez_vous_id = 0, $rendez_vous = null ) {
	if ( empty( $rendez_vous_id ) ) {
		return;
	}

	// No need to delete activities in case of drafts
	if ( ! empty( $rendez_vous ) && 'draft' == $rendez_vous->post_status ) {
		return;
	}

	// delete all notifications
	BP_Notifications_Notification::delete( array(
		'item_id'           => $rendez_vous_id,
		'component_name'    => buddypress()->rendez_vous->id,
	) );

}
add_action( 'rendez_vous_after_delete', 'rendez_vous_delete_item_notifications', 9, 2 );

/**
 * Remove a user's notification data.
 *
 * @package Rendez Vous
 * @subpackage Notifications
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_remove_all_user_notifications( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	// delete all notifications user sent to others
	BP_Notifications_Notification::delete( array(
		'secondary_item_id' => $user_id,
		'component_name'    => buddypress()->rendez_vous->id,
	) );
}
add_action( 'wpmu_delete_user',  'rendez_vous_remove_all_user_notifications', 10, 1 );
add_action( 'delete_user',       'rendez_vous_remove_all_user_notifications', 10, 1 );
