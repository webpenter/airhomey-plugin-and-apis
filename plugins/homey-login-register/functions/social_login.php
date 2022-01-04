<?php
/**
 * User: saad
 * Date: 21/01/19
 * Time: 04:22 PM
 */

/* -----------------------------------------------------------------------------------------------------------
 *  Login With Facebook
 -------------------------------------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_facebook_login_oauth', 'homey_facebook_login_oauth' );
add_action( 'wp_ajax_homey_facebook_login_oauth', 'homey_facebook_login_oauth' );

if( !function_exists('homey_facebook_login_oauth') ) {

    function homey_facebook_login_oauth() {
        $dir = plugin_dir_path( __DIR__ ) . 'social/Facebook/';
        require_once $dir.'autoload.php';

        $dashboard_profile_link = homey_get_template_link_dash('template/dashboard-profile.php');
        $dashboard_profile_link = str_replace( 'http://', 'https://', $dashboard_profile_link );

        $facebook_api    =  homey_option('facebook_api_key');
        $facebook_secret =  homey_option('facebook_secret');

        $fb = new Facebook\Facebook([
            'app_id' => $facebook_api,
            'app_secret' => $facebook_secret,
            'default_graph_version' => 'v2.12',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl( $dashboard_profile_link, $permissions );

        print $loginUrl;
        wp_die();
    }
}

if( !function_exists('homey_facebook_login') ):

    function homey_facebook_login($get_vars){
        session_start();
        $dir = plugin_dir_path( __DIR__ ) . 'social/Facebook/';
        require $dir.'autoload.php';

        $dashboard_profile_link = homey_get_template_link_dash('template/dashboard-profile.php');
        $dashboard_profile_link = str_replace( 'http://', 'https://', $dashboard_profile_link );

        $facebook_api    =  homey_option('facebook_api_key');
        $facebook_secret =  homey_option('facebook_secret');

        $fb = new Facebook\Facebook([
            'app_id' => $facebook_api,
            'app_secret' => $facebook_secret,
            'default_graph_version' => 'v2.12',
            'http_client_handler' => 'curl', // can be changed to stream or guzzle
            'persistent_data_handler' => 'session' // make sure session has started
        ]);

        if( isset( $get_vars['code'] ) )
        {
            $helper = $fb->getRedirectLoginHelper();
            // Trick below will avoid "Cross-site request forgery validation failed. Required param "state" missing." from Facebook
            $_SESSION['FBRLH_state'] = $_REQUEST['state'];
        }
        else
        {
            // login helper with redirect_uri
            $helper = $fb->getRedirectLoginHelper( $dashboard_profile_link );
        }


        // see if we have a code in the URL
        if( isset( $get_vars['code'] ) ) {
            // get new access token if we've been redirected from login page
            try {
                // get access token
                $access_token = $helper->getAccessToken();

                // save access token to persistent data store
                $helper->getPersistentDataHandler()->set( 'access_token', $access_token );
            } catch ( Exception $e ) {
                // error occured
                echo 'Exception 1: ' . $e->getMessage() . '';
            }

            // get stored access token
            $access_token = $helper->getPersistentDataHandler()->get( 'access_token' );
        }

        // check if we have an access_token, and that it's valid
        if ( $access_token && !$access_token->isExpired() )
        {
            // set default access_token so we can use it in any requests
            $fb->setDefaultAccessToken( $access_token );
            try {
                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/me?fields=first_name,last_name,email', $access_token);
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $user = $response->getGraphObject()->asArray();

            $profile_image_url = 'https://graph.facebook.com/'.$user['id'].'/picture?width=300&height=300';

            $fb_email       = $user['email'];
            $fb_firstname   = $user['first_name'];
            $fb_lastname    = $user['last_name'];
            $password       = $user['id'];

            $username = explode( '@', $fb_email );
            $username=  $username[0];
            $display_name = $fb_firstname.' '.$fb_lastname;

            $user = get_user_by( 'email', $fb_email );

            $info                   = array();
            $info['remember']       = true;

            if(isset($user->ID)){
                $homeURL  = add_query_arg(
                    array(
                        'you_are_logged_in' => 1
                    ),
                    $dashboard_profile_link );

                if(isset($user->user_login)){
                    update_user_meta($user->ID, 'is_email_verified', 1);

                    add_filter( 'authenticate', 'homey_nop_auto_login', 3, 10 );
                    homey_nop_auto_login($user, $user->user_login, null, $homeURL );
                }

            }else {
                homey_register_user_social($fb_email, $username, $display_name, $password, $profile_image_url);

                $info['user_login'] = $username;
                $info['user_password'] = $password;
                $info['remember'] = true;

                $wordpress_user_id = username_exists($username);
                update_user_meta($wordpress_user_id, 'is_email_verified', 1);

                $user_signon = wp_signon($info, false);

                if (is_wp_error($user_signon)) {
                    wp_redirect(home_url());
                    exit;
                } else {
                    ///ueu
                    wp_redirect($dashboard_profile_link);  // redirect to any page
                    exit;
                }
            }

        }
        exit;

    }

endif;

/* -----------------------------------------------------------------------------------------------------------
 *  Login With Yahoo
 -------------------------------------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_yahoo_login', 'homey_yahoo_login' );
add_action( 'wp_ajax_homey_yahoo_login', 'homey_yahoo_login' );

if( !function_exists('homey_yahoo_login') ) {

    function homey_yahoo_login() {

        $dir = plugin_dir_path( __DIR__ ) . 'social/';
        require $dir.'openid.php';

        $dashboard_profile_link = homey_get_template_link_dash('template/dashboard-profile.php');
        $dashboard_profile_link = str_replace( 'http://', 'https://', $dashboard_profile_link );

        try {
            $openID = new LightOpenID( homey_get_domain_openid() );

            if ( !$openID->mode ) {
                $openID->identity = 'https://me.yahoo.com';

                $openID->required = array(
                    'namePerson',
                    'namePerson/first',
                    'namePerson/last',
                    'contact/email',
                );
                $openID->optional = array('namePerson', 'namePerson/friendly');
                $openID->returnUrl = $dashboard_profile_link;

                print  $openID->authUrl();
                wp_die();

            }
        } catch (ErrorException $e) {
            echo $e->getMessage();
        }
    }
}

/* -----------------------------------------------------------------------------------------------------------
 *  Login With Google
 -------------------------------------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_google_login_oauth', 'homey_google_login_oauth' );
add_action( 'wp_ajax_homey_google_login_oauth', 'homey_google_login_oauth' );


if( !function_exists('homey_google_login_oauth') ):

    function homey_google_login_oauth(){

        $google_client_id      =  homey_option('google_client_id');
        $google_client_secret  =  homey_option('google_secret');
        $google_developer_key  =  homey_option('google_api_key');
        $google_redirect_url   =  homey_get_template_link_dash('template/dashboard-profile.php');
        $google_redirect_url = str_replace( 'http://', 'https://', $google_redirect_url );

        $dir = plugin_dir_path( __DIR__ ) . 'social/';
        require_once $dir.'google/Google_Client.php';
        require_once $dir.'google/contrib/Google_Oauth2Service.php';

        $client = new Google_Client();

        $client->setApplicationName('Login to Homey');
        $client->setClientId($google_client_id);
        $client->setClientSecret($google_client_secret);
        $client->setDeveloperKey($google_developer_key);
        $client->setRedirectUri($google_redirect_url);
        $client->setScopes(array('email', 'profile'));

        $google_oauthV2 = new Google_Oauth2Service($client);
        $authUrl = $client->createAuthUrl();

        print $authUrl;
        wp_die();
    }

endif;


if( !function_exists('homey_google_oauth_login') ):

    function homey_google_oauth_login($get_vars){
        $allowed_html   =   array();
        $dir = plugin_dir_path( __DIR__ ) . 'social/';
        require_once $dir.'google/Google_Client.php';
        require_once $dir.'google/contrib/Google_Oauth2Service.php';

        $google_client_id      =  homey_option('google_client_id');
        $google_client_secret  =  homey_option('google_secret');
        $google_developer_key  =  homey_option('google_api_key');
        $google_redirect_url   =  homey_get_template_link_dash('template/dashboard-profile.php');
        $google_redirect_url = str_replace( 'http://', 'https://', $google_redirect_url );

        $gClient = new Google_Client();
        $gClient->setApplicationName('Login to Homey');
        $gClient->setClientId($google_client_id);
        $gClient->setClientSecret($google_client_secret);
        $gClient->setDeveloperKey($google_developer_key);
        $gClient->setRedirectUri($google_redirect_url);
        $gClient->setScopes(array('email', 'profile'));

        $google_oauthV2 = new Google_Oauth2Service($gClient);

        if (isset($_REQUEST['code'])) {
            $code = sanitize_text_field ( wp_kses($_REQUEST['code'],$allowed_html) );
            $gClient->authenticate($code);
        }

        if ($gClient->getAccessToken()) {

            $dashboard_url     =   homey_get_template_link_dash('template/dashboard-profile.php');
            $user              =   $google_oauthV2->userinfo->get();
            $user_id           =   $user['id'];
            $display_name      =   wp_kses($user['name'], $allowed_html);
            $email             =   wp_kses($user['email'], $allowed_html);

            $first_name = $last_name = '';
            if(isset($user['family_name'])){
                $last_name = $user['family_name'];
            }
            if(isset($user['given_name'])){
                $first_name = $user['given_name'];
            }

            $profile_image_url = filter_var($user['picture'], FILTER_VALIDATE_URL);

            $username = str_replace(' ', '.', $display_name);
            $user = get_user_by( 'email', $email );

            $info                   = array();
            $info['remember']       = true;

            if(isset($user->ID)){
                $homeURL  = add_query_arg(
                    array(
                        'you_are_logged_in' => 1
                    ),
                    $dashboard_url );

                if(isset($user->user_login)) {
                    update_user_meta($user->ID, 'is_email_verified', 1);

                    add_filter('authenticate', 'homey_nop_auto_login', 3, 10);
                    homey_nop_auto_login($user, $user->user_login, null, $homeURL);
                }
                update_user_meta($user->ID, 'is_email_verified', 1);
            }else{
                homey_register_user_social( $email, $username, $display_name, $user_id, $profile_image_url );

                $wordpress_user_id = username_exists($username);
                update_user_meta($wordpress_user_id, 'is_email_verified', 1);

                wp_set_password( $user_id, $wordpress_user_id ) ;


                $info['user_login']     = $username;
                $info['user_password']  = $user_id;

                $user_signon            = wp_signon( $info, false );
                update_user_meta($wordpress_user_id, 'is_email_verified', 1);

                if ( is_wp_error($user_signon) ){
                    wp_redirect( home_url() );
                } else {
                    ///ueu
                    wp_redirect($dashboard_url);
                }
            }
        }
    }

endif;

/* --------------------------------------------------------------------------
 * Open Id login
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_openid_login') ) {

    function homey_openid_login($get_vars) {

        $dir = plugin_dir_path( __DIR__ ) . 'social/';
        require $dir.'openid.php';

        $openID = new LightOpenID(homey_get_domain_openid());
        $allowed_html = array();

        if ( $openID->validate() ) {

            $dashboard_profile_link = homey_get_template_link_dash('template/dashboard-profile.php');
            $dashboard_profile_link = str_replace( 'http://', 'https://', $dashboard_profile_link );
            $openID_identity = wp_kses($get_vars['openid_identity'], $allowed_html);

            if ( strrpos($openID_identity, 'yahoo') ) {
                $email = wp_kses($get_vars['openid_ax_value_email'], $allowed_html);
                $username = explode( '@', $email );
                $username=  $username[0];
                $display_name = wp_kses($get_vars['openid_ax_value_fullname'], $allowed_html);
                $openID_identity_pos = strrpos( $openID_identity, '/a/.' );
                $openID_identity = str_split( $openID_identity, $openID_identity_pos + 4 );
                $openID_identity_code = $openID_identity[1];
            }

            $user = get_user_by( 'email', $email );

            $info                   = array();
            $info['remember']       = true;

            if(isset($user->ID)){
                $homeURL  = add_query_arg(
                    array(
                        'you_are_logged_in' => 1
                    ),
                    $dashboard_profile_link );
                if(isset($user->user_login)){
                    update_user_meta($user->ID, 'is_email_verified', 1);

                    add_filter( 'authenticate', 'homey_nop_auto_login', 3, 10 );
                    homey_nop_auto_login($user, $user->user_login, null, $homeURL );
                }
            }else {
                homey_register_user_social($email, $username, $display_name, $openID_identity_code, '');

                $info['user_login'] = $username;
                $info['user_password'] = $openID_identity_code;
                $info['remember'] = true;

                $wordpress_user_id = username_exists($username);
                update_user_meta($wordpress_user_id, 'is_email_verified', 1);

                $user_logon = wp_signon($info, false);

                if (is_wp_error($user_logon)) {
                    wp_redirect(home_url());
                } else {
                    ///ueu
                    wp_redirect($dashboard_profile_link);
                }
            }
        }
    }
}

/* --------------------------------------------------------------------------
 * Get domain open id
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_get_domain_openid') ) {
    function homey_get_domain_openid()
    {
        $home_url = get_home_url();
        $home_url = str_replace('http://', '', $home_url);
        $home_url = str_replace('https://', '', $home_url);
        return $home_url;
    }
}


/* --------------------------------------------------------------------------
 * Register User Via Social ( )
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_register_user_social') ) {

    function homey_register_user_social( $email, $username, $display_name, $password, $profile_image_url  )
    {

        $user_as_agent = homey_option('user_as_agent');
        $full_name = explode(' ', $display_name );
        $first_name = $full_name[0];
        $last_name = $full_name[1];
        $user_role = get_option( 'default_role' );

        if ( email_exists($email) ) {

            if (username_exists( $username )) {
                return;
            } else {
                $userID = wp_create_user( $username, $password, ' ');

                if( !is_wp_error( $userID ) ) {
                    wp_update_user(array('ID' => $userID, 'display_name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name));
                    //update_user_meta($userID, 'fave_author_custom_picture', $profile_image_url);

                    homey_wp_new_user_notification( $userID, $password );
                }
            }

        } else {

            if ( username_exists($username) ) {
                return;

            } else {
                $userID = wp_create_user( $username, $password, $email );

                if( !is_wp_error( $userID ) ) {
                    wp_update_user(array('ID' => $userID, 'display_name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name));
                    //update_user_meta($userID, 'fave_author_custom_picture', $profile_image_url);


                    homey_wp_new_user_notification( $userID, $password );
                }
            }

        }

        $profile_attach_id = homey_insert_attachment_from_url($profile_image_url);
        homey_save_user_photo($userID, $profile_attach_id);

    }
}

if(!function_exists('homey_insert_attachment_from_url')) {
    function homey_insert_attachment_from_url($attachment_file) {

        $response = wp_remote_get($attachment_file, array( 'timeout' => 8 ) );
        if( !is_wp_error( $response ) ) {
            $bits = wp_remote_retrieve_body( $response );
            $filename = strtotime("now").'_'.uniqid().'.jpg';
            $upload = wp_upload_bits( $filename, null, $bits );
            $data['guid'] = $upload['url'];
            $data['post_mime_type'] = 'image/jpeg';
            $attach_id = wp_insert_attachment( $data, $upload['file'], 0 );

            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            $profile_attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            wp_update_attachment_metadata( $attach_id, $profile_attach_data );

            return $attach_id;
        }
    }

}


if(!function_exists('homey_social_after_login_redirect_page')) {
    function homey_social_after_login_redirect_page() {
        global $post;
        $login_redirect = '';
        $after_login_redirect = homey_option('login_redirect');
        if ($after_login_redirect == 'same_page') {

            if (is_tax()) {
                $login_redirect = get_term_link(get_query_var('term'), get_query_var('taxonomy'));
            } else {
                if (is_home() || is_front_page()) {
                    $login_redirect = site_url();
                } else {
                    if (!is_404() && !is_search() && !is_author()) {
                        $login_redirect = get_permalink($post->ID);
                    }
                }
            }

        } else {
            $login_redirect = homey_option('login_redirect_link');
        }
        return $login_redirect;
    }
 }

function homey_nop_auto_login( $user, $username, $password, $url=null ) {
    if ( ! $user ) {
        $user = get_user_by( 'email', $username );
    }
    if ( ! $user ) {
        $user = get_user_by( 'login', $username );
    }

    if ( $user ) {
        wp_set_current_user( $user->ID, $user->data->user_login );
        wp_set_auth_cookie( $user->ID );
        do_action( 'wp_login', $user->data->user_login, $user);

        // remove filter to work proper with other login.
        remove_filter( 'authenticate', 'homey_nop_auto_login', 3, 10 );

        wp_safe_redirect( $url );
        exit;
    }
    return 0;
}