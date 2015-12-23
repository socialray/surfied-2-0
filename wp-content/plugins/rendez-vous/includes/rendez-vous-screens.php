<?php
/**
 * Rendez Vous Screens.
 *
 * Manage screens
 *
 * @package Rendez Vous
 * @subpackage Screens
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Screen Class.
 *
 * @package Rendez Vous
 * @subpackage Screens
 *
 * @since Rendez Vous (1.0.0)
 */
class Rendez_Vous_Screens {

	/**
	 * The constructor
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_filters();
		$this->setup_actions();
	}

	/**
	 * Starts screen management
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function manage_screens() {
		$rdv = rendez_vous();

		if ( empty( $rdv->screens ) ) {
			$rdv->screens = new self;
		}

		return $rdv->screens;
	}

	/**
	 * Set some globals
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function setup_globals() {

		$this->template      = '';
		$this->template_dir  = rendez_vous()->includes_dir . 'templates';
		$this->screen = '';
	}

	/**
	 * Set filters
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	private function setup_filters() {
		if ( bp_is_current_component( 'rendez_vous' ) ) {
			add_filter( 'bp_located_template',   array( $this, 'template_filter' ), 20, 2 );
			add_filter( 'bp_get_template_stack', array( $this, 'add_to_template_stack' ), 10, 1 );
		}
	}

	/**
	 * Set Actions
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	private function setup_actions() {
		add_action( 'rendez_vous_schedule', array( $this, 'schedule_actions' ) );
	}

	/**
	 * Filter the located template
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function template_filter( $found_template = '', $templates = array() ) {
		$bp = buddypress();

		// Bail if theme has it's own template for content.
		if ( ! empty( $found_template ) )
			return $found_template;

		// Current theme do use theme compat, no need to carry on
		if ( $bp->theme_compat->use_with_current_theme )
			return false;

		return apply_filters( 'rendez_vous_load_template_filter', $found_template );
	}

	/**
	 * Add template dir to stack (not used)
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function add_to_template_stack( $templates = array() ) {
		// Adding the plugin's provided template to the end of the stack
		// So that the theme can override it.
		return array_merge( $templates, array( $this->template_dir ) );
	}

	/**
	 * Shedule Screen
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function schedule_screen() {

		do_action( 'rendez_vous_schedule' );

		self::load_template( '', 'schedule' );
	}

	/**
	 * Attend Screen
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function attend_screen() {

		do_action( 'rendez_vous_attend' );

		// We'll only use members/single/plugins
		self::load_template( '', 'attend' );
	}

	/**
	 * Load the templates
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function load_template( $template = '', $screen = '' ) {
		$rdv = rendez_vous();
		/****
		 * Displaying Content
		 */
		$rdv->screens->template = $template;

		if ( ! empty( $rdv->screens->screen ) )
			$screen = $rdv->screens->screen;

		if ( buddypress()->theme_compat->use_with_current_theme && ! empty( $template ) ) {
			add_filter( 'bp_get_template_part', array( __CLASS__, 'template_part' ), 10, 3 );
		} else {
			// You can only use this method for users profile pages
			if ( ! bp_is_directory() ) {

				$rdv->screens->template = 'members/single/plugins';
				add_action( 'bp_template_title',   "rendez_vous_{$screen}_title"   );
				add_action( 'bp_template_content', "rendez_vous_{$screen}_content" );
			}
		}

		/* This is going to look in wp-content/plugins/[plugin-name]/includes/templates/ first */
		bp_core_load_template( apply_filters( "rendez_vous_template_{$screen}", $rdv->screens->template ) );
	}

	/**
	 * Filter template part (not used)
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function template_part( $templates, $slug, $name ) {
		if ( $slug != 'members/single/plugins' ) {
	        return $templates;
		}
	    return array( rendez_vous()->screens->template . '.php' );
	}

	/**
	 * Set Actions
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function schedule_actions() {
		$this->screen = rendez_vous_handle_actions();
	}
}
add_action( 'bp_init', array( 'Rendez_Vous_Screens', 'manage_screens' ) );
