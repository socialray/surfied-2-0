<?php
/**
 * Rendez Vous Classes.
 *
 * Editor & Crud Classes
 *
 * @package Rendez Vous
 * @subpackage Classes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rendez Vous Editor Class.
 *
 * This class is used to create the
 * rendez-vous
 *
 * @since Rendez Vous (1.0.0)
 */
class Rendez_Vous_Editor {

	private static $settings = array();

	private function __construct() {}

	/**
	 * Set the settings
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public static function set( $editor_id, $settings ) {
		$set = bp_parse_args( $settings,  array(
			'component'       => 'rendez_vous',
			'status'          => 'public',
			'btn_caption'     => __( 'New Rendez-vous', 'rendez-vous' ),
			'btn_class'       => 'btn-rendez-vous',
			'action'          => 'rendez_vous_create',
			'group_id'        => null,
		), 'rendez_vous_editor_args' );

		self::$settings = array_merge( $set, array( 'rendez_vous_button_id' => '#' . $editor_id ) );
		return $set;
	}

	/**
	 * Display the button to launch the Editor
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public static function editor( $editor_id, $settings = array() ) {
		$set = self::set( $editor_id, $settings );

		$load_editor = apply_filters( 'rendez_vous_load_editor', bp_is_my_profile() );

		if ( current_user_can( 'publish_rendez_vouss' ) && ! empty( $load_editor ) ) {

			bp_button( array(
				'id'                => 'create-' . $set['component'] . '-' . $set['status'],
				'component'         => 'rendez_vous',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper_id'        => $editor_id,
				'wrapper_class'     => $set['btn_class'],
				'link_class'        => 'add-' .  $set['status'],
				'link_href'         => '#',
				'link_title'        => $set['btn_caption'],
				'link_text'         => $set['btn_caption']
			) );

		}

		self::launch( $editor_id );
	}

	/**
	 * Starts the editor
	 *
	 * @uses rendez_vous_enqueue_editor()
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public static function launch( $editor_id ) {
		$args = self::$settings;

		// time to enqueue script
		rendez_vous_enqueue_editor( $args );

	}
}

/**
 * Rendez_Vous "CRUD" Class.
 *
 * @since Rendez Vous (1.0.0)
 */
class Rendez_Vous_Item {
	public $id;
	public $organizer;
	public $title;
	public $venue;
	public $type;
	public $description;
	public $duration;
	public $privacy;
	public $status;
	public $days;
	public $attendees;
	public $report;
	public $older_date;
	public $def_date;
	public $modified;
	public $group_id;

	/**
	 * Constructor.
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	/**
	 * request an item id
	 *
	 * @uses get_post()
	 */
	public function populate() {
		$rendez_vous       = get_post( $this->id );

		if ( is_a( $rendez_vous, 'WP_Post' ) ) {
			$this->id          = $rendez_vous->ID;
			$this->organizer   = $rendez_vous->post_author;
			$this->title       = $rendez_vous->post_title;
			$this->venue       = get_post_meta( $rendez_vous->ID, '_rendez_vous_venue', true );
			$this->type        = rendez_vous_get_type( $rendez_vous->ID );
			$this->description = $rendez_vous->post_excerpt;
			$this->duration    = get_post_meta( $rendez_vous->ID, '_rendez_vous_duration', true );
			$this->privacy     = 'draft' == $rendez_vous->post_status ? get_post_meta( $rendez_vous->ID, '_rendez_vous_status', true ) : $rendez_vous->post_status;
			$this->status      = $rendez_vous->post_status;
			$this->days        = get_post_meta( $rendez_vous->ID, '_rendez_vous_days', true );
			$this->attendees   = get_post_meta( $this->id, '_rendez_vous_attendees' );
			$this->report      = $rendez_vous->post_content;
			$this->older_date  = false;

			if ( ! empty( $this->days ) ) {
				$timestamps = array_keys( $this->days );
				rsort( $timestamps );
				$this->older_date = date_i18n( 'Y-m-d H:i:s', $timestamps[0] );
			}

			$this->def_date    = get_post_meta( $rendez_vous->ID, '_rendez_vous_defdate', true );
			$this->modified    = $rendez_vous->post_modified;
			$this->group_id    = get_post_meta( $rendez_vous->ID, '_rendez_vous_group_id', true );
		}
	}

	/**
	 * Save a rendez-vous.
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public function save() {
		$this->id          = apply_filters_ref_array( 'rendez_vous_id_before_save',          array( $this->id,          &$this ) );
		$this->organizer   = apply_filters_ref_array( 'rendez_vous_organizer_before_save',   array( $this->organizer,   &$this ) );
		$this->title       = apply_filters_ref_array( 'rendez_vous_title_before_save',       array( $this->title,       &$this ) );
		$this->venue       = apply_filters_ref_array( 'rendez_vous_venue_before_save',       array( $this->venue,       &$this ) );
		$this->type        = apply_filters_ref_array( 'rendez_vous_type_before_save',        array( $this->type,        &$this ) );
		$this->description = apply_filters_ref_array( 'rendez_vous_description_before_save', array( $this->description, &$this ) );
		$this->duration    = apply_filters_ref_array( 'rendez_vous_duration_before_save',    array( $this->duration,    &$this ) );
		$this->privacy     = apply_filters_ref_array( 'rendez_vous_privacy_before_save',     array( $this->privacy,     &$this ) );
		$this->status      = apply_filters_ref_array( 'rendez_vous_status_before_save',      array( $this->status,      &$this ) );
		$this->days        = apply_filters_ref_array( 'rendez_vous_days_before_save',        array( $this->days,        &$this ) );
		$this->attendees   = apply_filters_ref_array( 'rendez_vous_attendees_before_save',   array( $this->attendees,   &$this ) );
		$this->report      = apply_filters_ref_array( 'rendez_vous_report_before_save',      array( $this->report,      &$this ) );
		$this->older_date  = apply_filters_ref_array( 'rendez_vous_older_date_before_save',  array( $this->older_date,  &$this ) );
		$this->def_date    = apply_filters_ref_array( 'rendez_vous_def_date_before_save',    array( $this->def_date,    &$this ) );
		$this->modified    = apply_filters_ref_array( 'rendez_vous_modified_before_save',    array( $this->modified,    &$this ) );
		$this->group_id    = apply_filters_ref_array( 'rendez_vous_group_id_before_save',    array( $this->group_id,    &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'rendez_vous_before_save', array( &$this ) );

		if ( empty( $this->organizer ) || empty( $this->title ) ) {
			return false;
		}

		if ( empty( $this->status ) ) {
			$this->status = 'publish';
		}

		// Update.
		if ( $this->id ) {

			$wp_update_post_args = array(
				'ID'		     => $this->id,
				'post_author'	 => $this->organizer,
				'post_title'	 => $this->title,
				'post_type'		 => 'rendez_vous',
				'post_excerpt'   => $this->description,
				'post_status'	 => ! empty( $this->privacy ) ? 'private' : $this->status
			);

			// The report is saved once the rendez vous date is past
			if ( ! empty( $this->report ) ) {
				$wp_update_post_args['post_content'] = $this->report;
			}

			// reset privacy to get rid of the meta now the post has been published
			$this->privacy  = '';
			$this->group_id = get_post_meta( $this->id, '_rendez_vous_group_id', true );

			$result = wp_update_post( $wp_update_post_args );

		// Insert.
		} else {

			$wp_insert_post_args = array(
				'post_author'	 => $this->organizer,
				'post_title'	 => $this->title,
				'post_type'		 => 'rendez_vous',
				'post_excerpt'   => $this->description,
				'post_status'	 => 'draft'
			);

			$result = wp_insert_post( $wp_insert_post_args );

			// We only need to do that once
			if( $result ) {
				if ( ! empty( $this->days ) && is_array( $this->days ) ) {
					update_post_meta( $result, '_rendez_vous_days', $this->days );
				}

				// Group
				if ( ! empty( $this->group_id ) ) {
					update_post_meta( $result, '_rendez_vous_group_id', $this->group_id );
				}
			}
		}

		// Saving metas !
		if ( ! empty( $result ) ) {

			if( ! empty( $this->venue ) ) {
				update_post_meta( $result, '_rendez_vous_venue', $this->venue );
			} else {
				delete_post_meta( $result, '_rendez_vous_venue' );
			}

			if( ! empty( $this->duration ) ) {
				update_post_meta( $result, '_rendez_vous_duration', $this->duration );
			} else {
				delete_post_meta( $result, '_rendez_vous_duration' );
			}

			if( ! empty( $this->privacy ) ) {
				update_post_meta( $result, '_rendez_vous_status', $this->privacy );
			} else {
				delete_post_meta( $result, '_rendez_vous_status' );
			}

			if( ! empty( $this->def_date ) ) {
				update_post_meta( $result, '_rendez_vous_defdate', $this->def_date );
			} else {
				delete_post_meta( $result, '_rendez_vous_defdate' );
			}

			if( ! empty( $this->attendees ) && is_array( $this->attendees ) ) {
				$this->attendees = array_map( 'absint', $this->attendees );

				$in_db = get_post_meta( $result, '_rendez_vous_attendees' );

				if ( empty( $in_db ) ) {

					foreach( $this->attendees as $attendee ) {
						add_post_meta( $result, '_rendez_vous_attendees', absint( $attendee ) );
					}

				} else {
					$to_delete = array_diff( $in_db, $this->attendees );
					$to_add    = array_diff( $this->attendees, $in_db );

					if ( ! empty( $to_delete ) ){
						// Delete item ids
						foreach ( $to_delete as $del_attendee ) {
							delete_post_meta( $result, '_rendez_vous_attendees', absint( $del_attendee ) );
							// delete user's preferences
							self::attendees_pref( $result, $del_attendee );
						}
					}

					if ( ! empty( $to_add ) ){
						// Add item ids
						foreach ( $to_add as $add_attendee ) {
							add_post_meta( $result, '_rendez_vous_attendees', absint( $add_attendee ) );
						}
					}
				}

			} else {
				delete_post_meta( $result, '_rendez_vous_attendees' );
			}

			// Set rendez-vous type
			rendez_vous_set_type( $result, $this->type );

			do_action_ref_array( 'rendez_vous_after_meta_update', array( &$this ) );

		}

		do_action_ref_array( 'rendez_vous_after_save', array( &$this ) );

		return $result;
	}

	/**
	 * Set an attendee's preferences.
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public static function attendees_pref( $id = 0, $user_id = 0, $prefs = array() ) {
		if ( empty( $id ) || empty( $user_id ) ) {
			return false;
		}

		$days      = get_post_meta( $id, '_rendez_vous_days', true );
		$attendees = get_post_meta( $id, '_rendez_vous_attendees' );

		if ( empty( $days ) || ! is_array( $days ) ) {
			return false;
		}

		$check_days = array_keys( $days );

		foreach ( $check_days as $day ) {
			// User has not set or didn't chose this day so far
			if ( ! in_array( $user_id, $days[ $day ] ) ) {
				if ( in_array( $day, $prefs ) )
					$days[ $day ] = array_merge( $days[ $day ], array( $user_id ) );
			// User choosed this day, remove it if not in prefs
			} else {
				if ( ! in_array( $day, $prefs ) )
					$days[ $day ] = array_diff( $days[ $day ], array( $user_id ) );
			}
		}

		update_post_meta( $id, '_rendez_vous_days', $days );

		// We have a guest! Should only happen for public rendez-vous
		if ( ! in_array( $user_id, $attendees ) && ! empty( $prefs ) ) {
			add_post_meta( $id, '_rendez_vous_attendees', absint( $user_id ) );
		}

		return true;
	}

	/**
	 * The selection query
	 *
	 * @since Rendez Vous (1.0.0)
	 * @param array $args arguments to customize the query
	 * @uses bp_parse_args
	 */
	public static function get( $args = array() ) {

		$defaults = array(
			'attendees' => array(), // one or more user ids who may attend to the rendez vous
			'organizer' => false,   // the author id of the rendez vous
			'per_page'  => 20,
			'page'      => 1,
			'search'    => false,
			'exclude'   => false,   // comma separated list or array of rendez vous ids.
			'orderby'   => 'modified',
			'order'     => 'DESC',
			'group_id'  => false,
		);

		$r = bp_parse_args( $args, $defaults, 'rendez_vous_get_query_args' );

		$rendez_vous_status = array( 'publish', 'private' );

		$draft_status = apply_filters( 'rendez_vous_get_query_draft_status', bp_is_my_profile() );

		if ( $draft_status || bp_current_user_can( 'bp_moderate' ) ) {
			$rendez_vous_status[] = 'draft';
		}

		$query_args = array(
			'post_status'	 => $rendez_vous_status,
			'post_type'	     => 'rendez_vous',
			'posts_per_page' => $r['per_page'],
			'paged'		     => $r['page'],
			'orderby' 		 => $r['orderby'],
			'order'          => $r['order'],
		);

		if ( ! empty( $r['organizer'] ) ) {
			$query_args['author'] = $r['organizer'];
		}

		if ( ! empty( $r['exclude'] ) ) {
			$exclude = $r['exclude'];

			if ( ! is_array( $exclude ) ) {
				$exclude = explode( ',', $exclude );
			}

			$query_args['post__not_in'] = $exclude;
		}

		// component is defined, we can zoom on specific ids
		if ( ! empty( $r['attendees'] ) ) {
			// We really want an array!
			$attendees = (array) $r['attendees'];

			$query_args['meta_query'] = array(
				array(
					'key'     => '_rendez_vous_attendees',
					'value'   => $attendees,
					'compare' => 'IN',
				)
			);
		}

		if ( ! empty( $r['group_id'] ) ) {
			$group_query = array(
				'key'     => '_rendez_vous_group_id',
				'value'   => $r['group_id'],
				'compare' => '=',
			);

			if ( empty( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array( $group_query );
			} else {
				$query_args['meta_query'][] = $group_query;
			}
		}

		if ( ! empty( $r['type'] ) ) {
			$query_args['tax_query'] = array( array(
				'field'    => 'slug',
				'taxonomy' => 'rendez_vous_type',
				'terms'    => $r['type'],
			) );
		}

		$rendez_vous_items = new WP_Query( $query_args );

		return array( 'rendez_vous_items' => $rendez_vous_items->posts, 'total' => $rendez_vous_items->found_posts );
	}

	/**
	 * Delete a rendez-vous
	 *
	 * @since Rendez Vous (1.0.0)
	 * @uses wp_delete_post()
	 */
	public static function delete( $rendez_vous_id = 0 ) {
		if ( empty( $rendez_vous_id ) )
			return false;

		$deleted = wp_delete_post( $rendez_vous_id, true );

		return $deleted;
	}
}
