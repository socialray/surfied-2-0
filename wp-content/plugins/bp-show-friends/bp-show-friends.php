<?php
/*
Plugin Name: BP Show Friends
Plugin URI: http://imathi.eu/tag/bp-show-friends/
Description: Displays the friends of the logged in user or of the displayed user once in BuddyPress Member area
Version: 2.0
Requires at least: 3.7
Tested up to: 3.8
License: GNU/GPL 2
Author: imath
Author URI: http://imathi.eu/
Text Domain:       bp-show-friends
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path:       /languages/
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'BP_Show_Friends' ) ) :
/**
 * Main BP_Show_Friends Class
 *
 * @since BP_Show_Friends (2.0)
 */
class BP_Show_Friends_Widget extends WP_Widget{
	/**
	 * Instance of this class.
	 *
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Some init vars
	 *
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 *
	 * @var      array
	 */
	public static $init_vars = array(
		'bp_version_required' => '1.8.1'
	);

	/**
	 * Initialize the plugin
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
		$this->setup_widget();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	private function setup_globals() {
		/** BP_Show_Friends globals ********************************************/
		$this->version                = '2.0';
		$this->domain                 = 'bp-show-friends';
		$this->file                   = __FILE__;
		$this->basename               = plugin_basename( $this->file );
		$this->plugin_dir             = plugin_dir_path( $this->file );
		$this->plugin_url             = plugin_dir_url( $this->file );
		$this->lang_dir               = trailingslashit( $this->plugin_dir . 'languages' );
		$this->plugin_js              = trailingslashit( $this->plugin_url . 'js' );
		$this->plugin_images          = trailingslashit( $this->plugin_url . 'images' );
	}

	/**
	 * Checks BuddyPress version
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	public static function buddypress_version_check() {
		// taking no risk
		if( !defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$init_vars['bp_version_required'], '>=' );
	}

	/**
	 * Checks if the plugins is network activated
	 * 
	 * Inspired by BuddyPress function bp_is_network_activated()
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses get_site_option()
	 */
	public function is_network_activated() {
		// Default to is_multisite()
		$retval  = is_multisite();

		// Check the sitewide plugins array
		$base    = $this->basename;
		$plugins = get_site_option( 'active_sitewide_plugins' );

		// Override is_multisite() if not network activated
		if ( ! is_array( $plugins ) || ! isset( $plugins[$base] ) )
			$retval = false;

		return (bool) $retval;
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses is_plugin_active_for_network() to check if the plugin has been activated for the network
	 */
	private function setup_hooks() {
		// if BuddyPress version is not supported sends a notice to admin !
		if( ! self::buddypress_version_check() ) {
			add_action( $this->is_network_activated() ? 'network_admin_notices' : 'admin_notices', array( $this, 'warning_notice' ) );
		} else {
			add_action( 'bp_init',                              array( $this, 'load_textdomain' ), 6 );
			add_action( 'bp_widgets_init',                      array( $this, 'register_widget' )    );
			add_action( 'bp_enqueue_scripts',                   array( $this, 'cssjs'           )    );
			add_action( 'wp_ajax_bpsf_refresh_friends', 		array( $this, 'list_friends'    )    );
			add_action( 'wp_ajax_nopriv_bpsf_refresh_friends', array( $this, 'list_friends'    )    );


			// Maybe update plugin version
			add_action( $this->is_network_activated() ? 'network_admin_menu' : 'admin_menu', array( $this, 'db_version' ) );
		}

	}

	/**
	 * Displays a notice to user who are not using the BuddyPress required version
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	public function warning_notice() {
		?>
		<div id="message" class="updated fade">
			<p><?php printf( __( 'BP Show Friends requires BuddyPress version %s, please upgrate or download and install a previous version of it.', 'bp-show-friends' ), self::$init_vars['bp_version_required'] );?></p>
		</div>
		<?php
	}

	public function db_version() {
		if( $this->version != get_option( 'bp-show-friends-version' ) )
			update_option( 'bp-show-friends-version', $this->version );
	}

	/**
	 * Registers the widget
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses register_widget()
	 */
	public function register_widget() {
		register_widget( 'BP_Show_Friends_Widget' );
	}

	/**
	 * Set Up the widget
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses Widget->construct()
	 */
	public function setup_widget() {
		$widget_ops = array( 'classname' => 'widget_show_friends', 'description' => __( 'Show the friends of the loggedin user or of the displayed user if in the member area', 'bp-show-friends' ) );
		parent::__construct( false, _x( 'BP Show Friends', 'widget name', 'bp-show-friends' ), $widget_ops );
	}

	/**
	 * Displays the widget
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses bp_is_user() to check we're on a user's profile
	 * @uses bp_displayed_user_id() to get displayed user id
	 * @uses bp_loggedin_user_id() to get loggedin user id
	 * @uses bp_core_get_user_domain() to build the user domain
	 * @uses bp_get_friends_slug() to get the friends component slug
	 * @uses bp_core_get_user_displayname() to get the display name of the user
	 * @uses is_user_logged_in() to check the visitor is logged in
	 * @uses friends_get_total_friend_count() to get the total friends for the user
	 * @uses BP_Show_Friends_Widget->list_friends() to output the user's friends
	 */
	public function widget( $args = array(), $instance = array() ) {

		$user_id = false;
		$user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id() ;

		if( empty( $user_id ) )
			return;

		extract( $args );

		$instance['per_page'] = !empty( $instance['per_page'] ) ? $instance['per_page'] : 5;
		$instance['size'] = !empty( $instance['size'] ) ? $instance['size'] : 50;

		$user_all_friends_url = trailingslashit( bp_core_get_user_domain( $user_id ) . bp_get_friends_slug() );
		$user_name = bp_core_get_user_displayname( $user_id );
		?>

		<?php if( bp_is_user() || is_user_logged_in() ):

			echo $before_widget;
			echo $before_title;

			if( bp_is_my_profile() ) 
				printf( __( 'My Friends - <a href="%1$s">All (%2$s)</a>', 'bp-show-friends' ), $user_all_friends_url, friends_get_total_friend_count( $user_id ) );
			else 
				printf( __( '%1$s&apos;s Friends - <a href="%2$s">All (%3$s)</a>', 'bp-show-friends' ), $user_name, $user_all_friends_url, friends_get_total_friend_count( $user_id ) );
		    
		    echo $after_title; ?>

			<div class="item-options bpsf-list-options">
				<a href="#" class="bp-show-friends-action current"  data-type="active" data-number="<?php echo intval( $this->number ) ;?>"><?php _e('Recently Actives','bp-show-friends');?></a>&nbsp;|&nbsp;
				<a href="#" class="bp-show-friends-action"  data-type="online" data-number="<?php echo intval( $this->number ) ;?>"><?php _e('Online Friends','bp-show-friends');?></a>
			</div>

			<?php $this->list_friends( $instance['per_page'], $instance['size'] );
			echo $after_widget; ?>

		<?php endif;
	}

	/**
	 * Outputs the list of friends (active or online)
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses bp_is_user() to check we're on a user's profile
	 * @uses bp_displayed_user_id() to get displayed user id
	 * @uses bp_loggedin_user_id() to get loggedin user id
	 * @uses Widget->get_settings() to get the instance of the widget
	 * @uses the Members loop
	 */
	public function list_friends( $limit = 0, $size = 0 ) {
		$user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id() ;

		if( empty( $limit ) || empty( $size ) ) {
			$widget_settings = $this->get_settings();
			$number = intval( $_POST['bpsf_number'] );
			$limit = !empty( $widget_settings[$number]['per_page'] ) ? $widget_settings[$number]['per_page'] : 5;
			$size = !empty( $widget_settings[$number]['size'] ) ? $widget_settings[$number]['size'] : 50;
		}

		// plugins or themes can now order the friends differently !
		$args = apply_filters( 'bp_show_friends_args', 
			array( 
				'user_id'        => $user_id, 
				'type'           =>'active', 
				'per_page'       => $limit,
				'max'            => $limit, 
				'populate_extra' => 0
			)
		);

		$fallback_message = '<p>'.__('No friends!','bp-show-friends').'</p>';

		if( !empty( $_POST['bpsf_type'] ) ) {
			$args['type'] = $_POST['bpsf_type'];

			if( 'online' == $args['type'] )
				$fallback_message = '<p>'.__('No online friends!','bp-show-friends').'</p>';
		}

		$avatar_args = apply_filters( 'bp_show_friends_avatar_args', 
			array(
				'type'   => 'full',
				'width'  => $size,
				'height' => $size,
				'class'  => 'avatar bp-show-friends',
			)
		);

		?>

		<?php if( empty( $_POST['bpsf_type'] ) ) :?>
			<div class="friends-container">
		<?php endif;?>

		    <?php if ( bp_has_members( $args ) ) : ?>
		    	<ul class="bpsf-members">
			      <?php while ( bp_members() ) : bp_the_member(); ?>

			      	<li class="bpsf-member">
				        <div class="item-avatar">
				          	<a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name();?>">
				          		<?php bp_member_avatar( $avatar_args ) ?>
				          	</a>
				        </div>
				        <?php do_action( 'bp_show_friends_after_friend_avatar', bp_get_member_user_id() );?>
				    </li>

			      <?php endwhile; ?>
			     </ul>

		    <?php else:
		    echo $fallback_message;
		    endif;

		if( empty( $_POST['bpsf_type'] ) ) :?>
			</div>
		    <br style="clear:both"/>
		<?php else:
			exit();
		endif;
	}

	/**
	 * Updates the widget settings
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['per_page'] = intval( $new_instance['per_page'] );
		$instance['size'] = intval( $new_instance['size'] );

		return $instance;
	}

	/**
	 * Displays the form to define widget settings
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses bp_core_avatar_dimension() to get max size
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'per_page' => 5, 'size' => 50 ) );
		$per_page = intval( $instance['per_page'] );
		$size     = intval( $instance['size'] );
		$maxsize = bp_core_avatar_dimension( 'full', 'width');
		$maxsize = !empty( $maxsize ) ? $maxsize : 100;
		?>
		
		<p>
			<label for="bp-show-friends-widget-per-page">
				<?php _e( 'Max Number of Avatars:', 'bp-show-friends' ); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'per_page' ); ?>" name="<?php echo $this->get_field_name( 'per_page' ); ?>" type="number" min="1" step="1" value="<?php echo intval( $per_page ); ?>" style="width: 30%" />
			</label>
		</p>
		<p>
			<label for="bp-show-friends-widget-avatar-size">
				<?php _e( 'Avatar size (in pixels)', 'bp-show-friends' ); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" type="number" min="40" max="<?php echo $maxsize;?>" step="5" value="<?php echo intval( $size ); ?>" style="width: 30%" />
			</label>
		</p>
		
	<?php
	}

	/**
	 * Enqueues the js and css files only if BuddyPlug needs it
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses is_active_widget() to check if the widget is active
	 * @uses is_admin() to avoid loading the script and css in WordPress Admin
	 * @uses is_network_admin() to avoid loading the script and css in WordPress Network Admin
	 * @uses bp_is_user() to check we're on a user's profile
	 * @uses is_user_logged_in() to check the visitor is logged in
	 * @uses BP_Show_Friends_Widget->css_datas() to eventually load css from theme
	 * @uses wp_enqueue_style() to safely add our style to WordPress queue
	 * @uses wp_enqueue_script() to safely add our script to WordPress queue
	 */
	public function cssjs() {

		if ( is_active_widget( false, false, $this->id_base ) && !is_admin() && !is_network_admin() && ( bp_is_user() || is_user_logged_in() ) ) {
			// CSS is Theme's territory, so let's help him to easily override plugin's css.
			$css_datas = (array) $this->css_datas();
			wp_enqueue_style( $css_datas['handle'], $css_datas['location'], false, $this->version );

			// JavaScript & image loader thanks to wp_localize_script
			wp_enqueue_script( 'bp-show-friends-js', $this->plugin_js .'bp-show-friends.js', array( 'jquery' ), $this->version, true );
		}
		
	}

	/**
	 * The theme can override plugin's css
	 * 
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 */
	public function css_datas() {
		$file = 'css/bp-show-friends.css';
		
		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() ) . $file ; 
			$handle   = 'bp-show-friends-child-css';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() ) . $file ;
			$handle   = 'bp-show-friends-parent-css';

		// use our style
		} else {
			$location = $this->plugin_url . $file;
			$handle   = 'bp-show-friends-css';
		}

		return array( 'handle' => $handle, 'location' => $location );
	}

	/**
	 * Loads the translation files
	 *
	 * @package BP_Show_Friends_Widget
	 * @since    2.0
	 * 
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/bp-show-friends/' . $mofile;

		// Look in global /wp-content/languages/bp-show-friends folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/bp-show-friends/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}
	
}

/**
 * Main BP Show Friends Function
 *
 * Loads the plugin once BuddyPress is fully loaded 
 *
 * @package BP Show Friends
 * @since 2.0
 *
 * @uses buddypress() to get BuddyPress main instance
 * @uses bp_is_active() to check the friends component is active
 * @uses BP_Show_Friends_Widget::get_instance() to attach globals to BuddyPress instance
 */
function bp_show_friends_loader() {
	$bp =  buddypress();

	if( bp_is_active( 'friends' ) ) {

		if( empty( $bp->friends->extend ) )
			$bp->friends->extend = new stdClass();

		$bp->friends->extend->bp_show_friends = BP_Show_Friends_Widget::get_instance();
	}

}

add_action( 'bp_include', 'bp_show_friends_loader' );

endif;
