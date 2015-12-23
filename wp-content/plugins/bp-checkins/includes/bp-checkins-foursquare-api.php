<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Bp_Checkins_Foursquare_Oauth{
	protected $client_id;
    protected $client_secret;
    protected $access_token_url = 'https://foursquare.com/oauth2/access_token';
    protected $grant_type = 'authorization_code';
    protected $redirect_uri;
    protected $authenticate_url = 'https://foursquare.com/oauth2/authenticate';

	function bp_checkins_foursquare_oauth() {
		$this->__construct();
	}

	function __construct(){
		global $bp;
		$this->client_id = bp_get_option('foursquare-client-id');
		$this->client_secret = bp_get_option('foursquare-client-secret');
		$this->redirect_uri = isset( $bp->pages->{BP_CHECKINS_SLUG}->slug ) ? site_url( $bp->pages->{BP_CHECKINS_SLUG}->slug ) : site_url( BP_CHECKINS_SLUG );
	}

	function oauth_user_url() {
		return $this->authenticate_url . '?client_id=' . $this->client_id . '&response_type=code&redirect_uri=' . $this->redirect_uri;
	}
	
	function token_url( $code = false ) {
		global $bp;
		
		if( empty( $bp->loggedin_user->id ) )
			return array( 'type' => 'error', __('You must be loggedin to perform this action', 'bp-checkins') );
		
		if( empty( $this->access_token_url ) || empty( $this->client_id ) || empty( $this->client_secret ) )
			return array( 'type' => 'error', 'message' => __('Please contact admin, the Foursquare credentials of this website are not set', 'bp-checkins') );
			
		if( empty( $code ) )
			return array( 'type' => 'error', 'message' => __('OOps something went wrong, please try again later', 'bp-checkins') );
		
		$foursquare_token_url  = $this->access_token_url . '?';
		$foursquare_token_url .= 'client_id='. $this->client_id;
		$foursquare_token_url .= '&client_secret=' . $this->client_secret . '&grant_type=authorization_code';
		$foursquare_token_url .= '&redirect_uri='.$this->redirect_uri;
		$foursquare_token_url .= '&code=' . $code;
		
		$foursquare_request = new WP_Http;
		$foursquare_result = $foursquare_request->request( $foursquare_token_url, array('sslverify' => false) );
		
		if( !$foursquare_result || !empty( $foursquare_result->errors ) ) {
			return array( 'type' => 'error', 'message' => __('OOps something went wrong while contacting Foursquare, please try again later', 'bp-checkins') );
		} else {
			
			$foursquare_access_token = $foursquare_result['body'];
			$parse_token = json_decode($foursquare_access_token);
			
			if( !empty( $parse_token->access_token ) && update_user_meta( $bp->loggedin_user->id, 'foursquare_token', $parse_token->access_token ) )
				return array( 'type' => 'success', 'message' => __('Congratulations, your foursquare account is now linked to this website', 'bp-checkins' ) );	
			
			else
				return array( 'type' => 'error', 'message' => sprintf(__( 'Oops Foursquare replyed : %s You should check your foursquare network connexion settings to see if our website is authorized.', 'bp-checkins' ), $parse_token->error ));
			
		}
	}

}

class Bp_Checkins_Foursquare_Import{
	var $user_id;
	var $foursquare_api_url = 'https://api.foursquare.com/v2/users/self/checkins';
	var $user_token;
	var $user_latest;
	
	function bp_checkins_foursquare_import( $user_id = false ) {
		$this->__construct( $user_id );
	}

	function __construct( $user_id = false ) {
		if ( !empty( $user_id ) ) {
			$this->user_id = $user_id;
			$this->user_token();
		}
	}
	
	function user_token() {
		
		$this->user_token = get_user_meta( $this->user_id, 'foursquare_token', true );
		$this->user_latest = get_user_meta( $this->user_id, 'foursquare_latest_timestamp', true );
		
	}
	
	function user_import() {
		
		if( empty( $this->user_token ) || empty( $this->user_latest ) )
			return array( 'type' => 'error', 'message' => __('Your foursquare credentials have not been found, please try again later', 'bp-checkins') );
		
		$foursquare_url  = $this->foursquare_api_url . '?oauth_token='.$this->user_token;
		$foursquare_url .= '&afterTimestamp='.$this->user_latest.'&v=20120519';
		
		$import_results[] = Bp_Checkins_Foursquare_Import::save_activity( $foursquare_url, $this->user_id, $this->user_latest );
		
		Bp_Checkins_Foursquare_Import::log( $import_results );
		
		switch ( $import_results[0]['code'] ) {
			case 3 :
				return array( 'type' => 'error', 'message' => __('Error : your token seems to be revoked. Please link your foursquare account', 'bp-checkins') );
				break;
			case 4 :
				return array( 'type' => 'error', 'message' => sprintf(__('It seems you have not checked in at Foursquare since your latest import (%s)', 'bp-checkins'), bp_checkins_date( $this->user_latest ) ) ); 
				break;
			case 5 :
				return array( 'type' => 'success', 'message' => $import_results[0]['info'] );
				break;
			default :
				return array( 'type' => 'error', 'message' => __('OOps something went wrong while contacting Foursquare, please try again later', 'bp-checkins') );
				break;
		}

	}
	
	function wpcron_import() {
		global $wpdb;
		
		$user_metas = $wpdb->get_results(
            $wpdb->prepare("SELECT user_id, meta_value as foursquare_token FROM $wpdb->usermeta where meta_key= %s", 'foursquare_token' )
        );

		if( count($user_metas) < 1 )
			return false;
			
		$import_results = array();
		
		foreach( $user_metas as $meta ) {
			
			$user_latest = get_user_meta( $meta->user_id, 'foursquare_latest_timestamp', true );
			$user_token = $meta->foursquare_token;
			
			$foursquare_url  = $this->foursquare_api_url . '?oauth_token='.$user_token;
			$foursquare_url .= '&afterTimestamp='.$user_latest.'&v=20120519';
			
			$import_results[] = Bp_Checkins_Foursquare_Import::save_activity( $foursquare_url, $meta->user_id, $user_latest );
			
		}
		
		Bp_Checkins_Foursquare_Import::log( $import_results );
		
	}
	
	function save_activity( $foursquare_url = false, $user_id = false, $user_latest = 0 ) {
		
		if( empty( $foursquare_url ) )
			return array( 'user_id' => $user_id, 'time' => date('Y-m-d H:i:s'), 'info' => __('No foursquare url were given', 'bp-checkins'), 'code' => 1 );
			
		$foursquare_api_request = new WP_Http;
		$foursquare_result = $foursquare_api_request->request( $foursquare_url, array('sslverify' => false) );

		if( !$foursquare_result || !empty( $foursquare_result->errors ) ) {

			return array( 'user_id' => $user_id, 'time' => date('Y-m-d H:i:s'), 'info' => __('foursquare not responding', 'bp-checkins'), 'code' => 2 );
		} 
		else {

			$foursquare_parse_user_stream = $foursquare_result['body'];
			$foursquare_user_stream = json_decode( $foursquare_parse_user_stream );

			if( !empty( $foursquare_user_stream->meta->code ) && !empty( $foursquare_user_stream->meta->errorDetail ) ) {
				
				delete_user_meta( $user_id, 'foursquare_token' );
				return array( 'user_id' => $user_id,  'time' => date('Y-m-d H:i:s'), 'info' => __('user revoked his auth, meta deleted', 'bp-checkins'), 'code' => 3 );
				
			} 
			else {
				$user_checkins = $foursquare_user_stream->response->checkins->items;

				if( !$user_checkins || empty( $user_checkins ) || count( $user_checkins ) < 1 ) {
					return array( 'user_id' => $user_id, 'time' => date('Y-m-d H:i:s'), 'info' => __('no new checkins', 'bp-checkins'), 'code' => 4 );
				} 
				else {
					
					$from_user_link   = bp_core_get_userlink( $user_id );
					$checkin_action  = sprintf( __( '%s added a <span class="foursquare-checkin">foursquare</span> checkin', 'bp-checkins' ), $from_user_link );
					$primary_link     = bp_core_get_userlink( $user_id, false, true );
					$timestamp_latest = $user_latest;
					$checkins_imported = 0;
					
					foreach( $user_checkins as $checkin ){

						if( $checkin->createdAt > $timestamp_latest)
							$timestamp_latest = $checkin->createdAt;

						$foursquare_activity_args = array();
						$foursquare_activity_meta_args = array();

						$foursquare_activity_meta_args = array('bpci_activity_lat' => $checkin->venue->location->lat,
						                            'bpci_activity_lng' => $checkin->venue->location->lng,
													'bpci_activity_address' => $checkin->venue->name .' ('.$checkin->venue->location->country.')' );
						if( $checkin->photos->count > 0 )
							$foursquare_activity_meta_args['bpci_picture'] = $checkin->photos->items[0]->sizes->items;

						$foursquare_activity_args = array( 

							'user_id'      => $user_id,
							'action'       => $checkin_action,
							'content'      => $checkin->shout,
							'primary_link' => $primary_link,
							'component'    => 'checkins',
							'type'         => 'foursquare_checkin',
							'secondary_item_id' => $checkin->id,
							'recorded_time' => bp_checkins_date( $checkin->createdAt ),
							'bp_checkins_meta' => $foursquare_activity_meta_args

							);

						$checkins_imported += bp_checkins_post_foursquare_updates( $foursquare_activity_args );

					}
					
					if( update_user_meta( $user_id, 'foursquare_latest_timestamp', $timestamp_latest + 1 ) )
						 return array( 'user_id' => $user_id, 'time' => date('Y-m-d H:i:s'), 'info' => sprintf(__('%d checkins imported', 'bp-checkins'), $checkins_imported ), 'code' => 5 );
					
				}
			}
		}
		
	}
	
	function log( $results = false) {
		global $wpdb;
		
		if( !empty( $results ) && is_array( $results ) ){
			foreach( $results as $trace ){
				if( $trace['code'] <= 3 )
					$type = 'error';
					
				else
					$type = 'success';
					
				$log = '[' . $trace['time'] . '] ' . $trace['info'];
					
				$wpdb->insert( $wpdb->base_prefix.'bp_checkins_foursquare_logs', 
							   array( 'user_id' => intval( $trace['user_id'] ), 'type' => $type, 'log' => $log ), 
							   array('%d', '%s', '%s') );
			}
		}
		
	}
}

function bp_checkins_is_logged_in_user_connected(){
	global $bp;
	$foursquare_token = get_user_meta($bp->loggedin_user->id, 'foursquare_token', true);
	
	if( !empty( $foursquare_token ) )
		return true;
		
	else
		return false;
}

function bp_checkins_foursquare_user_activation() {
	$foursquare_api = new Bp_Checkins_Foursquare_Oauth;
	$foursquare_link = $foursquare_api->oauth_user_url();
	?>
	<div id="foursquare">
		<a id="foursquare-connect" href="<?php echo $foursquare_link;?>"><span><?php _e('Connect to your foursquare account', 'bp-checkins');?></span></a>
	</div>
	<?php
}

function bp_checkins_post_foursquare_updates( $args ) {
	global $bp;

	$defaults = array(
		'action'            => '',
		'content'           => '',

		'component'         => 'checkins',
		'type'              => 'foursquare_checkin',
		'primary_link'      => '',

		'user_id'           => $bp->loggedin_user->id,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => bp_core_current_time(),
		'bp_checkins_meta'  => false,
		'hide_sitewide'     => false 
	);
	
	$params = wp_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );
	
	$activity_id = bp_activity_add( array(
		'user_id'      => $user_id,
		'action'       => apply_filters( 'bp_activity_new_update_action', $action ),
		'content'      => apply_filters( 'bp_activity_new_update_content', $content ),
		'primary_link' => apply_filters( 'bp_activity_new_update_primary_link', $primary_link ),
		'component'    => $component,
		'type'         => $type,
		'secondary_item_id' => $secondary_item_id,
		'recorded_time' => $recorded_time,
		'hide_sitewide'     => $hide_sitewide
	) );
	
	if( $activity_id && $bp_checkins_meta && is_array($bp_checkins_meta) ) {
		foreach( $bp_checkins_meta as $meta_key => $meta_value ){
			
			bp_activity_update_meta( $activity_id, $meta_key, $meta_value );
			
		}
		
	}
	if( $activity_id )
		return 1;
}


function bp_checkins_foursquare_user_actions() {
	global $bp;
	
	if( !bp_checkins_is_foursquare_ready())
		return;
	
	if( bp_is_current_component('checkins') && !bp_displayed_user_id() && !bp_current_action()  && !empty( $_GET['code']) ) {
		
		$referer = trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() ) . 'checkins-settings';
		
		$foursquare_auth_user = new Bp_Checkins_Foursquare_Oauth;
		$foursquare_user = $foursquare_auth_user->token_url( $_GET['code'] );
		
		if( $foursquare_user['type'] == 'error' )
			bp_core_add_message( $foursquare_user['message'], 'error' );
			
		else {
			update_user_meta( $bp->loggedin_user->id, 'foursquare_latest_timestamp', current_time('timestamp') );
			bp_core_add_message( $foursquare_user['message'] );
		}
			
			
		bp_core_redirect( $referer );
		
	}
	
	if( bp_is_settings_component() && bp_displayed_user_id() && 'checkins-settings' == bp_current_action() && !empty( $_GET['update'] )){
		
		$referer = trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() ) . 'checkins-settings';
		
		if ( (int)bp_get_option( 'foursquare-user-import' ) )
			return false;
		
		$user_import = new Bp_Checkins_Foursquare_Import( $bp->loggedin_user->id );
		
		$user_import_do = $user_import->user_import();
		
		if( $user_import_do['type'] == 'error' )
			bp_core_add_message( $user_import_do['message'], 'error' );
			
		else {
			bp_core_add_message( $user_import_do['message'] );
		}
			
			
		bp_core_redirect( $referer );
		
	}
}

add_action('bp_actions', 'bp_checkins_foursquare_user_actions', 1);

/* cron hook */
add_action('bp_checkins_cron_job', 'bp_checkins_do_job');

function bp_checkins_do_job() {
	
	require( BP_CHECKINS_PLUGIN_DIR . '/includes/bp-checkins-wp-cron.php' );
	
	do_action('bp_checkins_foursquare_syncing');
}

function bp_checkins_fs_add_filter_options(){
	?>
	<option value="foursquare_checkin"><?php _e( 'Foursquare checkins', 'bp-checkins' ); ?></option>
	<?php
}

add_action('bp_activity_filter_options', 'bp_checkins_fs_add_filter_options', 12);
add_action('bp_member_activity_filter_options', 'bp_checkins_fs_add_filter_options', 12 );
add_action('bp_checkins_filter_options', 'bp_checkins_fs_add_filter_options', 1 );
add_action('bp_checkins_member_checkins_filters', 'bp_checkins_fs_add_filter_options', 1 );