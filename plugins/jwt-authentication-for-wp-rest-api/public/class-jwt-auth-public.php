<?php

/** Requiere the JWT library. */
use \Firebase\JWT\JWT;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://enriquechavez.co
 * @since      1.0.0
 */
 
/** 
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     Enrique Chavez <noone@tmeister.net>
 */   
require ABSPATH . 'wp-admin/includes/image.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'public/functions.php';
class Jwt_Auth_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of this plugin.
     */
    private $version;

    /**
     * The namespace to add to the api calls.
     *
     * @var string The namespace to add to the api call
     */
    private $namespace;

    /**
     * Store errors to display if the JWT is wrong
     *
     * @var WP_Error
     */
    private $jwt_error = null;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->namespace = $this->plugin_name . '/v' . intval($this->version);
    }

    /**
     * Add the endpoints to the API
     */
    public function add_api_routes()
    {
        register_rest_route($this->namespace, 'token', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_token'),
        ));
 
        register_rest_route($this->namespace, 'token/validate', array(
            'methods' => 'POST',
            'callback' => array($this, 'validate_token'),
        ));
         register_rest_route($this->namespace, 'token/register', array(
            'methods' => 'POST',
            'callback' => array($this, 'register_token'),
        ));
         register_rest_route($this->namespace, 'token/retrieve_password', array(
            'methods' => 'POST',
            'callback' => array($this, 'retrieve_password_api'),
        ));
         register_rest_route($this->namespace, 'token/update_password', array(
            'methods' => 'POST',
            'callback' => array($this, 'user_up_password'),
        ));
          register_rest_route($this->namespace, 'token/update_user', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_user_fields'),
        ));
         register_rest_route($this->namespace, 'token/profile_image', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_image'),
        ));
         register_rest_route($this->namespace, 'token/address_form', array(
            'methods' => 'POST',
            'callback' => array($this, 'user_address_form'),
        ));
         register_rest_route($this->namespace, 'token/emergency_contact', array(
            'methods' => 'POST',
            'callback' => array($this, 'emergency_contact_form'),
        ));
         register_rest_route($this->namespace, 'token/social', array(
            'methods' => 'POST',
            'callback' => array($this, 'social_media_form'),
        ));
         register_rest_route($this->namespace, 'token/user_info', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_user_info'),
        ));
         register_rest_route($this->namespace, 'token/user_host', array(
            'methods' => 'Get',
            'callback' => array($this, 'get_user_host'),
        ));
         register_rest_route($this->namespace, 'token/settings_API', array(
            'methods' => 'POST',
            'callback' => array($this, 'action_settings_API'),
        ));
         register_rest_route($this->namespace, 'token/get_settings_API', array(
            'methods' => 'POST',
            'callback' => array($this, 'action_get_settings_API'),
        ));
        register_rest_route($this->namespace, 'search/homey_half_map', array(
           'methods' => 'POST',
           'callback' => array($this, 'homey_half_map_db'),
       ));
       register_rest_route($this->namespace, 'listing/list_detail', array(
          'methods' => 'GET',
          'callback' => array($this, 'get_list_detail'),
      ));
         register_rest_route($this->namespace, 'search/search_availability', array(
            'methods' => 'GET',
            'callback' => array($this, 'search_availability_db'),
        ));

         register_rest_route($this->namespace, 'listing/listing_submission', array(
            'methods' => 'POST',
            'callback' => array($this, 'listing_submission_db'),
        ));

         register_rest_route($this->namespace, 'listing/listing_gallery_up', array(
            'methods' => 'POST',
            'callback' => array($this, 'listing_gallery_upload'),
        ));
        register_rest_route($this->namespace, 'listing/message', array(
           'methods' => 'GET',
           'callback' => array($this, 'message_db'),
       ));
       register_rest_route($this->namespace, 'messages/message_detail', array(
          'methods' => 'GET',
          'callback' => array($this, 'message_detail_db'),
      ));
      
      register_rest_route($this->namespace, 'messages/send_message', array(
        'methods' => 'POST',
        'callback' => array($this, 'send_message_db'),
    ));

         register_rest_route($this->namespace, 'listing/invoices', array(
            'methods' => 'GET',
            'callback' => array($this, 'invoices_db'),
        ));
         register_rest_route($this->namespace, 'listing/invoice_detail', array(
            'methods' => 'GET',
            'callback' => array($this, 'invoices_detail_db'),
        ));
         register_rest_route($this->namespace, 'listing/get_trending', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_destinations'),
        ));
         register_rest_route($this->namespace, 'blogs/get_blogs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_blog'),
        ));
        register_rest_route($this->namespace, 'listing/comfort_place', array(
           'methods' => 'GET',
           'callback' => array($this, 'get_comfort_place'),
       ));
       register_rest_route($this->namespace, 'profile/rservation', array(
          'methods' => 'GET',
          'callback' => array($this, 'get_rservation'),
      ));
      register_rest_route($this->namespace, 'profile/rservation_detail', array(
         'methods' => 'GET',
         'callback' => array($this, 'get_detail_rservation'),
     ));
     register_rest_route($this->namespace, 'profile/confirm_reservation', array(
        'methods' => 'POST',
        'callback' => array($this, 'confirm_reservation_status'),
    ));
    register_rest_route($this->namespace, 'profile/decline_reservation', array(
       'methods' => 'POST',
       'callback' => array($this, 'decline_reservation_status'),
   ));
   register_rest_route($this->namespace, 'profile/wallet', array(
      'methods' => 'GET',
      'callback' => array($this, 'wallet'),
  ));
    register_rest_route($this->namespace, 'profile/wallet_detail', array(
        'methods' => 'GET',
        'callback' => array($this, 'wallet_detail'),
    ));
     register_rest_route($this->namespace, 'booking/booking_request', array(
        'methods' => 'POST',
        'callback' => array($this, 'app_add_reservation'),
    ));
     register_rest_route($this->namespace, 'profile/username_existsmy', array(
        'methods' => 'GET',
        'callback' => array($this, 'username_existsmy'),
    ));
		
     register_rest_route($this->namespace, 'site-logo/logo', array(
        'methods' => 'GET',
        'callback' => array($this, 'site_logo'),
    ));

     
    }

    /**
     * Add CORs suppot to the request.
     */
    public function add_cors_support()
    {
        $enable_cors = defined('JWT_AUTH_CORS_ENABLE') ? JWT_AUTH_CORS_ENABLE : false;
        if ($enable_cors) {
            $headers = apply_filters('jwt_auth_cors_allow_headers', 'Access-Control-Allow-Headers, Content-Type, Authorization');
            header(sprintf('Access-Control-Allow-Headers: %s', $headers));
        }
    }  
	public function site_logo()
	{
		$custom_logo = homey_option( 'custom_logo', false, 'url' );
		$splash_logo = homey_option( 'custom_logo_splash', false, 'url' );
		if(homey_is_transparent_logo()) {
			$custom_logo = $splash_logo;
		}
		return $custom_logo;
	}

    public function confirm_reservation_status($request)
    {
             return confirm_reservation_fn($request);  
    }  

    public function decline_reservation_status($request)
    {
             return decline_reservation_fn($request);  
    }

    public function get_detail_rservation($request)
    {
             return rservation_detail($request);  
    }

   

    public function get_rservation($request)
    {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        return rservation($request);  
        
           
    }

    public function get_comfort_place()
    {

        return comfort_place();

   
    }


    public function get_blog()
    {
        $listing_no   =  3;
        $paged        = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type'        =>  'post',
            'paged'             => $paged,
            'posts_per_page'    => $listing_no,
        );
        $listings = array();
  
        $res_query = new WP_Query($args);
        if( $res_query->have_posts() ):
                while ($res_query->have_posts()): $res_query->the_post(); 
                    $listing = array();
                    $listing_id = get_the_ID();
                    $listing['id']  =get_the_ID();
                    $listing['title']  =get_the_title();
                    $listing['description']  =wp_strip_all_tags(substr(get_the_content(), 0, 200));
                    $the_cat=get_the_category();
                    $listing['cat_name']  =$the_cat[0]->cat_name;
                    $listing['get_time']  =sprintf( esc_html__( '%s ago', 'textdomain' ), human_time_diff(get_the_time ( 'U' ), current_time( 'timestamp' ) ) );
                    $listing['url']  = get_the_post_thumbnail_url( $listing_id, 'homey-listing-thumb');
                    $listing['author']  = get_the_author_meta( 'user_nicename'); 
                    $listings[]=$listing;
                endwhile;
                return $listings;
                 endif;
                wp_reset_postdata();
    }

    public function get_destinations()
    {
        return destinations();

   
    }
    public function invoices_detail_db()
    {
        return invoices_detail();


    }
    public function invoices_db()
    {

        return invoices();

    }

    public function message_db()
    {
       return messages();
    }

    public function wallet()
    {
       return wallet_fn();
    }
    public function wallet_detail()
    {
       return wallet_detail_fn();
    }
    
    public function message_detail_db()
    {
       return message_detail_fn();
    }
    public function send_message_db($request)
    {
		$_POST['user_id'] =$request->get_param('user_id');
        $_POST['listing_id'] =$request->get_param('listing_id');
        $_POST['thread_id'] =$request->get_param('thread_id');
        $_POST['message'] =$request->get_param('message');
        $_POST['listing_image_ids'] =$request->get_param('listing_image_ids');
        if($_POST['listing_id']){
       return api_start_thread();} else {
       return api_thread_message();}
    }

    public function listing_gallery_upload()
    {
        $submitted_file = $_FILES['listing_upload_file'];
        $is_dimension_valid = homey_listing_image_dimension($submitted_file);
        $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

        if ( isset( $uploaded_image['file'] ) && $is_dimension_valid != -1 ) {
            $file_name          =   basename( $submitted_file['name'] );
            $file_type          =   wp_check_filetype( $uploaded_image['file'] );

            // Prepare an array of post data for the attachment.
            $attachment_details = array(
                'guid'           => $uploaded_image['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
            $attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
            $listing_thumb = wp_get_attachment_image_src( $attach_id, 'homey-listing-thumb' );
            $feat_image_url = wp_get_attachment_url( $attach_id );

            $ajax_response = array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $attach_id,
                'full_image'    => $feat_image_url,
                'thumb'    => $listing_thumb[0],
            );

            echo json_encode( $ajax_response );
            die;

        } else {
            $reason = esc_html__('Image upload failed!','homey');
            if($is_dimension_valid == -1){
               $reason = esc_html__('Image Dimensions Error','homey');
            }

            $ajax_response = array( 'success' => false, 'reason' => $reason );
            echo json_encode( $ajax_response );
            die;
        }
    }
    public function listing_submission_db()
    {
        $new_listing="";
        return listing_submission_filter( $new_listing);
    }
    /**
     * Get the user and password in the request body and generate a JWT
     *
     * @param [type] $request [description]
     *
     * @return [type] [description]
     */
       
     public function app_add_reservation($request)
     {
            $admin_email = get_option( 'admin_email' ); 
            $userID       =$request->get_param('user_id');
            $local = homey_get_localization();
            $allowded_html = array();
            $reservation_meta = array();
    
            $listing_id = $request->get_param('listing_id');
            $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
            $check_in_date     = $request->get_param('check_in_date');
            $check_out_date    = $request->get_param('check_out_date');
            $extra_options    = $request->get_param('extra_options');
            $guest_message = stripslashes ( $request->get_param('guest_message'));
            $guests   = $request->get_param('guests') ;
            $title = $local['reservation_text'];
            $booking_type = homey_booking_type_by_id($listing_id);
            
            $owner = homey_usermeta($listing_owner_id);
            $owner_email = $owner['email'];
            // return 
            //         array(
            //             'success' => $userID,
            //             'listing_id' => $listing_id,
            //             'check_in_date' => $check_in_date,
            //             'check_out_date' => $check_out_date
            //         );
            if ($userID === 0 ) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['login_for_reservation']
                    );
                wp_die();
            }
    
            if($userID == $listing_owner_id) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['own_listing_error']
                    );
                wp_die();
            }
    
            if(strtotime($check_out_date) <= strtotime($check_in_date)) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['dates_not_available']
                    );
                wp_die();
            }
    
            //check security
            // $nonce = $_REQUEST['security'];
            // if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {
    
            //     return json_encode(
            //         array(
            //             'success' => false,
            //             'message' => $local['security_check_text']
            //         )
            //     );
            //     wp_die();
            // }
    
            $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
            $is_available = $check_availability['success'];
            $check_message = $check_availability['message'];
    
            if($is_available) {
    
                if( $booking_type == 'per_week' ) {
                    $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
    
                    $price_per_week    = $prices_array['price_per_week'];
                    $weeks_total_price = $prices_array['weeks_total_price'];
                    $total_weeks_count = $prices_array['total_weeks_count'];
    
                    $reservation_meta['price_per_week'] = $price_per_week;
                    $reservation_meta['weeks_total_price'] = $weeks_total_price;
                    $reservation_meta['total_weeks_count'] = $total_weeks_count;
                    $reservation_meta['reservation_listing_type'] = 'per_week';
    
                } else if( $booking_type == 'per_month' ) {
                    $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
    
                    $price_per_month    = $prices_array['price_per_month'];
                    $months_total_price = $prices_array['months_total_price'];
                    $total_months_count = $prices_array['total_months_count'];
    
                    $reservation_meta['price_per_month'] = $price_per_month;
                    $reservation_meta['months_total_price'] = $months_total_price;
                    $reservation_meta['total_months_count'] = $total_months_count;
                    $reservation_meta['reservation_listing_type'] = 'per_month';
    
                } else if( $booking_type == 'per_day_date' ) {
    
                    $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                    $price_per_night = $prices_array['price_per_day_date'];
                    $nights_total_price = $prices_array['nights_total_price'];
    
                    $reservation_meta['price_per_day_date'] = $price_per_night;
                    $reservation_meta['price_per_night'] = $price_per_night;
                    $reservation_meta['days_total_price'] = $nights_total_price;
                    $reservation_meta['reservation_listing_type'] = 'per_day_date';
                } else {
    
                    $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                    $price_per_night = $prices_array['price_per_night'];
                    $nights_total_price = $prices_array['nights_total_price'];
    
                    $reservation_meta['price_per_night'] = $price_per_night;
                    $reservation_meta['nights_total_price'] = $nights_total_price;
                    $reservation_meta['reservation_listing_type'] = 'per_night';
                }
    
                $reservation_meta['no_of_days'] = $prices_array['days_count'] = $booking_type == 'per_day_date' ? $prices_array['days_count'] : $prices_array['days_count'];
                $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
    
                $upfront_payment = $prices_array['upfront_payment'];
                $balance = $prices_array['balance'];
                $total_price = $prices_array['total_price'];
                $cleaning_fee = $prices_array['cleaning_fee'];
                $city_fee = $prices_array['city_fee'];
                $services_fee = $prices_array['services_fee'];
                $days_count = $prices_array['days_count'];
                $period_days = $prices_array['period_days'];
                $taxes = $prices_array['taxes'];
                $taxes_percent = $prices_array['taxes_percent'];
                $security_deposit = $prices_array['security_deposit'];
                $additional_guests = $prices_array['additional_guests'];
                $additional_guests_price = $prices_array['additional_guests_price'];
                $additional_guests_total_price = $prices_array['additional_guests_total_price'];
                $booking_has_weekend = $prices_array['booking_has_weekend'];
                $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    
                $reservation_meta['check_in_date'] = $check_in_date;
                $reservation_meta['check_out_date'] = $check_out_date;
                $reservation_meta['guests'] = $guests;
                $reservation_meta['listing_id'] = $listing_id;
                $reservation_meta['upfront'] = $upfront_payment;
                $reservation_meta['balance'] = $balance;
                $reservation_meta['total'] = $total_price;
    
                $reservation_meta['cleaning_fee'] = $cleaning_fee;
                $reservation_meta['city_fee'] = $city_fee;
                $reservation_meta['services_fee'] = $services_fee;
                $reservation_meta['period_days'] = $period_days;
                $reservation_meta['taxes'] = $taxes;
                $reservation_meta['taxes_percent'] = $taxes_percent;
                $reservation_meta['security_deposit'] = $security_deposit;
                $reservation_meta['additional_guests_price'] = $additional_guests_price;
                $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
                $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
                $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;
    
                $reservation = array(
                    'post_title'    => $title,
                    'post_status'   => 'publish',
                    'post_type'     => 'homey_reservation' ,
                    'post_author'   => $userID
                );
                $reservation_id =  wp_insert_post($reservation );
    
                $reservation_update = array(
                    'ID'         => $reservation_id,
                    'post_title' => $title.' '.$reservation_id
                );
                wp_update_post( $reservation_update );
    
                update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
                update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
                update_post_meta($reservation_id, 'listing_renter', $userID);
                update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
                update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
                update_post_meta($reservation_id, 'reservation_guests', $guests);
                update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
                update_post_meta($reservation_id, 'reservation_status', 'under_review');
                update_post_meta($reservation_id, 'is_hourly', 'no');
                update_post_meta($reservation_id, 'extra_options', $extra_options);
    
                update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
                update_post_meta($reservation_id, 'reservation_balance', $balance);
                update_post_meta($reservation_id, 'reservation_total', $total_price);
    
                $pending_dates_array = homey_get_booking_pending_days($listing_id);
                update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);
    
                return 
                    array(
                        'success' => true,
                        'message' => $local['request_sent']
                    );
    
                $message_link = homey_thread_link_after_reservation($reservation_id);
                $email_args = array(
                    'reservation_detail_url' => reservation_detail_link($reservation_id),
                    'guest_message' => $guest_message,
                    'message_link' => $message_link
                );
                
                if(!empty(trim($guest_message)) ){
                    do_action('homey_create_messages_thread', $guest_message, $reservation_id);
                }
    
                homey_email_composer( $owner_email, 'new_reservation', $email_args );
                homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );
                
                if(isset($current_user->user_email)){
                    $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
                    $reservation_detail_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page );
                    $email_args = array( 'reservation_detail_url' => $reservation_detail_link );
    
                    homey_email_composer( $current_user->user_email, 'new_reservation_sent', $email_args );
                }
    
                wp_die();
    
            } else { // end $check_availability
                return 
                    array(
                        'success' => false,
                        'message' => $check_message
                    );
                wp_die();
            }
    
        
     
                        
     }
    public function search_availability_db($request)
    {
       return search_availability($request);
    
                       
    }
    
      
    public function get_list_detail()
    {
             return list_detail_fn();  
    }
    public function homey_half_map_db($request)
    {
        return homey_half_map_fn($request);
    }
    public function generate_token($request)
    {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        $username = $request->get_param('username');
        $password = $request->get_param('password');

        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        }*/
        /** Try to authenticate the user with the passed credentials*/
        $user = wp_authenticate($username, $password);

        /** If the authentication fails return a error*/
        if (is_wp_error($user)) {
			return array(
                    'status' => $user,
                 );
//             $error_code = $user->get_error_code();
//             return new WP_Error(
//                 '[jwt_auth] ' . $error_code,
//                 $user->get_error_message($error_code),
//                 array(
//                     'status' => 410,
//                 )
//             );
        }

        /** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $user->data->ID,
                ),
            ),
        );

        /** Let the user modify the token data before the sign. */
        $token = JWT::encode(apply_filters('jwt_auth_token_before_sign', $token, $user), $secret_key);

        /** The token is signed, now create the object with no sensible user data to the client*/
        $avatar=api_get_author_by_id('70', '70', 'img-circle media-object avatar', $user->data->ID)['photo'];
        $current_user = get_userdata($user->data->ID);
        $data = array(
            'token' => $token,
            'user_id' => $user->data->ID,
            'user_email' => $user->data->user_email,
            'user_nicename' => $user->data->user_nicename,
            'user_display_name' => $user->data->display_name,
            'photo' =>$avatar,
            'roles' =>implode(" ",$current_user->roles),
        );

        /** Let the user modify the data before send it back */
        return apply_filters('jwt_auth_token_before_dispatch', $data, $user);
    }
    public function action_settings_API($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
       /* if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        }*/ 
            $color_cheme= $request->get_param('name');
            $color_code=$request->get_param('value');

            update_option($color_cheme, $color_code);
            // get an option
           $option = get_option($color_cheme);
        /*
          // array of options
          $data_r = array('title' => 'hello world!', 1, false );
          // add a new option
          add_option('wporg_custom_option', $data_r);
          // get an option
          $options_r = get_option('wporg_custom_option');
          // output the title
          echo esc_html($options_r['title']);*/
       
        if ( ! $option || is_wp_error( $option ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $option;
    }
    public function action_get_settings_API($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
       /* if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */
            $color_cheme= $request->get_param('name');
            $option = get_option($color_cheme);
       
        if ( ! $option || is_wp_error( $option ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $option;
    }
    function username_exists( $username ) 
          {
              $user = get_user_by( 'login', $username );
              if ( $user ) {
                  $user_id = $user->ID;
              } else {
                  $user_id = false;
              }
         return apply_filters( 'username_exists', $user_id, $username );
          }
    public function register_token($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        $user_name = $request->get_param('username');
        $user_email = $request->get_param('useremail');
        $password = $request->get_param('password');
        $user_role = get_option( 'default_role' );
        $allowed_html = array();
        if($request->get_param('role') != '' ){
            $user_role =$request->get_param('role')  ? sanitize_text_field( wp_kses( $request->get_param('role'), $allowed_html ) ) : $user_role;
        } else {
            $user_role = $user_role;
        }

        if ( username_exists( $user_name ) ) {
        return  array(
                    'status' => 454,
                    'error' => 'This username is already registered. Please choose another one',
                );

        } 
        if (preg_match("/^[0-9A-Za-z_]+$/", $user_name) == 0) {
            return new WP_Error('jwt_auth_bad_config',
            __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 455,
                    'error' => 'Invalid username (do not use special characters or spaces)!',
                )
            );   wp_die();
        }
        if( email_exists( $user_email ) ) {
            return new WP_Error('jwt_auth_bad_config',
            __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 455,
                    'error' => 'This email address is already registered.',
                )
            );
            wp_die();
        }
        if( !is_email( $user_email ) ) {
            return new WP_Error('jwt_auth_bad_config',
            __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 4555,
                    'error' => 'Invalid email address',
                )
            );         wp_die();
        }

         $user_id = wp_create_user( $user_name,$password,$user_email );
         if ( is_wp_error($user_id) ) {
            echo json_encode( array( 'success' => false, 'msg' => $user_id ) );
            wp_die();
        } else {

            wp_update_user( array( 'ID' => $user_id, 'role' => $user_role ) );
            return array('status' => 200,'user_id'=>$user_id,'message'=>'Your account was created and you can login now!');
            homey_wp_new_user_notification( $user_id, $user_password );
        }

        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      
    }

      public function user_up_password($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
            $userdata = array('ID' => $request->get_param('user_id'),'user_pass'=>$request->get_param('password'));
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */

        if ( username_exists( $request->get_param('username') ) ) {
        return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: This username is already registered. Please choose another one.'),
                array(
                    'status' => 405,
                )
            );

        } 

        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         $user_id =wp_update_user($userdata);
        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $user_id;
    }
      public function update_user_fields($request)
    {
        
        //return $request;
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
           //$userdata = array('ID' => $request->get_param('user_id'),'display_name' => $request->get_param('display_name'));
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */

        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         //$user_id =wp_update_user($userdata);
         $user_id=$request->get_param('user_id');
         if($user_id){ 
            $metas = array(
                'first_name'=>$request->get_param('first_name'),
                'last_name'   => $request->get_param('last_name'),
                'homey_native_language'   => $request->get_param('language'),
                'homey_other_language'   => $request->get_param('other_language'),
                'description'   => $request->get_param('bio'),
            );
             
            foreach($metas as $key => $value) {
                update_user_meta( $user_id, $key, $value );
            }}
        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $user_id;
    }

         public function get_user_host($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
           $userdata = "homey_host";//$request->get_param('user_id');
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */
         $args = array(
            'role' => 'homey_host'
            ); 
         $result=array(); 
        $users = get_users($args, array( 'fields' => array( 'ID' ) ) );
        foreach($users as $user){
                //print_r(get_user_meta ( $user->ID));
                $re=array(); 
                // $result[]=get_user_meta ( $user->ID);
                 $result[] = api_get_author_by_id('70', '70', 'img-circle media-object avatar id', $user->ID);
                 //$re['description']=$user_meta->description;
                 //$result[]=$re;
            }
         //array_merge($array1, $array2);
      
        if ( ! $users || is_wp_error( $users ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
       return $result;
    }

     public function get_user_info($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
           $userdata = $request->get_param('user_id');
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */
       
         $user = get_user_by( 'ID', $userdata );
         $array2    = array();
         $all_meta = get_user_meta( $userdata );

          foreach( $all_meta as $key => $meta ) {
             
                  $array2[$key] = $meta[0];
          }
          $array1 = $user->data;  
          $author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $userdata);
          $post =$array2['homey_author_picture_id'];
          $img_url =wp_get_attachment_url($post);
           
          $d = array(
            "user" => $array1,
            "meta" => $array2,
            "author" => $author,
            "img_url" => $img_url
             
          );
        $result = $d;//array_merge($array1, $array2);
      
        if ( ! $user || is_wp_error( $user ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $result;
    }

      public function upload_image($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
       /* if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */


        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         $user_id = $_REQUEST['user_id'];
        
         $homey_user_image = $_FILES['homey_file_data_name'];
        $homey_wp_handle_upload = wp_handle_upload( $homey_user_image, array( 'test_form' => false ) );

        if ( isset( $homey_wp_handle_upload['file'] ) ) {
            $file_name  = basename( $homey_user_image['name'] );
            $file_type  = wp_check_filetype( $homey_wp_handle_upload['file'] );

            $uploaded_image_details = array(
                'guid'           => $homey_wp_handle_upload['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $profile_attach_id      =   wp_insert_attachment( $uploaded_image_details, $homey_wp_handle_upload['file'] );
            $profile_attach_data    =   wp_generate_attachment_metadata( $profile_attach_id, $homey_wp_handle_upload['file'] );
            wp_update_attachment_metadata( $profile_attach_id, $profile_attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $profile_attach_id, 'thumbnail' );
            homey_save_user_photo($user_id, $profile_attach_id);

            echo json_encode( array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $profile_attach_id
            ));
            die;

        } else {
            echo json_encode( array( 'success' => false, 'reason' => 'Profile Photo upload failed!' ) );
            die;
        }
        
    }

      public function user_address_form($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        }*/ 

        if ( username_exists( $request->get_param('username') ) ) {
        return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: This username is already registered. Please choose another one.'),
                array(
                    'status' => 405,
                )
            );

        } 

        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         $user_id =  $request->get_param('user_id');//wp_update_user($userdata);
         if($user_id){ 
            $metas = array(
                'homey_street_address'   => $request->get_param('street_address'),
                'homey_apt_suit'   => $request->get_param('apt_suit'),
                'homey_city'   => $request->get_param('city'),
                'homey_state'   => $request->get_param('state'),
                'homey_zipcode'   => $request->get_param('zipcode'),
                'neighborhood'   => $request->get_param('neighborhood'),
                'homey_neighborhood'   => $request->get_param('country'),
            );

            foreach($metas as $key => $value) {
                update_user_meta( $user_id, $key, $value );
            }
            return $metas;
        }
        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      
    }
      public function emergency_contact_form($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */

        if ( username_exists( $request->get_param('username') ) ) {
        return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: This username is already registered. Please choose another one.'),
                array(
                    'status' => 405,
                )
            );

        } 

        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         $user_id =  $request->get_param('user_id');//wp_update_user($userdata);
         if($user_id){ 
            $metas = array(
                'homey_em_contact_name'   => $request->get_param('em_contact_name'),
                'homey_em_relationship'   => $request->get_param('em_relationship'),
                'homey_em_email'   => $request->get_param('em_email'),
                'homey_em_phone'   => $request->get_param('em_phone')
            );

            foreach($metas as $key => $value) {
                update_user_meta( $user_id, $key, $value );
            }}
        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $user_id;
    }
      public function social_media_form($request)
    {
    
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        } */

        if ( username_exists( $request->get_param('username') ) ) {
        return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: This username is already registered. Please choose another one.'),
                array(
                    'status' => 405,
                )
            );

        } 

        //$user_obj = get_userdata( $user_id ); return $user_obj; die();
         $user_id =  $request->get_param('user_id');//wp_update_user($userdata);
         if($user_id){ 
            $metas = array(
                'homey_author_facebook'   => $request->get_param('facebook'),
                'homey_author_twitter'   => $request->get_param('twitter'),
                'homey_author_linkedin'   => $request->get_param('linkedin'),
                'homey_author_googleplus'   => $request->get_param('googleplus'),
                'homey_author_instagram'   => $request->get_param('instagram'),
                'homey_author_pinterest'   => $request->get_param('pinterest'),
                'homey_author_youtube'   => $request->get_param('youtube'),
                'homey_author_vimeo'   => $request->get_param('vimeo'),
                'homey_author_airbnb'   => $request->get_param('airbnb'),
                'homey_author_trip_advisor'   => $request->get_param('trip_advisor')
            );

            foreach($metas as $key => $value) {
                update_user_meta( $user_id, $key, $value );
            }}
        if ( ! $user_id || is_wp_error( $user_id ) ) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('<strong>Error</strong>: Could not register you&hellip; please contact the <a href="mailto:%s">site admin</a>!'),
                array(
                    'status' => 406,
                )
            );
            }
        
      return $user_id;
    }
      public function retrieve_password_api($request)
    {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        $user_login = $request->get_param('user_login');

        /** First thing, check the secret key if not exist return a error*/
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        }*/
        /** Try to authenticate the user with the passed credentials*/
        $user = retrieve_password($user_login);
        return $user;
    }



  
    /**
     * This is our Middleware to try to authenticate the user according to the
     * token send.
     *
     * @param (int|bool) $user Logged User ID
     *
     * @return (int|bool)
     */



    public function determine_current_user($user)
    {
        /**
         * This hook only should run on the REST API requests to determine
         * if the user in the Token (if any) is valid, for any other
         * normal call ex. wp-admin/.* return the user.
         *
         * @since 1.2.3
         **/
        $rest_api_slug = rest_get_url_prefix();
        $valid_api_uri = strpos($_SERVER['REQUEST_URI'], $rest_api_slug);
        if (!$valid_api_uri) {
            return $user;
        }

        /*
         * if the request URI is for validate the token don't do anything,
         * this avoid double calls to the validate_token function.
         */
        $validate_uri = strpos($_SERVER['REQUEST_URI'], 'token/validate');
        if ($validate_uri > 0) {
            return $user;
        }

        $token = $this->validate_token(false);

        if (is_wp_error($token)) {
            if ($token->get_error_code() != 'jwt_auth_no_auth_header') {
                /** If there is a error, store it to show it after see rest_pre_dispatch */
                $this->jwt_error = $token;
                return $user;
            } else {
                return $user;
            }
        }
        /** Everything is ok, return the user ID stored in the token*/
        return $token->data->user->id;
    }

    /**
     * Main validation function, this function try to get the Autentication
     * headers and decoded.
     *
     * @param bool $output
     *
     * @return WP_Error | Object | Array
     */
    public function validate_token($output = true)
    {
        /*
         * Looking for the HTTP_AUTHORIZATION header, if not present just
         * return the user.
         */
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

        /* Double check for different auth header string (server dependent) */
        if (!$auth) {
            $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
        }

        if (!$auth) {
            return new WP_Error(
                'jwt_auth_no_auth_header',
                'Authorization header not found.',
                array(
                    'status' => 403,
                )
            );
        }

        /*
         * The HTTP_AUTHORIZATION is present verify the format
         * if the format is wrong return the user.
         */
        list($token) = sscanf($auth, 'Bearer %s');
        if (!$token) {
            return new WP_Error(
                'jwt_auth_bad_auth_header',
                'Authorization header malformed.',
                array(
                    'status' => 403,
                )
            );
        }

        /** Get the Secret Key */
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /*if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                'JWT is not configurated properly, please contact the admin',
                array(
                    'status' => 403,
                )
            );
        }*/

        /** Try to decode the token */
        try {
            $token = JWT::decode($token, $secret_key, array('HS256'));
            /** The Token is decoded now validate the iss */
            if ($token->iss != get_bloginfo('url')) {
                /** The iss do not match, return error */
                return new WP_Error(
                    'jwt_auth_bad_iss',
                    'The iss do not match with this server',
                    array(
                        'status' => 403,
                    )
                );
            }
            /** So far so good, validate the user id in the token */
            if (!isset($token->data->user->id)) {
                /** No user id in the token, abort!! */
                return new WP_Error(
                    'jwt_auth_bad_request',
                    'User ID not found in the token',
                    array(
                        'status' => 403,
                    )
                );
            }
            /** Everything looks good return the decoded token if the $output is false */
            if (!$output) {
                return $token;
            }
            /** If the output is true return an answer to the request to show it */
            return array(
                'code' => 'jwt_auth_valid_token',
                'data' => array(
                    'status' => 200,
                ),
            );
        } catch (Exception $e) {
            /** Something is wrong trying to decode the token, send back the error */
            return new WP_Error(
                'jwt_auth_invalid_token',
                $e->getMessage(),
                array(
                    'status' => 403,
                )
            );
        }
    }

    /**
     * Filter to hook the rest_pre_dispatch, if the is an error in the request
     * send it, if there is no error just continue with the current request.
     *
     * @param $request
     */
    public function rest_pre_dispatch($request)
    {
        if (is_wp_error($this->jwt_error)) {
            return $this->jwt_error;
        }
        return $request;
    }
}
