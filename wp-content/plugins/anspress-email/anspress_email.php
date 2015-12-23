<?php
/**
 * AnsPress Email
 *
 * Email notification extension for AnsPress
 *
 * @package   AnsPress_Email
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress Email
 * Plugin URI:        http://anspress.io
 * Description:       Email notification extension for AnsPress
 * Version:           1.3
 * Author:            Rahul Aryan
 * Author URI:        http://anspress.io
 * Text Domain:       anspress_email
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI:
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


class AnsPress_Ext_AnsPress_Email
{

	/**
	 * Class instance
	 * @var object
	 * @since 1.0
	 */
	private static $instance;

	var $emails = array();
	var $subject;
	var $message;


	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new AnsPress_Ext_AnsPress_Email(); }

		return self::$instance;
	}
	/**
	 * Initialize the class
	 * @since 1.0
	 */
	public function __construct() {

		if ( ! class_exists( 'AnsPress' ) ) {
			return; // AnsPress not installed
		}
		if ( ! defined( 'ANSPRESS_EMAIL_DIR' ) ) {
			define( 'ANSPRESS_EMAIL_DIR', plugin_dir_path( __FILE__ ) ); }

		if ( ! defined( 'ANSPRESS_EMAIL_URL' ) ) {
				define( 'ANSPRESS_EMAIL_URL', plugin_dir_url( __FILE__ ) ); }

		// internationalization
		add_action( 'init', array( $this, 'textdomain' ) );
		add_filter( 'ap_default_options', array( $this, 'ap_default_options' ) );
		add_action( 'init', array( $this, 'register_option' ), 100 );

		add_action( 'ap_after_new_question', array( $this, 'ap_after_new_question' ) );
		add_action( 'ap_after_new_answer', array( $this, 'ap_after_new_answer' ) );

		add_action( 'ap_select_answer', array( $this, 'select_answer' ), 10, 3 );

		add_action( 'ap_publish_comment', array( $this, 'new_comment' ) );

		add_action( 'ap_after_update_question', array( $this, 'ap_after_update_question' ) );

		add_action( 'ap_after_update_answer', array( $this, 'ap_after_update_answer' ) );

		add_action( 'ap_trash_question', array( $this, 'ap_trash_question' ) );
		add_action( 'ap_trash_answer', array( $this, 'ap_trash_answer' ) );
	}
	/**
	 * Load plugin text domain
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public static function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

		// Load the translations
		load_plugin_textdomain( 'AnsPress_Email', false, $lang_dir );

	}


	/**
	 * Apppend default options
	 * @param   array $defaults
	 * @return  array
	 * @since   1.0
	 */
	public function ap_default_options($defaults) {

		$defaults['notify_admin_email']         = get_option( 'admin_email' );
		$defaults['plain_email']                = false;
		$defaults['notify_admin_new_question']  = true;
		$defaults['notify_admin_new_answer']    = true;
		$defaults['notify_admin_new_comment']   = true;
		$defaults['notify_admin_edit_question'] = true;
		$defaults['notify_admin_edit_answer']   = true;
		$defaults['notify_admin_trash_question'] = true;
		$defaults['notify_admin_trash_answer']  = true;

		$defaults['new_question_email_subject'] = __( 'New question posted by {asker}', 'AnsPress_Email' );
		$defaults['new_question_email_body']    = __( "Hello!\r\nA new question is posted by {asker}\r\n\r\nTitle: {question_title}\r\nDescription:\r\n{question_excerpt}\r\n\r\nLink: {question_link}", 'AnsPress_Email' );

		$defaults['new_answer_email_subject'] = __( 'New answer posted by {answerer}', 'AnsPress_Email' );
		$defaults['new_answer_email_body']    = __( "Hello!\r\nA new answer is posted by {answerer} on {question_title}\r\nAnswer:\r\n{answer_excerpt}\r\n\r\nLink: {answer_link}", 'AnsPress_Email' );

		$defaults['select_answer_email_subject'] = __( 'Your answer is selected as best', 'AnsPress_Email' );
		$defaults['select_answer_email_body']    = __( "Hello!\r\nYour answer on '{question_title}' is selected as best.\r\n\r\nLink: {answer_link}", 'AnsPress_Email' );

		$defaults['new_comment_email_subject'] = __( 'New comment by {commenter}', 'AnsPress_Email' );
		$defaults['new_comment_email_body']    = __( "Hello!\r\nA new comment posted on '{question_title}' by {commenter}.\r\n\r\nLink: {comment_link}", 'AnsPress_Email' );

		$defaults['edit_question_email_subject'] = __( 'A question is edited by {editor}', 'AnsPress_Email' );
		$defaults['edit_question_email_body']    = __( "Hello!\r\nQuestion '{question_title}' is edited by {editor}.\r\n\r\nLink: {question_link}", 'AnsPress_Email' );

		$defaults['edit_answer_email_subject'] = __( 'An answer is edited by {editor}', 'AnsPress_Email' );
		$defaults['edit_answer_email_body']    = __( "Hello!\r\nAnswer on '{question_title}' is edited by {editor}.\r\n\r\nLink: {question_link}", 'AnsPress_Email' );

		$defaults['trash_question_email_subject'] = __( 'A question is trashed by {user}', 'AnsPress_Email' );
		$defaults['trash_question_email_body']    = __( "Hello!\r\nQuestion '{question_title}' is trashed by {user}.\r\n", 'AnsPress_Email' );

		$defaults['trash_answer_email_subject'] = __( 'An answer is trashed by {user}', 'AnsPress_Email' );
		$defaults['trash_answer_email_body']    = __( "Hello!\r\nAnswer on '{question_title}' is trashed by {user}.\r\n", 'AnsPress_Email' );

		return $defaults;
	}

	/**
	 * Sanitize form value
	 * @param  string $name Field value.
	 * @return string
	 */
	public function value($name) {
		$settings = ap_opt();
		if ( isset( $settings[ $name ] ) ) {
			return str_replace( "//", "", $settings[ $name ] );
        }

		return '';
	}


	/**
	 * Register options
	 */
	public function register_option() {
		if ( ! is_admin() ) {
			return;
		}

		// Register general settings.
		ap_register_option_group('email', __( 'Email', 'AnsPress_Email' ) , array(
			array(
				'name' => 'anspress_opt[notify_admin_email]',
				'label' => __( 'Admin email', 'AnsPress_Email' ),
				'desc' => __( 'Enter email where admin notification should be sent', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'notify_admin_email' ),
				'show_desc_tip' => false,
			),

			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Notify admin', 'AnsPress_Email' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[notify_admin_new_question]',
				'label' => __( 'New question', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin for every new question.', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_new_question' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_new_answer]',
				'label' => __( 'New answer', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin for every new answer.', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_new_answer' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_new_comment]',
				'label' => __( 'New comment', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin for every new comment.', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_new_comment' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_edit_question]',
				'label' => __( 'Edit question', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin when question is edited', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_edit_question' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_edit_answer]',
				'label' => __( 'Edit answer', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin when answer is edited', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_edit_answer' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_trash_question]',
				'label' => __( 'Delete question', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin when question is trashed', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_trash_question' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notify_admin_trash_answer]',
				'label' => __( 'Delete answer', 'AnsPress_Email' ),
				'desc' => __( 'Send email to admin when asnwer is trashed', 'AnsPress_Email' ),
				'type' => 'checkbox',
				'value' => $this->value( 'notify_admin_trash_answer' ),
				'show_desc_tip' => false,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New question', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[new_question_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'new_question_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[new_question_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'new_question_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New Answer', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[new_answer_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'new_answer_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[new_answer_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'new_answer_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Select Answer', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[select_answer_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'select_answer_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[select_answer_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'select_answer_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New comment', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[new_comment_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'new_comment_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[new_comment_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'new_comment_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Edit question', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[edit_question_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'edit_question_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[edit_question_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'edit_question_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Edit answer', 'AnsPress_Email' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[edit_answer_email_subject]',
				'label' => __( 'Subject', 'AnsPress_Email' ),
				'type' => 'text',
				'value' => $this->value( 'edit_answer_email_subject' ),
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'anspress_opt[edit_answer_email_body]',
				'label' => __( 'Body', 'AnsPress_Email' ),
				'type' => 'textarea',
				'value' => $this->value( 'edit_answer_email_body' ),
				'attr' => 'style="width:100%;min-height:200px"',
			),
		));
	}

	public function header() {
		$header = '';
		if ( ! $charset = get_bloginfo( 'charset' ) ) {
			$charset = 'utf-8';
		}
		$header .= 'Content-type: text/plain; charset=' . $charset . "\r\n";

		return $header;
	}

	public function replace_tags($content, $args) {
		return strtr( $content, $args );
	}

	public function send_mail($email, $subject, $message) {
		wp_mail( $email, $subject, $message, $this->header() );
	}

	public function initiate_send_email() {

		$this->emails = array_unique( $this->emails );

		if ( ! empty( $this->emails ) && is_array( $this->emails ) ) {
			foreach ( $this->emails as $email ) {
				$this->send_mail( $email, $this->subject, $this->message );
			}
		}
	}

	/**
	 * Send email to admin when new question is created
	 * @param  integer $question_id
	 * @since 1.0
	 */
	public function ap_after_new_question($question_id) {
		if ( ap_opt( 'notify_admin_new_question' ) ) {

			$current_user = wp_get_current_user();

			$question = get_post( $question_id );

			// don't bother if current user is admin
			if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
				return; }

			$args = array(
				'{asker}'             => ap_user_display_name( $question->post_author ),
				'{question_title}'    => $question->post_title,
				'{question_link}'     => get_permalink( $question->ID ),
				'{question_content}'  => $question->post_content,
				'{question_excerpt}'  => ap_truncate_chars( strip_tags( $question->post_content ), 100 ),
			);

			$args = apply_filters( 'ap_new_question_email_tags', $args );

			$this->subject = $this->replace_tags( ap_opt( 'new_question_email_subject' ), $args );

			$this->message = $this->replace_tags( ap_opt( 'new_question_email_body' ), $args );

			$this->emails[] = ap_opt( 'notify_admin_email' );

			/*if ( ($answer->post_status != 'private_post' || $answer->post_status != 'moderate') ) {
				$users = ap_get_subscribers( $question_id, 'q_all', 100 );

				if ( $users ) {
					foreach ( $users as $user ) {
						// Dont send email to poster
						if ( $user->user_email != $current_user->user_email ) {
							$this->emails[] = $user->user_email; }
					}
				}
			}*/

			$this->initiate_send_email();
		}
	}

	public function ap_after_new_answer($answer_id) {

			$current_user = wp_get_current_user();

			$answer = get_post( $answer_id );

			$args = array(
				'{answerer}'        => ap_user_display_name( $answer->post_author ),
				'{question_title}'  => $answer->post_title,
				'{answer_link}'     => get_permalink( $answer->ID ),
				'{answer_content}'  => $answer->post_content,
				'{answer_excerpt}'  => ap_truncate_chars( strip_tags( $answer->post_content ), 100 ),
			);

			$args = apply_filters( 'ap_new_answer_email_tags', $args );

			$this->subject = $this->replace_tags( ap_opt( 'new_answer_email_subject' ), $args );

			$this->message = $this->replace_tags( ap_opt( 'new_answer_email_body' ), $args );

			$this->emails = array();

			if ( ap_opt( 'notify_admin_new_answer' ) && $current_user->user_email != ap_opt( 'notify_admin_email' ) ) {
				$this->emails[] = ap_opt( 'notify_admin_email' );
			}

			if ( ($answer->post_status != 'private_post' || $answer->post_status != 'moderate') ) {
				$subscribers = ap_get_subscribers( $answer->post_parent, 'q_all', 100, true );
				if ( $subscribers ) {
					foreach ( $subscribers as $s ) {
						if ( $s->user_email != $current_user->user_email ) {
							$this->emails[] = $s->user_email;
						}
					}
				}
			}

			$this->initiate_send_email();
	}

	/**
	 * Notify answer author that his answer is selected as best
	 * @param  integer $userid
	 * @param  integer $question_id
	 * @param  integer $answer_id
	 * @return void
	 */
	public function select_answer($selecting_userid, $question_id, $answer_id) {

		$answer = get_post( $answer_id );

		if ( $answer->post_author == get_current_user_id() ) {
			return;
		}

		$args = array(
			'{answerer}'        => ap_user_display_name( $answer->post_author ),
			'{question_title}'  => $answer->post_title,
			'{answer_link}'     => get_permalink( $answer->ID ),
			'{answer_content}'  => $answer->post_content,
			'{answer_excerpt}'  => ap_truncate_chars( strip_tags( $answer->post_content ), 100 ),
		);

		$args = apply_filters( 'ap_select_answer_email_tags', $args );

		$subject = $this->replace_tags( ap_opt( 'select_answer_email_subject' ), $args );

		$message = $this->replace_tags( ap_opt( 'select_answer_email_body' ), $args );

		$this->send_mail( get_the_author_meta( 'email', $answer->post_author ), $subject, $message );
	}

	/**
	 * Notify admin on new comment and is not approved
	 * @param  object $comment Comment id
	 */
	public function new_comment($comment) {

		$current_user = wp_get_current_user();

		$post = get_post( $comment->comment_post_ID );

		$post_id = $post->ID;

		$args = array(
			'{commenter}'         => ap_user_display_name( $comment->user_id ),
			'{question_title}'    => $post->post_title,
			'{comment_link}'      => get_comment_link( $comment ),
			'{comment_content}'   => $comment->comment_content,
		);

		$args = apply_filters( 'ap_new_comment_email_tags', $args );

		$this->subject = $this->replace_tags( ap_opt( 'new_comment_email_subject' ), $args );

		$this->message = $this->replace_tags( ap_opt( 'new_comment_email_body' ), $args );

		$this->emails = array();

		$subscribe_type = $post->post_type == 'answer' ? 'a_all' : 'q_post';

		$subscribers = ap_get_subscribers( $post_id, $subscribe_type, 100, true );

		$post_author  = get_user_by( 'id', $post->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( $s->user_email != $current_user->user_email ) {
					$this->emails[] = $s->user_email; 
				}
			}
		}

		$this->initiate_send_email();
	}

	public function ap_after_update_question($question_id) {

		$current_user = wp_get_current_user();

		$question = get_post( $question_id );

		$this->emails = array();

		if ( ap_opt( 'notify_admin_email' ) != $current_user->user_email && ap_opt( 'notify_admin_edit_question' ) ) {
			$this->emails[] = ap_opt( 'notify_admin_email' );
		}

		$subscribers = ap_get_subscribers( $question_id, array('q_post', 'q_all'), 100, true );

		$post_author  = get_user_by( 'id', $post->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( !empty($s->user_email) && $s->user_email != $current_user->user_email ) {
					$this->emails[] = $s->user_email; 
				}
			}
		}

		if ( ! is_array( $this->emails ) || empty( $this->emails ) ) {
			return;
		}

		$args = array(
			'{asker}'             => ap_user_display_name( $question->post_author ),
			'{editor}'            => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $question->post_title,
			'{question_link}'     => get_permalink( $question->ID ),
			'{question_content}'  => $question->post_content,
			'{question_excerpt}'  => ap_truncate_chars( strip_tags( $question->post_content ), 100 ),
		);

		$args = apply_filters( 'ap_edit_question_email_tags', $args );

		$this->subject = $this->replace_tags( ap_opt( 'edit_question_email_subject' ), $args );

		$this->message = $this->replace_tags( ap_opt( 'edit_question_email_body' ), $args );

		$this->initiate_send_email();
	}

	public function ap_after_update_answer($answer_id) {

		if ( ! ap_opt( 'notify_admin_edit_answer' ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		$answer = get_post( $answer_id );

		$this->emails = array();

		if ( ap_opt( 'notify_admin_email' ) != $current_user->user_email && ap_opt( 'notify_admin_edit_answer' ) ) {
			$this->emails[] = ap_opt( 'notify_admin_email' ); }

		$subscribers = ap_get_subscribers( $answer_id, 'a_all', 100, true );

		$post_author  = get_user_by( 'id', $answer->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( !empty($s->user_email) && $s->user_email != $current_user->user_email ) {
					$this->emails[] = $s->user_email;
				}
			}
		}

		if ( ! is_array( $this->emails ) || empty( $this->emails ) ) {
			return;
		}

		$args = array(
			'{answerer}'          => ap_user_display_name( $answer->post_author ),
			'{editor}'            => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $answer->post_title,
			'{question_link}'     => get_permalink( $answer->post_parent ),
			'{answer_content}'    => $answer->post_content,
		);

		$args = apply_filters( 'ap_edit_answer_email_tags', $args );

		$this->subject = $this->replace_tags( ap_opt( 'edit_answer_email_subject' ), $args );

		$this->message = $this->replace_tags( ap_opt( 'edit_answer_email_body' ), $args );

		$this->initiate_send_email();
	}

	public function ap_trash_question($post) {

		if ( ! ap_opt( 'notify_admin_trash_question' ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		// don't bother if current user is admin
		if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
			return; }

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $post->post_title,
			'{question_link}'     => get_permalink( $post->ID ),
		);

		$args = apply_filters( 'ap_trash_question_email_tags', $args );

		$subject = $this->replace_tags( ap_opt( 'trash_question_email_subject' ), $args );

		$message = $this->replace_tags( ap_opt( 'trash_question_email_body' ), $args );

		// sends email
		$this->send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}

	public function ap_trash_answer($post) {

		if ( ! ap_opt( 'notify_admin_trash_answer' ) ) {
			return; }

		$current_user = wp_get_current_user();

		// don't bother if current user is admin
		if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
			return;
		}

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $post->post_title,
			'{question_link}'     => get_permalink( $post->post_parent ),
		);

		$args = apply_filters( 'ap_trash_answer_email_tags', $args );

		$subject = $this->replace_tags( ap_opt( 'trash_answer_email_subject' ), $args );

		$message = $this->replace_tags( ap_opt( 'trash_answer_email_body' ), $args );

		// sends email
		$this->send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}
}

/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function anspress_ext_AnsPress_Email() {
	$anspress_ext_AnsPress_Email = new AnsPress_Ext_AnsPress_Email();
}
add_action( 'plugins_loaded', 'anspress_ext_AnsPress_Email' );

function anspress_activate_anspress_email() {
	$settings = get_option( 'anspress_opt' );
	unset( $settings['edit_question_email_subject'] );
	unset( $settings['edit_question_email_body'] );
	unset( $settings['edit_answer_email_subject'] );
	unset( $settings['edit_answer_email_body'] );
	update_option( 'anspress_opt', $settings );
}
register_activation_hook( __FILE__, 'anspress_activate_anspress_email' );

/**
 * Get the email ids of all subscribers of question
 * @param  integer $post_id
 * @return array
 * @deprecated 1.3
 */
function ap_get_question_subscribers_data($post_id, $question_subsciber = true) {
	_deprecated_function( 'ap_get_question_subscribers_data', '1.3', '' );
}

/**
 * @deprecated 1.3
 */
function ap_get_comments_subscribers_data($post_id) {
	_deprecated_function( 'ap_get_comments_subscribers_data', '1.3', '' );
}

if ( ! function_exists( 'ap_in_array_r' ) ) {
	function ap_in_array_r($needle, $haystack, $strict = false) {
		foreach ( $haystack as $item ) {
			if ( ($strict ? $item === $needle : $item == $needle) || (is_array( $item ) && in_array_r( $needle, $item, $strict )) ) {
				return true;
			}
		}
		return false;
	}
}
