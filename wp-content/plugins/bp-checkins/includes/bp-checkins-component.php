<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class BP_Checkins_Component extends BP_Component {

	/**
	 * Constructor method
	 *
	 *
	 * @package BP Checkins
	 * @since 1.0
	 */
	function __construct() {
		global $bp, $blog_id;

		parent::start(
			BP_CHECKINS_SLUG,
			__( 'Checkins', 'bp-checkins' ),
			BP_CHECKINS_PLUGIN_DIR
		);

	 	$this->includes();
		
		$bp->active_components[$this->id] = '1';

		/**
		 * Register the places custom post type
		 * If the priority is 10 or more, then taxo is not set when using ajax.
		 */
		if( $blog_id == BP_ROOT_BLOG ) {
			add_action( 'init', array( &$this, 'register_post_types' ), 9 );
			add_action( 'init', array( &$this, 'register_taxonomies' ), 9 );
		}	
	}

	/**
	 * BP Checkins needed files
	 *
	 * @package BP Checkins
	 * @since 1.0
	 */
	function includes() {

		// Files to include
		$includes = array(
			'includes/bp-checkins-actions.php',
			'includes/bp-checkins-screens.php',
			'includes/bp-checkins-functions.php',
			'includes/bp-checkins-filters.php',
			'includes/bp-checkins-places-class.php',
			'includes/bp-checkins-template.php',
			'includes/bp-checkins-ajax.php',
			'includes/bp-checkins-places-widget.php'
		);
		
		if( bp_is_active( 'groups' ) )
			$includes[] = 'includes/bp-checkins-group-class.php';
		
		if( bp_checkins_is_foursquare_ready() && bp_is_active( 'settings' ) )
			$includes[] = 'includes/bp-checkins-foursquare-api.php';
		

		parent::includes( $includes );

	}

	/**
	 * Set up BP Checkins globals
	 *
	 * @package BP Checkins
	 * @since 1.0
	 *
	 * @global obj $bp BuddyPress's global object
	 */
	function setup_globals() {
		global $bp;

		// Defining the slug in this way makes it possible for site admins to override it
		if ( !defined( 'BP_CHECKINS_SLUG' ) )
			define( 'BP_CHECKINS_SLUG', $this->id );

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => BP_CHECKINS_SLUG,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : BP_CHECKINS_SLUG,
			'has_directory'         => true, // Set to false if not required
			'notification_callback' => 'bp_checkins_format_notifications',
			'search_string'         => __( 'Search Places...', 'bp-checkins' )
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );

		// we'll use this to avoid conflict with activity's $bp->ajax_querystring
		$bp->{$this->id}->ajax_query = '';
		
	}

	/**
	 * Set up bp-checkins navigation.
	 */
	function setup_nav() {
		global $bp;
		// Add 'Checkins' to the main navigation
		$main_nav = array(
			'name' 		      => __( 'Checkins', 'bp-checkins' ),
			'slug' 		      => BP_CHECKINS_SLUG,
			'position' 	      => 80,
			'screen_function'     => 'bp_checkins_my_checkins',
			'default_subnav_slug' => 'checkins-area'
		);
		
		$user_domain = ( !empty( $bp->displayed_user->id ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

		$checkins_link = trailingslashit( $user_domain . BP_CHECKINS_SLUG );

		// Add a few subnav items under the main Example tab
		$sub_nav[] = array(
			'name'            =>  __( 'Checkins', 'bp-checkins' ),
			'slug'            => 'checkins-area',
			'parent_url'      => $checkins_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_checkins_my_checkins',
			'position'        => 10,
		);

		// Add the subnav items to the friends nav item
		
		$sub_nav[] = array(
			'name'            =>  __( 'Places', 'bp-checkins' ),
			'slug'            => 'places-area',
			'parent_url'      => $checkins_link,
			'parent_slug'     =>  $this->slug,
			'screen_function' => 'bp_checkins_my_places',
			'position'        => 20
		);	

		parent::setup_nav( $main_nav, $sub_nav );
		
		if( bp_checkins_is_foursquare_ready() && bp_is_active( 'settings' ) ){
			// Member Settings tab
			bp_core_new_subnav_item( array(
				'name' 		  => __( 'Checkins settings', 'bp-checkins' ),
				'slug' 		  => 'checkins-settings',
				'parent_slug'     => bp_get_settings_slug(),
				'parent_url' 	  => trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() ),
				'screen_function' => 'bp_checkins_screen_settings_menu',
				'position' 	  => 40,
				'user_has_access' => bp_is_my_profile() // Only the logged in user can access this on his/her profile
			) );
		}
		
	}
	
	function setup_admin_bar() {
		global $bp;

		// Prevent debug notices
		$wp_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$checkins_link = trailingslashit( bp_loggedin_user_domain() . BP_CHECKINS_SLUG );

			// Add main bp checkins menu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-' . BP_CHECKINS_SLUG,
				'title'  => __( 'Checkins', 'bp-checkins' ),
				'href'   => trailingslashit( $checkins_link )
			);
			
			// Add main bp checkins my places submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . BP_CHECKINS_SLUG,
				'id'     => 'my-account-' . BP_CHECKINS_SLUG .'-checkins',
				'title'  => __( 'My Checkins', 'bp-checkins' ),
				'href'   => trailingslashit( $checkins_link )
			);
			
			// Add main bp checkins my places submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . BP_CHECKINS_SLUG,
				'id'     => 'my-account-' . BP_CHECKINS_SLUG .'-places',
				'title'  => __( 'My Places', 'bp-checkins' ),
				'href'   => trailingslashit( $checkins_link . 'places-area' )
			);

			if( bp_checkins_is_foursquare_ready() && bp_is_active( 'settings' ) ) {
				
				if( !function_exists( 'bp_get_settings_slug' ) )
					return;
				
				// Add bp checkins settings submenu
				$wp_admin_nav[] = array(
					'parent' => 'my-account-settings-default',
					'id'     => 'my-account-settings-' . BP_CHECKINS_SLUG .'-foursquare',
					'title'  => __( 'Checkins', 'bp-checkins' ),
					'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() . '/checkins-settings' )
				);
			}

		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * registering post type
	 */
	function register_post_types() {
		global $bp, $wpdb;
		
		if( empty( $bp->pages->{$this->id}->slug ) ) {
			
			$directory_ids = bp_core_get_directory_page_ids();
			$page_id = $directory_ids[$this->id];
			
			$page_slug = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->base_prefix}posts WHERE ID = %d AND post_status = 'publish' ", $page_id ) );
			
		} else {
			$page_slug = $bp->pages->{$this->id}->slug;
		}
		
		$slug = isset( $page_slug ) ? $page_slug : BP_CHECKINS_SLUG;
		
		// Set up some labels for the post type
		$labels = array(
			'name'	             => __( 'Places', 'bp-checkins' ),
			'singular'           => __( 'Place', 'bp-checkins' ),
			'menu_name'          => __( 'Community Places', 'bp-checkins' ),
			'all_items'          => __( 'All Places', 'bp-checkins' ),
			'singular_name'      => __( 'Place', 'bp-checkins' ),
			'add_new'            => __( 'Add New Place', 'bp-checkins' ),
			'add_new_item'       => __( 'Add New Place', 'bp-checkins' ),
			'edit_item'          => __( 'Edit Place', 'bp-checkins' ),
			'new_item'           => __( 'New Place', 'bp-checkins' ),
			'view_item'          => __( 'View Place', 'bp-checkins' ),
			'search_items'       => __( 'Search Places', 'bp-checkins' ),
			'not_found'          => __( 'No Places Found', 'bp-checkins' ),
			'not_found_in_trash' => __( 'No Places Found in Trash', 'bp-checkins' )
		);
		
		$args = array(
			'label'	     => __( 'Place', 'bp-checkins' ),
			'labels'     => $labels,
			'public'     => false,
			'rewrite'=>array(
				'slug'=> $slug . '/place',
				'with_front'=>false),
			'show_ui'    => true,
			'supports'   => array( 'title', 'editor', 'author', 'excerpt', 'comments', 'custom-fields' ),
			'menu_icon'  => BP_CHECKINS_PLUGIN_URL_IMG . '/community-places-post-type-icon.png',
			'taxonomies' => array( 'places_category')
		);

		// Register the post type.
		register_post_type( 'places', $args );

		parent::register_post_types();
	}
	
	/**
	 * registering the custom taxonomy and the table for category meta
	 */
	function register_taxonomies() {
		global $wpdb;
		$places_cats_args = array(
			'hierarchical'=>true,
			'query_var'=>'places-category',
			'labels'=>array(
					'name'             => __( 'Places Categories', 'bp-checkins' ),
					'singular_name'    => __( 'Places Category', 'bp-checkins' ),
					'edit_item'        => __( 'Edit Places Category', 'bp-checkins' ),
					'update_item'      => __( 'Update Places Category', 'bp-checkins' ),
					'add_new_item'     => __( 'Add New Places Category', 'bp-checkins' ),
					'new_item_name'    => __( 'New Places Category Name', 'bp-checkins' ),
					'all_items'        => __( 'All Places Categories', 'bp-checkins' ),
					'search_items'     => __( 'Search Places Categories', 'bp-checkins' ),
					'parent_item'      => __( 'Parent Places Category', 'bp-checkins' ),
					'parent_item_colon'=> __( 'Parent Places Category:', 'bp-checkins' ))
			);

		//register the Category taxo
		register_taxonomy('places_category', array( 'places' ), $places_cats_args );
		
		$wpdb->places_categorymeta = $wpdb->prefix."places_categorymeta";
		
		//Let's insert a default category if none is set !
		$bp_checkins_check_taxo = get_terms('places_category');
		if( empty( $bp_checkins_check_taxo ) )
			wp_insert_term( __('Default', 'bp-checkins'), 'places_category', array('slug' => 'default') );
		
		parent::register_taxonomies();
	}

}

/**
 * Finally Loads the component into the $bp global
 *
 */
function bp_checkins_load_core_component() {
	global $bp;

	$bp->checkins = new BP_Checkins_Component;
}
add_action( 'bp_loaded', 'bp_checkins_load_core_component' );
?>