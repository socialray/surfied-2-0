<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

Class BP_Checkins_Place {
	var $id;
	var $group_id;
	var $hide_sitewide;
	var $user_id;
	var $title;
	var $content;
	var $place_category;
	var $address;
	var $lat;
	var $lng;
	var $type;
	var $start;
	var $end;
	var $query;


	function bp_checkins_place( $id = false ) {
		$this->__construct( $id );
	}

	function __construct( $id = false ) {
		global $bp;

		if ( !empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	function populate() {
		global $wpdb, $bp;
		
		$query_args = array(
			'post_status'	 => 'publish',
			'post_type'	 => 'places',
			'p' => intval( $this->id )
		);
		
		$this->query = new WP_Query( $query_args );

	}

	function save() {
		global $current_user;

		$this->id              = apply_filters_ref_array( 'bp_checkins_places_id_before_save',             array( $this->id,        &$this ) );
		$this->group_id        = apply_filters_ref_array( 'bp_checkins_places_group_id_before_save',       array( $this->group_id,  &$this ) );
		$this->hide_sitewide   = apply_filters_ref_array( 'bp_checkins_places_hide_sitewide_before_save', array( $this->hide_sitewide,  &$this ) );
		$this->title           = apply_filters_ref_array( 'bp_checkins_places_title_before_save',          array( $this->title,     &$this ) );
		$this->content         = apply_filters_ref_array( 'bp_checkins_places_content_before_save',        array( $this->content,   &$this ) );
		$this->user_id         = apply_filters_ref_array( 'bp_checkins_places_user_id_before_save',        array( $this->user_id,   &$this ) );
		$this->address         = apply_filters_ref_array( 'bp_checkins_places_address_before_save',        array( $this->address,   &$this ) );
		$this->lat             = apply_filters_ref_array( 'bp_checkins_places_lat_before_save',            array( $this->lat,       &$this ) );
		$this->lng             = apply_filters_ref_array( 'bp_checkins_places_lng_before_save',            array( $this->lng,       &$this ) );
		$this->place_category  = apply_filters_ref_array( 'bp_checkins_places_place_category_before_save', array( $this->place_category, &$this ) );
		$this->type            = apply_filters_ref_array( 'bp_checkins_places_type_before_save',           array( $this->type,       &$this ) );
		$this->start           = apply_filters_ref_array( 'bp_checkins_places_start_before_save',          array( $this->start,       &$this ) );
		$this->end             = apply_filters_ref_array( 'bp_checkins_places_end_before_save',            array( $this->end,       &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'bp_checkins_places_before_save', array( &$this ) );

		if ( !$this->title || !$this->content )
			return false;

		// If we have an existing ID, update the post, otherwise insert it.
		if ( $this->id ) {
			
			$wp_update_post_args = array(
				'ID'		    => $this->id,
				'post_author'	=> $this->user_id,
				'post_title'	=> $this->title,
				'post_content'	=> $this->content,
				'post_type'		=> 'places'
			);
			
			$result = wp_update_post( $wp_update_post_args );

			if ( $result ) {
				
				if( !empty( $this->group_id ) ) {
					update_post_meta( $result, '_bpci_group_id', $this->group_id );
					
					do_action_ref_array( 'bp_checkins_places_after_group_postmeta_update', array( &$this ) );
				} else {
					update_post_meta( $this->id, '_bpci_group_id', "0" );
				}
				
				if( !empty( $this->hide_sitewide ) ) {
					update_post_meta( $this->id, '_bpci_place_hide_sitewide', $this->hide_sitewide );

					do_action_ref_array( 'bp_checkins_places_after_privacy_postmeta_update', array( &$this ) );
				} else {
					update_post_meta( $this->hide_sitewide, '_bpci_place_hide_sitewide', "0" );
				}
				
				if( !empty( $this->address ) ) {
					update_post_meta( $result, 'bpci_places_address', $this->address );
					
					do_action_ref_array( 'bp_checkins_places_after_address_postmeta_update', array( &$this ) );
				}
				
				if( !empty( $this->lat ) ) {
					update_post_meta( $result, 'bpci_places_lat', $this->lat );
					
					do_action_ref_array( 'bp_checkins_places_after_lat_postmeta_update', array( &$this ) );
				}
				
				if( !empty( $this->lng ) ) {
					update_post_meta( $result, 'bpci_places_lng', $this->lng );
					
					do_action_ref_array( 'bp_checkins_places_after_lng_postmeta_update', array( &$this ) );
				}
				
				if( !empty( $this->type ) && $this->type == 'live' ) {
					update_post_meta( $result, 'bpci_places_is_live', $this->type );
					
					do_action_ref_array( 'bp_checkins_places_after_type_postmeta_update', array( &$this ) );
					
					if( !empty( $this->start ) ) {
						update_post_meta( $result, 'bpci_places_live_start', $this->start );

						do_action_ref_array( 'bp_checkins_places_after_live_start_postmeta_update', array( &$this ) );
					}
					if( !empty( $this->end ) ) {
						update_post_meta( $result, 'bpci_places_live_end', $this->end );

						do_action_ref_array( 'bp_checkins_places_after_live_end_postmeta_update', array( &$this ) );
					}
				}
					
				if( !empty( $this->place_category ) )
					wp_set_post_terms( $this->id, array( $this->place_category ), 'places_category', false);

					
				do_action_ref_array( 'bp_checkins_places_after_update_postmeta', array( &$this ) );
				
			}	
			
		} else {
			
			$wp_insert_post_args = array(
				'post_author'	=> $this->user_id,
				'post_title'	=> $this->title,
				'post_content'	=> $this->content,
				'post_type'		=> 'places',
				'post_status'	=> 'publish'
			);
			
			$result = wp_insert_post( $wp_insert_post_args );
			
			if( $result ) {
				
				$this->id = $result;
				
				if( !empty( $this->group_id ) ) {
					update_post_meta( $this->id, '_bpci_group_id', $this->group_id );

					do_action_ref_array( 'bp_checkins_places_after_group_postmeta_insert', array( &$this ) );
				} else {
					update_post_meta( $this->id, '_bpci_group_id', "0" );
				}
				
				if( !empty( $this->hide_sitewide ) ) {
					update_post_meta( $this->id, '_bpci_place_hide_sitewide', $this->hide_sitewide );

					do_action_ref_array( 'bp_checkins_places_after_privacy_postmeta_insert', array( &$this ) );
				} else {
					update_post_meta( $this->id, '_bpci_place_hide_sitewide', "0" );
				}

				
				if( !empty( $this->address ) ) {
					update_post_meta( $result, 'bpci_places_address', $this->address );
					
					do_action_ref_array( 'bp_checkins_places_after_address_postmeta_insert', array( &$this ) );
				}
				
				if( !empty( $this->lat ) ) {
					update_post_meta( $result, 'bpci_places_lat', $this->lat );
					
					do_action_ref_array( 'bp_checkins_places_after_lat_postmeta_insert', array( &$this ) );
				}
				
				if( !empty( $this->lng ) ) {
					update_post_meta( $result, 'bpci_places_lng', $this->lng );
					
					do_action_ref_array( 'bp_checkins_places_after_lng_postmeta_insert', array( &$this ) );
				}
				
				if( !empty( $this->type ) && $this->type == 'live' ) {
					update_post_meta( $result, 'bpci_places_is_live', $this->type );
					
					do_action_ref_array( 'bp_checkins_places_after_type_postmeta_insert', array( &$this ) );
					
					if( !empty( $this->start ) ) {
						update_post_meta( $result, 'bpci_places_live_start', $this->start );

						do_action_ref_array( 'bp_checkins_places_after_live_start_postmeta_insert', array( &$this ) );
					}
					if( !empty( $this->end ) ) {
						update_post_meta( $result, 'bpci_places_live_end', $this->end );

						do_action_ref_array( 'bp_checkins_places_after_live_end_postmeta_insert', array( &$this ) );
					}
				}
				
				if( !empty( $this->place_category ) )
					wp_set_post_terms( $this->id, array( $this->place_category ), 'places_category', false);
					
				
				do_action_ref_array( 'bp_checkins_places_after_insert_postmeta', array( &$this ) );
				
			}
			
			
		}

		do_action_ref_array( 'bp_checkins_places_after_save', array( &$this ) );

		return $result;
	}
	
	// Static Functions
	function get( $args ) {
		global $wpdb, $bp;
		
		// Only run the query once
		if ( empty( $this->query ) ) {
			$defaults = array(
				'id'              =>false,
				'p'               => false,
				'group_id'	      => false,
				'user_id'	      => false,
				'per_page'	      => 10,
				'paged'		      => 1,
				'type'            => false,
				'places_category' => false,
				'search'          => false
			);

			$r = wp_parse_args( $args, $defaults );
			extract( $r );
			
			$paged = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : $paged;
			
			if( !empty($p) ) {
				
				$query_args = array(
					'post_status'	 => 'publish',
					'post_type'	 => 'places',
					'name' => $p
				);
				
			} else {
				
				$query_args = array(
					'post_status'	 => 'publish',
					'post_type'	 => 'places',
					'posts_per_page' => $per_page,
					'paged'		 => $paged,
					'meta_query'	 => array()
				);
				
				if( !empty($places_category) ) {
					$query_args['tax_query'] = array(
													array(
														'taxonomy' => 'places_category',
														'field' => 'slug',
														'terms' => $places_category
														)
												);
				}
				
				if( !empty( $search ) ) {
					$query_args['s'] = $search;
				}

				if( !empty( $type ) ) {

					if( $type == 'all_live_places') {
						$query_args['meta_key'] = 'bpci_places_is_live';
						$query_args['meta_value'] = 'live';
					}

					if( $type == 'upcoming_places') {
						$query_args['meta_key'] = 'bpci_places_live_start';
						$query_args['meta_value'] = date('Y-m-d H:i:s');
						$query_args['meta_compare'] = '>=';
					}

					if( $type == 'places_around' ){
						
						$position = array();
						
						if( !empty( $_COOKIE['bp-ci-data'] ) )
							$position = explode('|', $_COOKIE['bp-ci-data']);

						if( count($position) > 1 ) {
							$lat = (float) $position[0];
							$lng = (float) $position[1];
						} else {
							$lat = (float) bp_get_user_meta( $bp->loggedin_user->id, 'bpci_latest_lat', true);
							$lng = (float) bp_get_user_meta( $bp->loggedin_user->id, 'bpci_latest_lat', true);
						}
						
						if( !empty( $lat ) ) {
							$query_args['meta_query'][] = array(
								'key'	  => 'bpci_places_lat',
								'value'	  => array( $lat - 0.4, $lat + 0.4),
								'type'    => 'DECIMAL',
								'compare' => 'BETWEEN' 
							);

							$query_args['meta_query'][] = array(
								'key'	  => 'bpci_places_lng',
								'value'	  => array( $lng - 0.4, $lng + 0.4),
								'type'    => 'DECIMAL',
								'compare' => 'BETWEEN' 
							);
						}

					}

				}

				if ( !empty( $user_id ) ) {
					$query_args['author'] = $user_id;
				}

				if( !empty( $group_id ) ) {
					$query_args['meta_query'][] = array(
						'key'	  => '_bpci_group_id',
						'value'	  => $group_id,
						'compare' => 'IN' // Allows $group_id to be an array
					);
				} else {
					if( ( empty($p) && empty( $user_id ) ) || ( empty($p) && $user_id != $bp->loggedin_user->id ) ) {
						$query_args['meta_query'][] = array(
							'key'	  => '_bpci_place_hide_sitewide',
							'value'	  => 1,
							'compare' => '!=' // hide the 'private - hidden' group places
						);
					}

				}
				
			}

			
			// Run the query, and store as an object property, so we can access from
			// other methods
			$this->query = new WP_Query( $query_args );
			
			if( !empty($p) ) {
				$this->query->is_singular = true;
				
				if( false === wp_cache_add( 'single_query', $this, 'bp_checkins_single' ) )
					wp_cache_set( 'single_query', $this, 'bp_checkins_single' );
			}
				
				


			// Let's also set up some pagination
			$this->pag_links = paginate_links( array(
				'base' => add_query_arg( 'items_page', '%#%' ),
				'format' => '',
				'total' => ceil( (int) $this->query->found_posts / (int) $this->query->query_vars['posts_per_page'] ),
				'current' => (int) $paged,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'mid_size' => 1
			) );
		}
		
	}
	
	function have_posts() {
		return $this->query->have_posts();
	}

	/**
	 * Part of our bp_example_has_high_fives() loop
	 *
	 * @package BuddyPress_Skeleton_Component
	 * @since 1.6
	 */
	function the_post() {
		return $this->query->the_post();
	}

	function delete( $args ) {
		global $wpdb, $bp;

		$defaults = array(
			'user_id'       => false,
			'group_id'      => false,
			'hide_sitewide' => 0
		);
		
		$params = wp_parse_args( $args, $defaults );
		extract( $params );

		if( !empty( $user_id ) ) {
			// we're deleting places added by the user
			$places_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_author = %d AND post_type = 'places'", $user_id ) );
			
			if( count( $places_id ) < 1 )
				return false;
				
			foreach( $places_id as $place ){
				wp_delete_post( $place, true );
			}
		}
		
		if( !empty( $group_id ) ) {
			// we're doing things to place attached to this group
			
			$places_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}postmeta LEFT JOIN {$wpdb->base_prefix}posts ON({$wpdb->base_prefix}postmeta.post_id = {$wpdb->base_prefix}posts.ID) WHERE post_type = 'places' AND meta_key='_bpci_group_id' AND meta_value = %d AND post_status = 'publish' ", $group_id ) );
			
			if( count( $places_id ) < 1 )
				return false;
			
			if( $hide_sitewide != 1 ){
				//if the group is public, simply remove the group meta for the places attached to the group
				
				foreach( $places_id as $place ){
					update_post_meta( $place, '_bpci_group_id', "0" );
				}
				
			} else {
				foreach( $places_id as $place ){
					wp_delete_post( $place, true );
				}
			}

		}

		return true;
	}
	
	function group_update_visibility( $group_id, $status ) {
		global $wpdb;
		
		$hide_sitewide = 0;
		
		if( empty( $group_id ) || empty( $status ) )
			return false;
			
		$places_id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->base_prefix}postmeta LEFT JOIN {$wpdb->base_prefix}posts ON({$wpdb->base_prefix}postmeta.post_id = {$wpdb->base_prefix}posts.ID) WHERE post_type = 'places' AND meta_key='_bpci_group_id' AND meta_value = %d AND post_status = 'publish' ", $group_id ) );
		
		if( count( $places_id ) < 1 )
			return false;
		
		if ( 'public' != $status )
			$hide_sitewide = 1;
			
		$places = implode( ',', $places_id );
			
		return $wpdb->get_var( $wpdb->prepare( "UPDATE {$wpdb->base_prefix}postmeta SET meta_value = %d WHERE meta_key = '_bpci_place_hide_sitewide' AND post_id IN({$places})", $hide_sitewide ) );
		
	}

}

?>