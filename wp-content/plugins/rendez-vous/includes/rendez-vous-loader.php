<?php
/**
 * Rendez Vous Loader.
 *
 * Loads the component
 *
 * @package Rendez Vous
 * @subpackage Component
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rendez_Vous_Component class
 *
 * @package Rendez_Vous
 * @subpackage Component
 *
 * @since Rendez Vous (1.0.0)
 */
class Rendez_Vous_Component extends BP_Component {

	/**
	 * Constructor method
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function __construct() {
		$bp = buddypress();

		parent::start(
			'rendez_vous',
			rendez_vous()->get_component_name(),
			rendez_vous()->includes_dir
		);

		$this->includes();

		$bp->active_components[$this->id] = '1';

		/**
		 * Only register the post type on the blog where BuddyPress is activated.
		 */
		if ( get_current_blog_id() == bp_get_root_blog_id() ) {
			add_action( 'init', array( &$this, 'register_post_types' ) );
		}
	}

	/**
	 * Include files
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'rendez-vous-filters.php',
			'rendez-vous-screens.php',
			'rendez-vous-editor.php',
			'rendez-vous-classes.php',
			'rendez-vous-ajax.php',
			'rendez-vous-parts.php',
			'rendez-vous-template.php',
			'rendez-vous-functions.php',
		);

		if ( bp_is_active( 'notifications' ) ) {
			$includes[] = 'rendez-vous-notifications.php';
		}

		if ( bp_is_active( 'activity' ) ) {
			$includes[] = 'rendez-vous-activity.php';
		}

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'rendez-vous-groups.php';
		}

		if ( is_admin() ) {
			$includes[] = 'rendez-vous-admin.php';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up globals
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function setup_globals( $args = array() ) {

		// Set up the $globals array to be passed along to parent::setup_globals()
		$args = array(
			'slug'                  => rendez_vous()->get_component_slug(),
			'notification_callback' => 'rendez_vous_format_notifications',
			'search_string'         => __( 'Search Rendez-vous...', 'rendez-vous' ),
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $args );

		/**
		 * Filter to change user's default subnav
		 *
		 * @since Rendez Vous (1.1.0)
		 *
		 * @param string default subnav to use (shedule or attend)
		 */
		$this->default_subnav = apply_filters( 'rendez_vous_member_default_subnav', rendez_vous()->get_schedule_slug() );

		$this->subnav_position = array(
			'schedule' => 10,
			'attend'   => 20,
		);

		if ( rendez_vous()->get_attend_slug() == $this->default_subnav ) {
			$this->subnav_position['attend'] = 5;
		}
	}

	/**
	 * Set up navigation.
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		// Add 'Rendez-vous' to the main navigation
		$main_nav = array(
			'name' 		          => rendez_vous()->get_component_name(),
			'slug' 		          => $this->slug,
			'position' 	          => 80,
			'screen_function'     => array( 'Rendez_Vous_Screens', 'public_screen' ),
			'default_subnav_slug' => $this->default_subnav
		);

		// Stop if there is no user displayed or logged in
		if ( ! is_user_logged_in() && ! bp_displayed_user_id() )
			return;

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$rendez_vous_link = trailingslashit( $user_domain . $this->slug );

		// Add a subnav item under the main Rendez-vous tab
		$sub_nav[] = array(
			'name'            =>  __( 'Schedule', 'rendez-vous' ),
			'slug'            => rendez_vous()->get_schedule_slug(),
			'parent_url'      => $rendez_vous_link,
			'parent_slug'     => $this->slug,
			'screen_function' => array( 'Rendez_Vous_Screens', 'schedule_screen' ),
			'position'        => $this->subnav_position['schedule']
		);

		// Add a subnav item under the main Rendez-vous tab
		$sub_nav[] = array(
			'name'            =>  __( 'Attend', 'rendez-vous' ),
			'slug'            => rendez_vous()->get_attend_slug(),
			'parent_url'      => $rendez_vous_link,
			'parent_slug'     => $this->slug,
			'screen_function' => array( 'Rendez_Vous_Screens', 'attend_screen' ),
			'position'        => $this->subnav_position['attend']
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain      = bp_loggedin_user_domain();
			$rendez_vous_link = trailingslashit( $user_domain . $this->slug );

			// Add the "Example" sub menu
			$wp_admin_nav[0] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Rendez-vous', 'rendez-vous' ),
				'href'   => trailingslashit( $rendez_vous_link )
			);

			// Personal
			$wp_admin_nav[ $this->subnav_position['schedule'] ] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-schedule',
				'title'  => __( 'Schedule', 'rendez-vous' ),
				'href'   => trailingslashit( $rendez_vous_link . rendez_vous()->get_schedule_slug() )
			);

			// Screen two
			$wp_admin_nav[ $this->subnav_position['attend'] ] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-attend',
				'title'  => __( 'Attend', 'rendez-vous' ),
				'href'   => trailingslashit( $rendez_vous_link . rendez_vous()->get_attend_slug() )
			);

			// Sort WP Admin Nav
			ksort( $wp_admin_nav );
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Register the rendez_vous post type
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.0.0)
	 */
	function register_post_types() {
		// Set up some labels for the post type
		$rdv_labels = array(
			'name'	             => __( 'Rendez-vous',                                                     'rendez-vous' ),
			'singular'           => _x( 'Rendez-vous',                   'rendez-vous singular',           'rendez-vous' ),
			'menu_name'          => _x( 'Rendez-vous',                   'rendez-vous menu name',          'rendez-vous' ),
			'all_items'          => _x( 'All Rendez-vous',               'rendez-vous all items',          'rendez-vous' ),
			'singular_name'      => _x( 'Rendez-vous',                   'rendez-vous singular name',      'rendez-vous' ),
			'add_new'            => _x( 'Add New Rendez-vous',           'rendez-vous add new',            'rendez-vous' ),
			'edit_item'          => _x( 'Edit Rendez-vous',              'rendez-vous edit item',          'rendez-vous' ),
			'new_item'           => _x( 'New Rendez-vous',               'rendez-vous new item',           'rendez-vous' ),
			'view_item'          => _x( 'View Rendez-vous',              'rendez-vous view item',          'rendez-vous' ),
			'search_items'       => _x( 'Search Rendez-vous',            'rendez-vous search items',       'rendez-vous' ),
			'not_found'          => _x( 'No Rendez-vous Found',          'rendez-vous not found',          'rendez-vous' ),
			'not_found_in_trash' => _x( 'No Rendez-vous Found in Trash', 'rendez-vous not found in trash', 'rendez-vous' )
		);

		$rdv_args = array(
			'label'	            => _x( 'Rendez-vous',                    'rendez-vous label',              'rendez-vous' ),
			'labels'            => $rdv_labels,
			'public'            => false,
			'rewrite'           => false,
			'show_ui'           => false,
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'capabilities'      => rendez_vous_get_caps(),
			'capability_type'   => array( 'rendez_vous', 'rendez_vouss' ),
			'delete_with_user'  => true,
			'supports'          => array( 'title', 'author' )
		);

		// Register the post type for attachements.
		register_post_type( 'rendez_vous', $rdv_args );

		parent::register_post_types();
	}

	/**
	 * Register the rendez_vous types taxonomy
	 *
	 * @package Rendez_Vous
	 * @subpackage Component
	 *
	 * @since Rendez Vous (1.2.0)
	 */
	public function register_taxonomies() {
		// Register the taxonomy
		register_taxonomy( 'rendez_vous_type', 'rendez_vous', array(
			'public' => false,
		) );
	}
}

/**
 * Loads rendez vous component into the $bp global
 *
 * @package Rendez_Vous
 * @subpackage Component
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_load_component() {
	$bp = buddypress();

	$bp->rendez_vous = new Rendez_Vous_Component;
}
