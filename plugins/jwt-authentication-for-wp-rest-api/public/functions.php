<?php
/**
 * Homey functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Homey
 * @since Homey 1.0.0
 * @author Arif Rahim
 */


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
global $wp_version;
/**
*	---------------------------------------------------------------
*	Define constants
*	---------------------------------------------------------------
*/
/**
*	----------------------------------------------------------------------------------
*	Set up theme default and register various supported features.
*	----------------------------------------------------------------------------------
*/
if( !function_exists('homey_add_reservation_fn') ) {
    function homey_add_reservation_fn($request) {

        $admin_email = get_option( 'admin_email' ); 
        $userID       = $_POST['user_id'];
        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = $_POST['listing_id'];
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $extra_options    =  $_POST['extra_options'];
        $guest_message = stripslashes ( $_POST['guest_message'] );
        $guests   =  $_POST['guests'];
        $title = $local['reservation_text'];

        $booking_type = homey_booking_type_by_id($listing_id);
        
        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];
        return 
                array(
                    'success' => $userID,
                    'listing_id' => $listing_id,
                    'check_in_date' => $check_in_date,
                    'check_out_date' => $check_out_date
                );
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
                    'success' => $userID
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
}


if(!function_exists('homey_instance_booking_fn')) {
    function homey_instance_booking_fn() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $_POST['user_id'];//$current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $instace_page_link = homey_get_template_link_2('template/template-instance-booking.php');

        $booking_hide_fields = homey_option('booking_hide_fields');


        if ($userID === 0 ) {
            return json_encode(
                array(
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if ( empty($instace_page_link) ) {
            return json_encode(
                array(
                    'message' => $local['instance_booking_page']
                )
            );
            wp_die();
        }

        //check security
        // $nonce = $_REQUEST['security'];
        // if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {

        //     echo json_encode(
        //         array(
        //             'success' => false,
        //             'message' => $local['security_check_text']
        //         )
        //     );
        //     wp_die();
        // }

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $guest_message    =  wp_kses ( $_POST['guest_message'], $allowded_html );
        $guests   =  intval($_POST['guests']);
        $extra_options   =  $_POST['extra_options'];
        
        if($userID == $listing_owner_id) {
            return json_encode(
                array(
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }
        /*
        if(!homey_is_renter()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['host_user_cannot_book']
                )
            );
            wp_die();
        }
        */

        if(empty($guests) && $booking_hide_fields['guests'] != 1) {
            return json_encode(
                array(
                    'message' => $local['choose_guests']
                )
            );
            wp_die();

        }

        $instance_page = add_query_arg( array(
            'check_in' => $check_in_date,
            'check_out' => $check_out_date,
            'guest' => $guests,
            'extra_options' => $extra_options,
            'listing_id' => $listing_id,
            'guest_message' => $guest_message,
        ), $instace_page_link );

        return json_encode(
            array(
                'message' => __('Submitting, Please wait...', 'homey'),
                'instance_url' =>  $instance_page
            )
        );
        wp_die();
    }
}

    if(!function_exists('get_custom_period')) {
    function get_custom_period($listing_id, $actions = true ) {
        if(empty($listing_id)) {
            return;
        }

        $homey_date_format = homey_option('homey_date_format');

        if($homey_date_format == 'yy-mm-dd') {
            $h_date_format = 'Y-m-d';

        } elseif($homey_date_format == 'yy-dd-mm') {
            $h_date_format = 'Y-d-m';

        } elseif($homey_date_format == 'mm-yy-dd') {
            $h_date_format = 'm-Y-d';
            
        } elseif($homey_date_format == 'dd-yy-mm') {
            $h_date_format = 'd-Y-m';
            
        } elseif($homey_date_format == 'mm-dd-yy') {
            $h_date_format = 'm-d-Y';
            
        } elseif($homey_date_format == 'dd-mm-yy') {
            $h_date_format = 'd-m-Y';
            
        }elseif($homey_date_format == 'dd.mm.yy') {
            $h_date_format = 'd.m.Y';

        } else {
            $h_date_format = 'Y-m-d';
        }

        $output = '';
        $i = 0;
        $night_price = '';
        $weekend_price = '';
        $guest_price = '';

        $local = homey_get_localization();

        $hide_fields = homey_option('add_hide_fields');
        $custom_weekend_price = isset($hide_fields['custom_weekend_price']) ? $hide_fields['custom_weekend_price'] : 0;

        $period_array = get_post_meta($listing_id, 'homey_custom_period', true);

        if(empty($period_array)) {
            return;
        }

        if(is_array($period_array)) {
            ksort($period_array);
        } 
        $period_tabl=array();
        foreach ($period_array as $timestamp => $data) {
            $period_inarray=array();
            $is_consecutive_day = 0;
            $from_date          = new DateTime("@".$timestamp);
            $to_date            = new DateTime("@".$timestamp);
            $tomorrrow_date     = new DateTime("@".$timestamp);

            $tomorrrow_date->modify('tomorrow');
            $tomorrrow_date = $tomorrrow_date->getTimestamp();


            if ( $i == 0 ) {
                $i = 1;

            
                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $guest_price   = $data['guest_price'];

                $from_date_unix = $from_date->getTimestamp();
                $period_inarray['from_date']=$from_date->format($h_date_format);
            }

            if ( !array_key_exists ($tomorrrow_date, $period_array) ) {
                $is_consecutive_day = 1; 
                 
            } else {
                
                if( $period_array[$tomorrrow_date]['night_price']   !=  $night_price || 
                    $period_array[$tomorrrow_date]['weekend_price'] !=  $weekend_price || 
                    $period_array[$tomorrrow_date]['guest_price']   !=  $guest_price ) {
                        $is_consecutive_day = 1;
                } 
            }

            if( $is_consecutive_day == 1 ) {

                if( $i == 1 ) {
                    $from_date_unix = $from_date->getTimestamp();
                    $period_inarray['from_date']=$from_date->format($h_date_format);

                    $to_date_unix = $from_date->getTimestamp();
                    $period_inarray['to_date']=$from_date->format($h_date_format);
                   

                    $period_inarray['night_price']=homey_formatted_price($night_price, false);
                    
                    if($custom_weekend_price != 1) { 
                        $period_inarray['weekend_price']=homey_formatted_price($weekend_price, false);
                    }

                    $booking_hide_fields = homey_option('booking_hide_fields');
                    if ( $booking_hide_fields['guests'] != 1 ) {
                        $period_inarray['guest_price']= homey_formatted_price($guest_price, false);
                    } 
                }
                $i = 0;
                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $guest_price   = $data['guest_price'];

                $period_tabl[]=$period_inarray;
            }
           

        } // End foreach
        return $period_tabl;

    }
}


if(!function_exists('list_detail_fn')) {
    function list_detail_fn()
   {
   //return homey_half_map();
   global $homey_prefix, $homey_local;

   $homey_prefix = 'homey_';
   $homey_local = homey_get_localization();

   $homey_search_type = homey_search_type();

   $rental_text = $homey_local['rental_label'];

   $tax_query = array();
   $meta_query = array();
   $allowed_html = array();
   $query_ids = '';

   $cgl_meta = homey_option('cgl_meta');
   $cgl_beds = homey_option('cgl_beds');
   $cgl_baths = homey_option('cgl_baths');
   $cgl_guests = homey_option('cgl_guests');
   $cgl_types = homey_option('cgl_types');
   $price_separator = homey_option('currency_separator');

    $list_id=$_GET['list_id'];

       $listing_id = $list_id;
       $address        = get_post_meta( $listing_id, $homey_prefix.'listing_address', true );
       $bedrooms       = get_post_meta( $listing_id, $homey_prefix.'listing_bedrooms', true );
       $guests         = get_post_meta( $listing_id, $homey_prefix.'guests', true );
       $beds           = get_post_meta( $listing_id, $homey_prefix.'beds', true );
       $baths          = get_post_meta( $listing_id, $homey_prefix.'baths', true );
       $night_price          = get_post_meta( $listing_id, $homey_prefix.'night_price', true );
       $location = get_post_meta( $listing_id, $homey_prefix.'listing_location',true);
       $lat_long = explode(',', $location);

       $listing_price = homey_get_price_by_id($listing_id);

       $listing_type = wp_get_post_terms( $listing_id, 'listing_type', array("fields" => "ids") );

       if($cgl_beds != 1) {
           $bedrooms = '';
       }

       if($cgl_baths != 1) {
           $baths = '';
       }

       if($cgl_guests != 1) {
           $guests = '';
       }

       $lat = $long = '';
       if(!empty($lat_long[0])) {
           $lat = $lat_long[0];
       }

       if(!empty($lat_long[1])) {
           $long = $lat_long[1];
       }

       $listing = array();//new stdClass();
       $extra_prices = get_post_meta($listing_id, 'homey_extra_prices', true);

        $homey_booking_type = homey_booking_type();
        if($homey_booking_type == 'per_hour') {
            $per_night_text = esc_html__('Per Hour', 'homey');
            $per_nightguest_text = esc_html__('Per Hour Per Guest', 'homey');
        } else {
            $per_night_text = esc_html__('Per Night', 'homey');
            $per_nightguest_text = esc_html__('Per Night Per Guest', 'homey');
        }
      $listing['listing_id']  =$listing_id;
       $listing['title']  =get_the_title($listing_id);
       $listing['description']  =wp_strip_all_tags( get_the_content() );
       $listing['price']  =$listing_price;
       $listing['address']  = $address;
       $listing['bedrooms']  = $bedrooms;
       $listing['guests']  = $guests;
       $listing['beds']  = $beds;
       $listing['bedrooms']  = get_post_meta( $listing_id, $homey_prefix.'listing_bedrooms', true );
       $listing['baths']  = $baths;
       $listing['checkin_after']  = get_post_meta( $listing_id, $homey_prefix.'checkin_after', true );
       $listing['checkout_before']  = get_post_meta( $listing_id, $homey_prefix.'checkout_before', true );
       $listing['room_type']  =  homey_taxonomy_simple_by_ID('room_type',$listing_id);
       $listing['listing_type']  =homey_taxonomy_simple_by_ID('listing_type',$listing_id);
       
       $listing['listing_size']  = get_post_meta( $listing_id, $homey_prefix.'listing_size', true );
       $listing['listing_size_unit']  = get_post_meta( $listing_id, $homey_prefix.'listing_size_unit', true );
       //$listing['room_type']  = homey_taxonomy_simple('room_type',$listing_id);
       $listing['night_price']  = get_post_meta( $listing_id, $homey_prefix.'night_price', true );
       $listing['weekends_price']  = get_post_meta( $listing_id, $homey_prefix.'weekends_price', true );
       $listing['weekends_days']  = get_post_meta( $listing_id, $homey_prefix.'weekends_days', true );
       $listing['priceWeek']  = get_post_meta( $listing_id, $homey_prefix.'priceWeek', true );
       $listing['priceMonthly']  = get_post_meta( $listing_id, $homey_prefix.'priceMonthly', true );
       $listing['min_book_days']  = get_post_meta( $listing_id, $homey_prefix.'min_book_days', true );
       $listing['max_book_days']  = get_post_meta( $listing_id, $homey_prefix.'max_book_days', true );
       $listing['security_deposit']  = get_post_meta( $listing_id, $homey_prefix.'security_deposit', true );
       $listing['cleaning_fee']  = get_post_meta( $listing_id, $homey_prefix.'cleaning_fee', true );
       $listing['cleaning_fee_type']  = get_post_meta( $listing_id, $homey_prefix.'cleaning_fee_type', true );
       $listing['city_fee']  = get_post_meta( $listing_id, $homey_prefix.'city_fee', true );
       $listing['city_fee_type']  = get_post_meta( $listing_id, $homey_prefix.'city_fee_type', true );
       $listing['additional_guests_price']  = get_post_meta( $listing_id, $homey_prefix.'additional_guests_price', true );
       $listing['allow_additional_guests']  = get_post_meta( $listing_id, $homey_prefix.'allow_additional_guests', true );
       $extra_price=array();
       if(is_array($extra_prices)) {
        foreach($extra_prices as $key => $option) { 
            $type_text = '';
            $type = $option['type'];
            if($type == 'single_fee') {
                $type_text = esc_html__('Single Fee', 'homey'); 
            } elseif($type == 'per_night') {
                $type_text = $per_night_text;
            } elseif($type == 'per_guest') {
                $type_text = esc_html__('Per Guest', 'homey');
            } elseif($type == 'per_night_per_guest') {
                $type_text = $per_nightguest_text;
            }

            $extra_price[]  = $option;
       
        } 
    }
    $listing['extra_prices']  = $extra_price;
    $listing['accomodation'] = homey_get_listing_data_by_id('accomodation',$listing_id); 
    $size = 'homey-gallery';
    $listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $listing_id );
    $image_array=array();
    foreach($listing_images as $key => $option) { 
        $image_array[]=$option['full_url'];
    }
    $listing['gallery'] = $image_array;
    $listing['amenities']   = wp_get_post_terms( $listing_id, 'listing_amenity', array("fields" => "all"));
    $listing['facilities']  = wp_get_post_terms( $listing_id, 'listing_facility', array("fields" => "all"));
    $listing['video_url'] =homey_get_listing_data_by_id('video_url',$listing_id); 
    $listing['smoke']=homey_get_listing_data_by_id('smoke',$listing_id); 
    $listing['pets'] =homey_get_listing_data_by_id('pets',$listing_id);
    $listing['party']=homey_get_listing_data_by_id('party',$listing_id); 
    $listing['children']=homey_get_listing_data_by_id('children',$listing_id);
    $listing['additional_rules'] =homey_get_listing_data_by_id('additional_rules',$listing_id);
    $listing['cancellation_policy'] =homey_get_listing_data_by_id('cancellation_policy',$listing_id);
    $period_array=get_post_meta($listing_id, 'homey_custom_period', true);
    $period_price=array();
    $period_start=array();
    if($period_array){
    foreach($period_array as $key => $option) { 
        $period_price[]=$option;
    }
}
    $listing['custom_period']  = get_custom_period($listing_id, false); //$period_price;
    
    //    if($cgl_types != 1) {
    //        $listing['listing_type']  ='';
    //    } else {
    //        $listing['listing_type']  = homey_taxonomy_simple('listing_type');
           
    //    }
       $listing['thumbnail']  = get_the_post_thumbnail_url( $listing_id, 'homey-listing-thumb');
       $listing['url']  = get_permalink();
       $author_id = get_post_field ('post_author', $listing_id);
       $listing['author']  = get_the_author_meta( 'user_nicename',$author_id); 
       $listing['author_country']  = get_the_author_meta( 'homey_country',$author_id);
       $listing['author_language']  = get_the_author_meta( 'homey_native_language',$author_id).' '.get_the_author_meta( 'homey_other_language',$author_id); 
       $listing['author_verified']  = get_the_author_meta( 'doc_verified',$author_id); 
       $current_user = get_userdata($author_id);
       $listing['role']  = implode(" ",$current_user->roles); 
       $listing['host_rating']  =  homey_get_host_reviews(get_the_author_meta( 'ID',$author_id ))['host_rating']; 
       $picture_id = get_the_author_meta( 'homey_author_picture_id',$author_id);
       
       //$listing->avatar =get_post_meta( get_the_ID(), $homey_prefix.'_wp_attached_file');
       $img_url =wp_get_attachment_url($picture_id);
       $listing['avatar']  =$img_url;
       $listing['icon']  = get_template_directory_uri() . '/images/custom-marker.png';

       $listing['retinaIcon']  = get_template_directory_uri() . '/images/custom-marker.png';

       if(!empty($listing_type)) {
           foreach( $listing_type as $term_id ) {

               $listing['term_id']  = $term_id;

               $icon_id = get_term_meta($term_id, 'homey_marker_icon', true);
               $retinaIcon_id = get_term_meta($term_id, 'homey_marker_retina_icon', true);

               $icon = wp_get_attachment_image_src( $icon_id, 'full' );
               $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

               if( !empty($icon['0']) ) {
                   $listing['icon']  = $icon['0'];
               } 
               if( !empty($retinaIcon['0']) ) {
                   $listing['retinaIcon']  = $retinaIcon['0'];
               } 
           }
       }

   wp_reset_postdata();
   if( count($listing) > 0 ) {
      // $output['post']=$listings; 
       return  $listing;
      /* echo json_encode( array( 'getListings' => false, 'listings' => $listings, 'total_results' => $total_listings.' '.$rental_text, 'listingHtml' => $listings_html ) );*/
       exit();
   } else {
       //echo json_encode( array( 'getListings' => false, 'total_results' => $total_listings.' '.$rental_text ) );
       exit();
   }
   die();

   }

}


if(!function_exists('homey_half_map_fn')) {
     function homey_half_map_fn($request)
    {
    //return homey_half_map();
    global $homey_prefix, $homey_local;

    $homey_prefix = 'homey_';
    $homey_local = homey_get_localization();

    $homey_search_type = homey_search_type();

    $rental_text = $homey_local['rental_label'];

    $tax_query = array();
    $meta_query = array();
    $allowed_html = array();
    $query_ids = '';

    $cgl_meta = homey_option('cgl_meta');
    $cgl_beds = homey_option('cgl_beds');
    $cgl_baths = homey_option('cgl_baths');
    $cgl_guests = homey_option('cgl_guests');
    $cgl_types = homey_option('cgl_types');
    $price_separator = homey_option('currency_separator');

    $arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
    $depart = isset($_POST['depart']) ? $_POST['depart'] : '';
    $guests = isset($_POST['guest']) ? $_POST['guest'] : '';
    $pets = isset($_POST['pets']) ? $_POST['pets'] : -1;
    $bedrooms = isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '';
    $rooms = isset($_POST['rooms']) ? $_POST['rooms'] : '';
    $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : '';
    $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : '';
    $room_size = isset($_POST['room_size']) ? $_POST['room_size'] : '';
    $search_country = isset($_POST['search_country']) ? $_POST['search_country'] : '';
    $search_city = isset($_POST['search_city']) ? $_POST['search_city'] : '';
    $search_area = isset($_POST['search_area']) ? $_POST['search_area'] : '';
    $listing_type = isset($_POST['listing_type']) ? $_POST['listing_type'] : '';
    $search_lat = isset($_POST['search_lat']) ? $_POST['search_lat'] : '';
    $search_lng = isset($_POST['search_lng']) ? $_POST['search_lng'] : '';
    $search_radius = isset($_POST['radius']) ? $_POST['radius'] : 20;

    $paged = isset($_POST['paged']) ? ($_POST['paged']) : '';
    $sort_by = isset($_POST['sort_by']) ? ($_POST['sort_by']) : '';
    $layout = isset($_POST['layout']) ? ($_POST['layout']) : 'list';
    $num_posts = isset($_POST['num_posts']) ? ($_POST['num_posts']) : '9';

    $country = isset($_POST['country']) ? $_POST['country'] : '';
    $state = isset($_POST['state']) ? $_POST['state'] : '';
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $area = isset($_POST['area']) ? $_POST['area'] : '';
    $booking_type = isset($_POST['booking_type']) ? $_POST['booking_type'] : '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

    $arrive = homey_search_date_format($arrive);
    $depart = homey_search_date_format($depart);

    $beds_baths_rooms_search = homey_option('beds_baths_rooms_search');
    $search_criteria = '=';
    if( $beds_baths_rooms_search == 'greater') {
        $search_criteria = '>=';
    } elseif ($beds_baths_rooms_search == 'lessthen') {
        $search_criteria = '<=';
    }

    if( !empty($booking_type) ) {
        $homey_search_type = $booking_type;
    }



    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => $num_posts,
        'post_status' => 'publish',
        'paged' => $paged,
    );

    $keyword = trim($keyword);
    if (!empty($keyword)) {
        $query_args['s'] = $keyword;
    }
    
    if( !empty( $_POST["optimized_loading"] ) ) {
        $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
        $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
        $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
        $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

        $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
    }
    

    if( homey_option('enable_radius') ) {
        if($homey_search_type == 'per_hour') {
            $available_listings_ids = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
        } else {
            $available_listings_ids = apply_filters('homey_check_search_availability_filter', $query_args, $arrive, $depart);
        }

        $radius_ids = apply_filters('homey_radius_filter', $query_args, $search_lat, $search_lng, $search_radius);

        if(!empty($available_listings_ids) && !empty($radius_ids)) {
            $query_ids =  array_intersect($available_listings_ids, $radius_ids);

            if(empty($query_ids)) {
                $query_ids = array(0);
            }

        } elseif(empty($available_listings_ids)) {
            $query_ids = $radius_ids;

        } elseif(empty($radius_ids)) {
            $query_ids = $available_listings_ids;
        }

        if(!empty($query_ids)) {
            $query_args['post__in'] = $query_ids;
        }
    } else {

        if($homey_search_type == 'per_hour') {
            $query_args = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
        } else {
            $query_args = apply_filters('homey_check_search_availability_filter', $query_args, $arrive, $depart);
        }

        if(!empty($search_city) || !empty($search_area)) {
            $_tax_query = Array();

            if(!empty($search_city) && !empty($search_area)) {
                $_tax_query['relation'] = 'AND';
            }

            if(!empty($search_city)) {
                $_tax_query[] = array(
                    'taxonomy' => 'listing_city',
                    'field' => 'slug',
                    'terms' => $search_city
                );
            }

            if(!empty($search_area)) {
                $_tax_query[] = array(
                    'taxonomy' => 'listing_area',
                    'field' => 'slug',
                    'terms' => $search_area
                );
            }

            $tax_query[] = $_tax_query;
        }

        if(!empty($search_country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => homey_traverse_comma_string($search_country)
            );
        }

    }


    if(!empty($listing_type)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_type',
            'field' => 'slug',
            'terms' => homey_traverse_comma_string($listing_type)
        );
    }

    if(!empty($country)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_country',
            'field' => 'slug',
            'terms' => $country
        );
    }

    if(!empty($state)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_state',
            'field' => 'slug',
            'terms' => $state
        );
    }

    if(!empty($city)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_city',
            'field' => 'slug',
            'terms' => $city
        );
    }

    if(!empty($area)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_area',
            'field' => 'slug',
            'terms' => $area
        );
    }

    // min and max price logic
    if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any' && isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
        $min_price = doubleval(homey_clean($_POST['min-price']));
        $max_price = doubleval(homey_clean($_POST['max-price']));

        if ($min_price > 0 && $max_price > $min_price) {
            $meta_query[] = array(
                'key' => 'homey_night_price',
                'value' => array($min_price, $max_price),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }
    } else if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any') {
        $min_price = doubleval(homey_clean($_POST['min-price']));
        if ($min_price > 0) {
            $meta_query[] = array(
                'key' => 'homey_night_price',
                'value' => $min_price,
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }
    } else if (isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
        $max_price = doubleval(homey_clean($_POST['max-price']));
        if ($max_price > 0) {
            $meta_query[] = array(
                'key' => 'homey_night_price',
                'value' => $max_price,
                'type' => 'NUMERIC',
                'compare' => '<=',
            );
        }
    }

    if(!empty($guests)) {
        $meta_query[] = array(
            'key' => 'homey_total_guests_plus_additional_guests',
            'value' => intval($guests),
            'type' => 'NUMERIC',
            'compare' => $search_criteria,
        );
    }

    //because this is boolean, no other option other than yes or no
    //$pets = $pets == '' ? 1 : $pets;
    //if(!empty($pets) && $pets != '0') {
    if(!empty($pets) && $pets != -1) {
        $meta_query[] = array(
            'key' => 'homey_pets',
            'value' => $pets,
            'type' => 'NUMERIC',
            'compare' => '=',
        );
    }
    if (!empty($bedrooms)) {
        $bedrooms = sanitize_text_field($bedrooms);
        $meta_query[] = array(
            'key' => 'homey_listing_bedrooms',
            'value' => $bedrooms,
            'type' => 'CHAR',
            'compare' => $search_criteria,
        );
    }

    if (!empty($rooms)) {
        $rooms = sanitize_text_field($rooms);
        $meta_query[] = array(
            'key' => 'homey_listing_rooms',
            'value' => $rooms,
            'type' => 'CHAR',
            'compare' => $search_criteria,
        );
    }

    if( !empty($booking_type) ) {
        $meta_query[] = array(
            'key'     => 'homey_booking_type',
            'value'   => $booking_type,
            'compare' => '=',
            'type'    => 'CHAR'
        );
    }

    if (isset($_POST['area']) && !empty($_POST['area'])) {
        if (is_array($_POST['area'])) {
            $areas = $_POST['area'];

            foreach ($areas as $area):
                $tax_query[] = array(
                    'taxonomy' => 'listing_area',
                    'field' => 'slug',
                    'terms' => homey_traverse_comma_string($area)
                );
            endforeach;
        }
    }

    if (isset($_POST['amenity']) && !empty($_POST['amenity'])) {
        if (is_array($_POST['amenity'])) {
            $amenities = $_POST['amenity'];

            foreach ($amenities as $amenity):
                $tax_query[] = array(
                    'taxonomy' => 'listing_amenity',
                    'field' => 'slug',
                    'terms' => $amenity
                );
            endforeach;
        }
    }

    if (isset($_POST['facility']) && !empty($_POST['facility'])) {
        if (is_array($_POST['facility'])) {
            $facilities = $_POST['facility'];

            foreach ($facilities as $facility):
                $tax_query[] = array(
                    'taxonomy' => 'listing_facility',
                    'field' => 'slug',
                    'terms' => $facility
                );
            endforeach;
        }
    }
    
    if(!empty($room_size)) {
        $tax_query[] = array(
            'taxonomy' => 'room_type',
            'field' => 'slug',
            'terms' => homey_traverse_comma_string($room_size)
        );
    }

    if ( $sort_by == 'a_price' ) {
        $query_args['orderby'] = 'meta_value_num';
        $query_args['meta_key'] = 'homey_night_price';
        $query_args['order'] = 'ASC';
    } else if ( $sort_by == 'd_price' ) {
        $query_args['orderby'] = 'meta_value_num';
        $query_args['meta_key'] = 'homey_night_price';
        $query_args['order'] = 'DESC';
    } else if ( $sort_by == 'a_rating' ) {
        $query_args['orderby'] = 'meta_value_num';
        $query_args['meta_key'] = 'listing_total_rating';
        $query_args['order'] = 'ASC';
    } else if ( $sort_by == 'd_rating' ) {
        $query_args['orderby'] = 'meta_value_num';
        $query_args['meta_key'] = 'listing_total_rating';
        $query_args['order'] = 'DESC';
    } else if ( $sort_by == 'featured' ) {
        $query_args['meta_key'] = 'homey_featured';
        $query_args['meta_value'] = '1';
    } else if ( $sort_by == 'a_date' ) {
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'ASC';
    } else if ( $sort_by == 'd_date' ) {
        $query_args['orderby'] = 'date';
        $query_args['order'] = 'DESC';
    } else if ( $sort_by == 'featured_top' ) {
        $query_args['orderby'] = 'meta_value date';
        $query_args['meta_key'] = 'homey_featured';
        $query_args['order'] = 'DESC';
    }

    $meta_count = count($meta_query);

    if( $meta_count > 1 ) {
        $meta_query['relation'] = 'AND';
    }
    if( $meta_count > 0 ){
        $query_args['meta_query'] = $meta_query;
    }

    $tax_count = count( $tax_query );

    if( $tax_count > 1 ) {
        $tax_query['relation'] = 'AND';
    }
    if( $tax_count > 0 ){
        $query_args['tax_query'] = $tax_query;
    }

    $query_args = new WP_Query( $query_args );

   

    ob_start();

    $total_listings = $query_args->found_posts;

    if($total_listings > 1) {
        $rental_text = $homey_local['rentals_label'];
    }

    $id= array();
    $title= array();
    $lat= array();
    $long= array();
    $price= array();
    $address= array();
    $bedrooms= array();
    $guests= array();
    $beds= array();
    $baths= array();
    $listing_type= array();
    $thumbnail= array();
    $url= array();
    $author= array();
    $avatar= array();
    $icon= array();
    $retinaIcon= array();
    $term_id= array();
    $icon= array();
    $retinaIcon= array();
    $listings = array();
    while( $query_args->have_posts() ): $query_args->the_post();

        $listing_id = get_the_ID();
        $address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
        $bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
        $guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
        $beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
        $baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
        $night_price          = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
        $location = get_post_meta( get_the_ID(), $homey_prefix.'listing_location',true);
        $lat_long = explode(',', $location);

        $listing_price = homey_get_price_by_id($listing_id);

        $listing_type = wp_get_post_terms( get_the_ID(), 'listing_type', array("fields" => "ids") );

        if($cgl_beds != 1) {
            $bedrooms = '';
        }

        if($cgl_baths != 1) {
            $baths = '';
        }

        if($cgl_guests != 1) {
            $guests = '';
        }

        $lat = $long = '';
        if(!empty($lat_long[0])) {
            $lat = $lat_long[0];
        }

        if(!empty($lat_long[1])) {
            $long = $lat_long[1];
        }

        $listing = array();//new stdClass();

        array_push( $id, $listing_id );
        array_push( $title, get_the_title() );
        array_push( $lat, $lat );
        array_push( $long,  $long );
        $listing['id']  =$listing_id;
        $listing['title']  =get_the_title();
        $listing['price']  =$listing_price;
        $listing['address']  = $address;
        $listing['bedrooms']  = $bedrooms;
        $listing['guests']  = $guests;
        $listing['beds']  = $beds;
        $listing['baths']  = $baths;
        $listing['featured']   = get_post_meta( get_the_ID(), 'homey_featured', true );
        
        if($cgl_types != 1) {
            $listing['listing_type']  = '';
        } else {
            $listing['listing_type']  = homey_taxonomy_simple('listing_type');
        }
        $listing['thumbnail']  = get_the_post_thumbnail_url( $listing_id, 'homey-listing-thumb');
        $listing['url']  = get_permalink();
        $listing['author']  = get_the_author_meta( 'user_nicename'); 
        $picture_id = get_the_author_meta( 'homey_author_picture_id');
        
        //$listing->avatar =get_post_meta( get_the_ID(), $homey_prefix.'_wp_attached_file');
        $img_url =wp_get_attachment_url($picture_id);
        $listing['avatar']  =$img_url;
        $listing['icon']  = get_template_directory_uri() . '/images/custom-marker.png';

        $listing['retinaIcon']  = get_template_directory_uri() . '/images/custom-marker.png';

        if(!empty($listing_type)) {
            foreach( $listing_type as $term_id ) {

                $listing['term_id']  = $term_id;

                $icon_id = get_term_meta($term_id, 'homey_marker_icon', true);
                $retinaIcon_id = get_term_meta($term_id, 'homey_marker_retina_icon', true);

                $icon = wp_get_attachment_image_src( $icon_id, 'full' );
                $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

                if( !empty($icon['0']) ) {
                    $listing['icon']  = $icon['0'];
                } 
                if( !empty($retinaIcon['0']) ) {
                    $listing['retinaIcon']  = $retinaIcon['0'];
                } 
            }
        }

        //array_push($listings, $listing);

        if($layout == 'card') {
            get_template_part('template-parts/listing/listing-card');
        } else {
            get_template_part('template-parts/listing/listing-item');
        }
        $listings[]=$listing;
    endwhile;

    wp_reset_postdata();

    homey_pagination_halfmap( $query_args->max_num_pages, $paged, $range = 2 );
    
    $listings_html = ob_get_contents();
    ob_end_clean();
    $output = array();
    if( count($listings) > 0 ) {
       // $output['post']=$listings; 
        return  $listings;
       /* echo json_encode( array( 'getListings' => false, 'listings' => $listings, 'total_results' => $total_listings.' '.$rental_text, 'listingHtml' => $listings_html ) );*/
        exit();
    } else {
        //echo json_encode( array( 'getListings' => false, 'total_results' => $total_listings.' '.$rental_text ) );
        exit();
    }
    die();

    }

}
if(!function_exists('search_availability')) {
     function search_availability($request)
    {
        $output = '';
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $booking_proceed = true;

        $listing_id = intval($request->get_param('listing_id'));
        $check_in_date     =  wp_kses ($request->get_param('check_in_date'), $allowded_html );
        $check_out_date    =  wp_kses ( $request->get_param('check_out_date'), $allowded_html );
        //return $check_in_date;
        $booking_type = homey_booking_type_by_id( $listing_id );

        if($booking_type != "per_day_date" && strtotime($check_out_date) <= strtotime($check_in_date)) {
            return json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_book_proceed']
                )
            );
            wp_die();
        }

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);
        if($booking_type == "per_day_date"){ $days_count += 1; }

        if( $booking_type == 'per_week' ) {

            $min_book_weeks = get_post_meta($listing_id, 'homey_min_book_weeks', true);
            $max_book_weeks = get_post_meta($listing_id, 'homey_max_book_weeks', true);

            $total_weeks_count = $days_count / 7;

            if($total_weeks_count < $min_book_weeks) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['min_book_weeks_error'].' '.$min_book_weeks
                    );
                wp_die();
            }

            if(($total_weeks_count > $max_book_weeks) && !empty($max_book_weeks)) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['max_book_weeks_error'].' '.$max_book_weeks
                    );
                wp_die();
            }

        } else if( $booking_type == 'per_month' ) {

            $min_book_months = get_post_meta($listing_id, 'homey_min_book_months', true);
            $max_book_months = get_post_meta($listing_id, 'homey_max_book_months', true);

            $total_months_count = $days_count / 30;

            if($total_months_count < $min_book_months) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['min_book_months_error'].' '.$min_book_months
                    );
                wp_die();
            }

            if(($total_months_count > $max_book_months) && !empty($max_book_months)) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['max_book_months_error'].' '.$max_book_months
                    );
                wp_die();
            }

        } else if( $booking_type == 'per_day_date' ) { // per day
            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['min_book_day_dates_error'].' '.$min_book_days
                    );
                wp_die();
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['max_book_day_dates_error'].' '.$max_book_days
                    );
                wp_die();
            }
        } else { // Per Night 

            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['min_book_days_error'].' '.$min_book_days
                    );
                wp_die();
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                return 
                    array(
                        'success' => false,
                        'message' => $local['max_book_days_error'].' '.$max_book_days
                    );
                wp_die();
            }
        }

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);

        if($booking_type != "per_day_date"){
            $check_out->modify('yesterday');
        }

        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {

            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {

                return 
                    array(
                        'success' => false,
                        'message' => $local['dates_not_available']
                    );
                wp_die();

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }
        $reservation_id = array(
            "listing_id" =>$request->get_param('listing_id'),
            "check_in_date" =>$request->get_param('check_in_date'),
            "check_out_date" =>$request->get_param('check_out_date'),
            "guests" =>$request->get_param('guests') ? $request->get_param('guests') :  '0',
            "extra_options" =>$request->get_param('extra_options') ? $request->get_param('extra_options') :  '',
          );
        
        if(empty($reservation_id)) {
          return 
            array(
                'success' => false,
                'message' => "Some thing went wrong!"
            );
        wp_die();
        }
        $extra_options = intval( $reservation_id['extra_options']);

        $listing_id     = intval($reservation_id['listing_id']);
        $check_in_date  = wp_kses ( $reservation_id['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_id['check_out_date'], $allowded_html );
        $guests         = intval($reservation_id['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        $with_weekend_label = $local['with_weekend_label'];
        
        $array1= $prices_array;
        $array2= array( 'success' => true ,'message' => $local['dates_available']);
        $d = array(
            "booking_cost" => $array1,
            "booking_check" => $array2
          );
        $result = $d;
        return $result;
         
         
        wp_die();
    
                       
    }
}

if(!function_exists('messages')) {
 function messages()
    {
    global $current_user, $wpdb, $userID, $homey_threads;
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'DESC';
    $messages_page = homey_get_template_link('template/dashboard-messages.php');
    $mine_messages_link = add_query_arg( 'mine', '1', $messages_page );

    wp_get_current_user();
    $userID =$_GET['user_id']; //$current_user->ID;

    $tabel = $wpdb->prefix . 'homey_threads';
    //pagination related meta data
    $items_per_page = isset( $_GET['per_page'] ) ? abs( (int) $_GET['per_page'] ) : 25;
    $items_per_page = isset($_GET['per_page'])  ? abs( (int) $_GET['per_page'] ) : 25;
    $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $offset = ( $page * $items_per_page ) - $items_per_page;
    // end pagination related meta data

    if(api_is_admin($userID)) {
        if($_GET['mine']) {
            $total_query = $wpdb->prepare(
                "
                SELECT COUNT(sender_id) as total_results
                FROM $tabel 
                WHERE sender_id = %d OR receiver_id = %d
                ORDER BY id ".$sort."
                 LIMIT %d, %d
                ",
                $userID,
                $userID,
                $offset,
                $items_per_page
            );

            $message_query = $wpdb->prepare(
                "
                SELECT * 
                FROM $tabel 
                WHERE sender_id = %d OR receiver_id = %d
                ORDER BY id ".$sort."
                LIMIT %d, %d
                ",
                $userID,
                $userID,
                $offset,
                $items_per_page
            );
        } else {
            $total_query = 'SELECT COUNT(sender_id) as total_results
            FROM '.$tabel.' 
            ORDER BY id '.$sort;

            $message_query = 'SELECT * 
            FROM '.$tabel.' 
            ORDER BY id '.$sort.' LIMIT '.$offset.', '.$items_per_page;
        }

    } else {
        $total_query = $wpdb->prepare(
            "
            SELECT COUNT(sender_id) as total_results
            FROM $tabel 
            WHERE sender_id = %d OR receiver_id = %d
            ORDER BY id ".$sort."
            ",
            $userID,
            $userID
        );

        $message_query = $wpdb->prepare(
            "
            SELECT * 
            FROM $tabel 
            WHERE sender_id = %d OR receiver_id = %d
            ORDER BY id ".$sort." LIMIT %d, %d
            ",
            $userID,
            $userID,
            $offset,
            $items_per_page
        );
    }

    $total_result = $wpdb->get_results( $total_query );
    $total_pages = absint((isset($total_result[0]->total_results)?$total_result[0]->total_results:1)/$items_per_page); 
    $homey_threads = $wpdb->get_results( $message_query );
    $messaes = array();

    if ( sizeof( $homey_threads ) != 0 ) :
        foreach ( $homey_threads as $thread ) {
    $messae = array();
        $sender_id = $thread->sender_id;
        $receiver_id = $thread->receiver_id;

        $delete = 0;
        if($userID == $sender_id) {
            $delete = $thread->sender_delete;
        } elseif($userID == $receiver_id) {
            $delete = $thread->receiver_delete;
        } else {
            if($thread->sender_delete && $thread->receiver_delete) {
                $delete = 1;
            }

        }

        $user_can_reply = false;
        if($sender_id == $userID || $receiver_id == $userID || api_is_admin($userID)) {
            $user_can_reply = true;
        }

        if($delete != 1) {

        $thread_class = 'msg-unread new-message';
        $tabel = $wpdb->prefix . 'homey_thread_messages';
        $thread_id = $thread->id;


        $homey_sql = $wpdb->prepare(
            "
                SELECT * 
                FROM $tabel 
                WHERE thread_id = %d
                ORDER BY id " .$sort,
            $thread_id
        );

        $last_message = $wpdb->get_row($homey_sql);

        // $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $sender_id );
        $user_for_photo_id = $sender_id;
        if($sender_id == $userID){
            // $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $receiver_id );
            $user_for_photo_id = $receiver_id;
        }
        // $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

        $homey_current_user_info = homey_get_author_by_id('60', '60', 'img-circle', $user_for_photo_id);
        $user_custom_picture = $homey_current_user_info['photo'];

        if( empty($user_custom_picture) ) {
            $user_custom_picture = get_template_directory_uri().'/images/profile-avatar.png';
        }

        if($user_can_reply) {
            $url_query = array( 'thread_id' => $thread_id, 'seen' => true );
        } else {
            $url_query = array( 'thread_id' => $thread_id);
        }

        if ( $last_message->created_by == $userID || $thread->seen ) {
            $thread_class = '';
            unset( $url_query['seen'] );
        }

        $thread_link = homey_get_template_link_2('template/dashboard-messages.php');
        $thread_link = add_query_arg( $url_query, $thread_link );

        $sender_first_name  =  get_the_author_meta( 'first_name', $sender_id );
        $sender_last_name  =  get_the_author_meta( 'last_name', $sender_id );
        $sender_display_name = get_the_author_meta( 'display_name', $sender_id );

        if($sender_id == $userID){
            $sender_first_name  =  get_the_author_meta( 'first_name', $receiver_id );
            $sender_last_name  =  get_the_author_meta( 'last_name', $receiver_id );
            $sender_display_name = get_the_author_meta( 'display_name', $receiver_id );
        }

        if( !empty($sender_first_name) && !empty($sender_last_name) ) {
            $sender_display_name = $sender_first_name.' '.$sender_last_name;
        }

        $last_sender_first_name  =  get_the_author_meta( 'first_name', $last_message->created_by );
        $last_sender_last_name  =  get_the_author_meta( 'last_name', $last_message->created_by );
        $last_sender_display_name = get_the_author_meta( 'display_name', $last_message->created_by );
        if( !empty($last_sender_first_name) && !empty($last_sender_last_name) ) {
            $last_sender_display_name = $last_sender_first_name.' '.$last_sender_last_name;
        }
            $messae['thread_id'] =intval($thread_id);
            $messae['sender_id'] = $sender_id;
            $messae['last_message_id'] = $last_message->id;

            $messae['custom_picture'] = $user_custom_picture;
            $messae['display_name'] = ucfirst( $sender_display_name ); 
            $messae['message_time'] = date_i18n( homey_convert_date(homey_option('homey_date_format')).' '.get_option('time_format'), strtotime( $last_message->time ) );
            $messae['display_name'] = esc_attr($last_sender_display_name);
            $messae['message'] = str_replace("\\", "", html_entity_decode($last_message->message)); 
            $messaea[]=$messae;
            }
    }       
    return $messaea;

     endif;


    }
}    


if(!function_exists('invoices')) {
 function invoices()
    {

    global $paged, $homey_local, $current_user, $dashboard_invoices;

        wp_get_current_user();
        $userID         =$_GET['user_id'];
        $user_login     = $current_user->user_login;
        $dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');

        $is_detail = false;

        if( isset( $_GET['invoice_id']) && !empty($_GET['invoice_id']) ) {
            $is_detail = true;
        }

        if ( is_front_page()  ) {
            $paged = (get_query_var('page')) ? get_query_var('page') : 1;
        }

        $invoices_content = '';

        if( ! isset( $_GET['invoice_id']) ) {
            
            $meta_query = array();
            $date_query = array();
            if( isset($_GET['invoice_status']) &&  $_GET['invoice_status'] !='' ){
        $temp_array = array();
        $temp_array['key'] = 'invoice_payment_status';
        $temp_array['value'] = sanitize_text_field( $_GET['invoice_status'] );
        $temp_array['compare'] = '=';
        $temp_array['type'] = 'NUMERIC';
        $meta_query[] = $temp_array;
    }

    if( isset($_GET['invoice_type']) &&  $_GET['invoice_type'] !='' ){
        $temp_array = array();
        $temp_array['key'] = 'homey_invoice_for';
        $temp_array['value'] = sanitize_text_field( $_GET['invoice_type'] );
        $temp_array['compare'] = 'LIKE';
        $temp_array['type'] = 'CHAR';
        $meta_query[] = $temp_array;
    }

    if( isset($_GET['startDate']) &&  $_GET['startDate'] !='' ){
        $temp_array = array();
        $temp_array['after'] = sanitize_text_field( $_GET['startDate'] );
        $date_query[] = $temp_array;
    }

    if( isset($_GET['endDate']) &&  $_GET['endDate'] !='' ){
        $temp_array = array();
        $temp_array['before'] = sanitize_text_field( $_GET['endDate'] );
        $date_query[] = $temp_array;
    }

            $invoices_args = array(
                'post_type' => 'homey_invoice',
                'posts_per_page' => -1,
                'paged' => $paged,
                'date_query' => $date_query
            );

            if(api_is_renter($userID)) {
                $meta_query[] = array(
                    'key' => 'homey_invoice_buyer',
                    'value' => $userID,
                    'compare' => '='
                );
            } else {
                if(!api_is_admin($userID)){
                    $meta_query[] = array(
                        'key' => 'homey_invoice_buyer',
                        'value' => $userID,
                        'compare' => '='
                    );
                    $meta_query[] = array(
                        'key' => 'invoice_resv_owner',
                        'value' => $userID,
                        'compare' => '='
                    );
                    $meta_query['relation'] = 'OR';
                }
            }

            $invoices_args['meta_query'] = $meta_query;

            $invoice_query = new WP_Query($invoices_args);
            $total = 0;
            
        }$invoices = array();
        if( ! isset( $_GET['invoice_id']) ) {

            if ($invoice_query->have_posts()) :
            while ($invoice_query->have_posts()) : $invoice_query->the_post();
            $invoice = array();
            $invoice_data = homey_get_invoice_meta( get_the_ID() );
            $invoice_meta = homey_get_invoice_meta(get_the_ID());
            $user_info = get_userdata($invoice_data['invoice_buyer_id']);
            $invoice_detail = add_query_arg( 'invoice_id', get_the_ID(), $dashboard_invoices );

            $invoice['ID']=  get_the_ID();

            $wc_reference_order_id = get_post_meta( get_the_ID(), 'wc_reference_order_id', true); 
            $invoice['reference_id']=  $wc_reference_order_id > 0 ? 'wc#'.$wc_reference_order_id : '';
            $reservation_page_link ="";
            $wc_reservation_reference_id = get_post_meta( get_the_ID(), 'wc_reservation_reference_id', true); 
            $detail_link = '';
            if($wc_reservation_reference_id > 0) {
                $detail_link = add_query_arg( 'reservation_detail', $wc_reservation_reference_id, $reservation_page_link );
            }
            $invoice['reference_id']=   $wc_reservation_reference_id > 0 ? '<a href="' .$detail_link.'" title="Reservation">resvr#'.$wc_reservation_reference_id.'</a>' : ''; 
            $wc_order_id = get_wc_order_id(get_the_ID()); if($wc_order_id > 0) 
            $invoice['order_id']= 'wc#'.$wc_order_id; 
            $invoice['homey_date']=  get_the_date(homey_convert_date(homey_option('homey_date_format')));
             
             if($invoice_data['invoice_billion_for'] == 'reservation') {
            $invoice['fee_text']=  esc_attr($homey_local['resv_fee_text']);

            } elseif($invoice_data['invoice_billion_for'] == 'listing') {

                if( $invoice_data['upgrade'] == 1 ) {
            $invoice['upgrade']=  esc_attr($homey_local['upgrade_text']);

                } else {
            $invoice['upgrade']=  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
                }
            } elseif($invoice_data['invoice_billion_for'] == 'upgrade_featured') {
            $invoice['billion_for']=  esc_attr($homey_local['upgrade_text']);
                    
            } elseif($invoice_data['invoice_billion_for'] == 'package') {
            $invoice['billion_for']=  esc_attr($homey_local['inv_package']);
            }

            $invoice['billing_type']= esc_html_e( $invoice_data['invoice_billing_type'], 'homey' );

            $invoice_status = get_post_meta(  get_the_ID(), 'invoice_payment_status', true );
            if( $invoice_status == 0 ) {
            $invoice['invoice_status']='<span class="label label-warning">'.esc_attr($homey_local['not_paid']).'</span>';
            } else {
            $invoice['invoice_status']='<span class="label label-success">'.esc_attr($homey_local['paid']).'</span>';
            }
            $invoice['payment_method']= esc_html__($invoice_data['invoice_payment_method'], 'homey');
            $reservation_meta = get_post_meta($invoice_data['invoice_item_id'], 'reservation_meta', true);
  
        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($invoice_data['invoice_item_id'], 'listing_renter', true);

        if( api_is_host($userID) && $homey_invoice_buyer != $userID ) {
            $is_host = true;
        }

        if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }

            $extra_expenses = homey_get_extra_expenses($invoice_data['invoice_item_id']);
        $extra_discount = homey_get_extra_discount($invoice_data['invoice_item_id']);

        if($is_host && !empty($services_fee)) {
            $upfront_payment = $upfront_payment - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
        }

        $invoice['upfront_payment']= homey_formatted_price( $upfront_payment );

            $total += $invoice_meta['invoice_item_price'] > 0 ?  $invoice_meta['invoice_item_price'] : 0;
            $invoices[]=$invoice;
            endwhile;
           return $invoices;
             endif;
            wp_reset_postdata();
                      }

    }
}    


if(!function_exists('invoices_detail')) {
 function invoices_detail()
    {
    global $homey_local, $dashboard_invoices, $current_user;
    wp_get_current_user();
    $userID         = $_GET['user_id'];//$current_user->ID;
    $user_login     = $current_user->user_login;
    $user_address = get_user_meta( $userID, 'homey_street_address', true);

    $invoice_id = $_GET['invoice_id'];
    $post = get_post( $invoice_id );
    $invoice_data = homey_get_invoice_meta( $invoice_id );
    $invoice_item_id = $invoice_data['invoice_item_id'];

    $publish_date = $post->post_date;
    $publish_date = date_i18n( homey_convert_date(homey_option('homey_date_format')), strtotime( $publish_date ) );
    $invoice_logo = homey_option( 'invoice_logo', false, 'url' );
    $invoice_company_name = homey_option( 'invoice_company_name' );
    $invoice_additional_info = homey_option( 'invoice_additional_info' );

    $homey_invoice_buyer = get_post_meta( $invoice_id, 'homey_invoice_buyer', true );

    $user_info = get_userdata($homey_invoice_buyer);
    $user_phone = get_user_meta( $homey_invoice_buyer, 'phone', true);

    $user_email     = isset($user_info->user_email)?$user_info->user_email:'-';
    $first_name     = isset($user_info->first_name)?$user_info->first_name:'-';
    $last_name      = isset($user_info->last_name)?$user_info->last_name:'-';

    if( !empty($first_name) && !empty($last_name) ) {
        $fullname = $first_name.' '.$last_name;
    } else {
        $fullname = $user_info->display_name;
    }

    $is_reservation_invoice = false;
    if($invoice_data['invoice_billion_for'] == 'reservation') {
        $is_reservation_invoice = true;
    }

    if($invoice_data['invoice_billion_for'] == 'reservation') {
                
        $billing_for_text = $homey_local['resv_fee_text'];

    } elseif($invoice_data['invoice_billion_for'] == 'listing') {
        if( $invoice_data['upgrade'] == 1 ) {
            $billing_for_text =  $homey_local['upgrade_text'];

        } else {
            $billing_for_text =  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
        }
    } elseif($invoice_data['invoice_billion_for'] == 'upgrade_featured') {
            $billing_for_text =  $homey_local['upgrade_text'];
            
    } elseif($invoice_data['invoice_billion_for'] == 'package') {
        $billing_for_text =  $homey_local['inv_package'];
    }
            $detail = array();
      $logged_in_user = get_current_user_id();

    $detail['invoice_logo'] = esc_url($invoice_logo);
    $detail['invoice_company_name'] =  esc_attr($invoice_company_name);
    $detail['invoice_address'] =  homey_option( 'invoice_address' );
    $detail['invoice_id'] =  esc_attr($invoice_id);
    $detail['publish_date'] =  esc_attr($publish_date);
    $detail['invoice_item_id'] =  esc_attr($invoice_item_id);
    $detail['fullname'] =  esc_attr($fullname); 
    $detail['user_email'] =  esc_attr($user_email);
    $detail['user_phone'] =  esc_attr($user_phone);
    if($is_reservation_invoice) { 
        $resv_id = $invoice_item_id;
        $is_hourly = get_post_meta($resv_id, 'is_hourly', true);
        if($is_hourly == 'yes') {
    $detail['h_reservation_cost'] =homey_calculate_hourly_reservation_cost($resv_id);
        } else {
    $detail['reservation_cost'] = homey_calculate_reservation_cost($resv_id); 
        }
        
    } else {
      
    $detail['billing_for'] = $homey_local['billing_for'].' '.$billing_for_text;
    $detail['billing_type'] = $homey_local['billing_type'];
    $detail['inv_pay_method'] = $homey_local['inv_pay_method'];
        $price_is_zero = homey_formatted_price( $invoice_data['invoice_item_price'] );
    $detail['inv_total'] = $homey_local['inv_total'].''.$price_is_zero != '' ? $price_is_zero : "0";
    $detail['invoice_item_price'] = '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$invoice_data['invoice_item_price'].'">';
    }
    if( !empty($invoice_additional_info)) { 
    $detail['additional_info'] = homey_option( 'invoice_additional_info' );  } 
     
    return  $detail;


    }
}    



if(!function_exists('destinations')) {
 function destinations()
    {

    $atts="";
    extract(shortcode_atts(array(
        'homey_grid_type' => 'grid_v1',
        'homey_grid_from' => 'listing_city',
        'homey_show_child' => '0',
        'orderby'           => 'name',
        'order'             => 'ASC',
        'homey_hide_empty' => '1',
        'no_of_terms'       => '',
        'listing_type' => '',
        'room_type' => '',
        'listing_area' => '',
        'listing_state' => '',
        'listing_city' => '',
        'listing_country' => ''
    ), $atts));

    ob_start();
    $module_type = '';
    $homey_local = homey_get_localization();

    $slugs = '';

    if( $homey_grid_from == 'listing_city' ) {
        $slugs = $listing_city;

    } else {
        $slugs = $listing_type;
    }

    if ($homey_show_child == 1) {
        $homey_show_child = '';
    }

    if ($homey_grid_type == 'grid_v1') {
        $taxonomy_grid_class = 'taxonomy-grid-1';
    } else {
        $taxonomy_grid_class = 'taxonomy-grid-1';
    }

    $custom_link_for = '';

    $tax_name = $homey_grid_from;
    $taxonomy = get_terms(array(
        'hide_empty' => $homey_hide_empty,
        'parent' => $homey_show_child,
        'slug' => homey_traverse_comma_string($slugs),
        'number' => $no_of_terms,
        'orderby' => $orderby,
        'order' => $order,
        'taxonomy' => $tax_name,
    ));
   // return $taxonomy;
    $cityName = array();
            if ( !is_wp_error( $taxonomy ) ) {
                $i = 0;
                $j = 0;

                foreach ($taxonomy as $term) {
                 $city= array();
                $attach_id = get_term_meta($term->term_id, 'homey_taxonomy_img', true);
                    $attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_555_360' );

                    if(empty($attachment)) {
                        $img_url = 'https://place-hold.it/555x360';
                        $img_width = '555';
                        $img_height = '360';
                    }else{
                        $img_url = $attachment['0'];
                        $img_width = $attachment['1'];
                        $img_height = $attachment['2'];
                    }
                $taxonomy_custom_link = '';//get_tax_meta($term->term_id, $custom_link_for);

                if( !empty($taxonomy_custom_link) ) {
                    $term_link = $taxonomy_custom_link;
                } else {
                    $term_link = get_term_link($term, $tax_name);
                }
                    //esc_url($term_link);
                    $city['name']= esc_attr($term->name); 
                    $city['url']= esc_url($img_url);
                    $city['id']= $term->term_id; 
                       
                    if ($homey_grid_type == 'grid_v2' && $i == 3) { 
                 }
                  $cityName[]=$city;
                }
            }

           
    $result = ob_get_contents();
    ob_end_clean();
   
    return $cityName;


    }
}   


if(!function_exists('comfort_place')) {
 function comfort_place()
    {

        $atts="";
        extract(shortcode_atts(array(
            'homey_grid_type' => 'grid_v2',
            'homey_grid_from' => 'listing_type',
            'homey_show_child' => '0',
            'orderby'           => 'name',
            'order'             => 'ASC',
            'homey_hide_empty' => '1',
            'no_of_terms'       => '',
            'listing_type' => '',
            'room_type' => '',
            'listing_area' => '',
            'listing_state' => '',
            'listing_city' => '',
            'listing_country' => ''
        ), $atts));

        ob_start();
        $module_type = '';
        $homey_local = homey_get_localization();

        $slugs = '';

        if( $homey_grid_from == 'listing_city' ) {
            $slugs = $listing_city;

        } else {
            $slugs = $listing_type;
        }
        if($homey_grid_type == 'grid_v2') {
            $taxonomy_grid_class = 'taxonomy-grid-2';
        } else {
            $taxonomy_grid_class = 'taxonomy-grid-1';
        }

        $custom_link_for = '';

        $tax_name = $homey_grid_from;
        $taxonomy = get_terms(array(
            'hide_empty' => $homey_hide_empty,
            'parent' => $homey_show_child,
            'slug' => homey_traverse_comma_string($slugs),
            'number' => $no_of_terms,
            'orderby' => $orderby,
            'order' => $order,
            'taxonomy' => $tax_name,
        ));
       // return $taxonomy;
        $cityName = array();
                if ( !is_wp_error( $taxonomy ) ) {
                    $i = 0;
                    $j = 0;

                    foreach ($taxonomy as $term) {
                     $city= array();
                    $i++;
                    $j++;

                    $attach_id = get_term_meta($term->term_id, 'homey_taxonomy_img', true);
                    
                    $attachment = wp_get_attachment_image_src( $attach_id, 'homey_thumb_360_360' );

                    if(empty($attachment)) {
                        $img_url = 'https://place-hold.it/360x360';
                        $img_width = '360';
                        $img_height = '360';
                    }else{
                        $img_url = $attachment['0'];
                        $img_width = $attachment['1'];
                        $img_height = $attachment['2'];
                    }
                    $taxonomy_custom_link = '';//get_tax_meta($term->term_id, $custom_link_for);

                    if( !empty($taxonomy_custom_link) ) {
                        $term_link = $taxonomy_custom_link;
                    } else {
                        $term_link = get_term_link($term, $tax_name);
                    }
                        //esc_url($term_link);
                        $city['name']= esc_attr($term->name); 
                        $city['url']= esc_url($img_url);
                        $city['id']= $term->term_id; 
                           
                        if ($homey_grid_type == 'grid_v2' && $i == 3) { 
                     }
                      $cityName[]=$city;
                    }
                }

               
        $result = ob_get_contents();
        ob_end_clean();
       
        return $cityName;

   
    }
}

if(!function_exists('rservation')) {
 function rservation($request)
    {
            $userID =   $request->get_param('user_id');
            $meta_query = array(); 
            $booking_hide_fields = homey_option('booking_hide_fields');

            //$total_reservations = homey_posts_count('homey_reservation');


            $listing_no   =  '9';
            $paged        = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type'        =>  'homey_reservation',
                'paged'             => $paged,
                'posts_per_page'    => $listing_no,
            );

            if(isset($_GET['post_status'])){
                $meta_query[] = array(
                    'key' => 'reservation_status',
                    'value' => $_GET['post_status'],
                    'compare' => '='
                );

            }

            if( api_is_renter($userID) ) {
                $meta_query[] = array(
                    'key' => 'listing_renter',
                    'value' => $userID,
                    'compare' => '='
                );
                $args['meta_query'] = $meta_query;
            } else {
                if(api_is_admin($userID)) {
                    if(isset($_GET['mine']) && $_GET['mine'] == 1) {
                        $meta_query[] = array(
                            'key' => 'listing_owner',
                            'value' => $userID,
                            'compare' => '='
                        );
                    } else {
                        $meta_query[] = array(
                            'key' => 'listing_owner',
                        );
                    }
                } else {
                    $meta_query[] = array(
                        'key' => 'listing_owner',
                        'value' => $userID,
                        'compare' => '='
                    );
                }
                $args['meta_query'] = $meta_query;
            }
            $listings = array();
            $res_query = new WP_Query($args);

                            if( $res_query->have_posts() ):
        while ($res_query->have_posts()): $res_query->the_post(); 
                $listing = array();
                $is_hourly = get_post_meta(get_the_ID(), 'is_hourly', true);

                                
            global $homey_prefix, $homey_local;
            $renter_id = get_post_meta(get_the_ID(), 'listing_renter', true);
            $listing_author = api_get_author_by_id('40', '40', 'img-circle media-object avatar', $renter_id);


            $check_in = get_post_meta(get_the_ID(), 'reservation_checkin_date', true);
            $check_out = get_post_meta(get_the_ID(), 'reservation_checkout_date', true);
            $reservation_guests = get_post_meta(get_the_ID(), 'reservation_guests', true);
            $listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
            $listing_address    = get_post_meta( $listing_id, $homey_prefix.'listing_address', true );
            $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
            $deposit = get_post_meta(get_the_ID(), 'reservation_upfront', true);
            $total_amount = get_post_meta(get_the_ID(), 'reservation_total', true);
            $reservation_status = get_post_meta(get_the_ID(), 'reservation_status', true);

            if(api_is_renter($userID)) {
                $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
            } else {

                if(!listing_guest(get_the_ID(),$userID)) {
                    $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
                } else {
                    $reservation_page_link = homey_get_template_link('template/dashboard-reservations2.php');
                }
            }

            $detail_link = add_query_arg( 'reservation_detail', get_the_ID(), $reservation_page_link );

            $no_upfront = homey_option('reservation_payment');
            $booking_hide_fields = homey_option('booking_hide_fields');

            if($no_upfront == 'no_upfront') {
                $price = '';
            } else {
                $price = $deposit;
            }

            if( empty($price) ) {
                $price = $total_amount;
            }

            if($pets != 1) {
                $pets_allow = $homey_local['text_no'];
            } else {
                $pets_allow = $homey_local['text_yes'];
            }
        if(!empty($listing_author['photo'])) { 
            $listing['link']= esc_url($listing_author['link']); 
            $listing['photo']= $listing_author['photo'];
        } 
        $listing['id']= get_the_ID();
            $wc_order_id = get_wc_order_id(get_the_ID()); 
            if($wc_order_id > 0) $listing['order_id']= $wc_order_id;
            $listing['status']= $reservation_status;
            $listing['date']=get_the_date();
            $listing['time']=get_the_time();
            $listing['permalink']= get_permalink($listing_id); 
            $listing['title']= get_the_title($listing_id);
            if(!empty($listing_address)) {
                $listing['address']= esc_attr($listing_address);
            }
            $listing['checkin']= homey_format_date_simple($check_in); 
            $listing['checkout']= homey_format_date_simple($check_out);
            if($booking_hide_fields['guests'] != 1 && 0 !=  homey_option('cgl_guests')) {
                $listing['guest']= esc_attr($reservation_guests);
            } 
            $listing['pets_allow']= esc_attr($pets_allow); 
            $listing['price']= homey_formatted_price($price);
            if( listing_guest(get_the_ID(),$userID) ) {
                if($reservation_status == 'available') {
                    $listing['detail_link']= esc_url($detail_link);
                    $listing['paynow_label']= $homey_local['res_paynow_label'];
                } else {
                    $listing['detail_link']= esc_url($detail_link);
                    $listing['details_label']= $homey_local['res_details_label'];
                }
            } else { 
                if($reservation_status == 'under_review') {
                    $listing['detail_link']= esc_url($detail_link);
                    $listing['confirm_label']= $homey_local['res_confirm_label'];
                } else {
                    $listing['detail_link']= esc_url($detail_link);
                    $homey_local['res_details_label'];
                }
            }
            $listings[]=$listing;
                endwhile;
                    return $listings;
                else: 
                    
                    $listing['not_found']= esc_attr($homey_local['reservation_not_found']);
            endif; 
            return $listings;
    }
}

if(!function_exists('give_access')) {
    function give_access($reservationID,$user_id) {
        global $current_user;
        $current_user = wp_get_current_user();
        //$user_id = $current_user->ID; 

        $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);

        if( ( $user_id == $listing_owner_id ) || ( $user_id == $listing_renter_id ) || api_is_admin($user_id) ) {
            return true;
        }

        return false;
    }
}

if(!function_exists('give_access')) {
    function give_access($reservationID,$user_id) {
        global $current_user;
        $current_user = wp_get_current_user();
        //$user_id = $current_user->ID; 

        $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);

        if( ( $user_id == $listing_owner_id ) || ( $user_id == $listing_renter_id ) || api_is_admin($user_id) ) {
            return true;
        }

        return false;
    }
}

if(!function_exists('listing_guest')) {
    function listing_guest($reservationID,$user_id) {
        global $current_user;
        $current_user = wp_get_current_user();
        //$user_id = $current_user->ID;

        $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);

        if( ($user_id == $listing_renter_id) || api_is_renter($user_id)) {
            return true;
        } 

        return false;
    }
}

if( !function_exists('api_is_renter') ) {
    function api_is_renter($user_id = null) {
        global $current_user;
        $current_user = wp_get_current_user();

        if(!empty($user_id)) {
            $current_user = get_userdata($user_id);
        }

        if (in_array('homey_renter', (array)$current_user->roles) || in_array('subscriber', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}

if( !function_exists('api_is_host') ) {
    function api_is_host($user_id = null) {
        global $current_user;
        $current_user = wp_get_current_user();
        if(!empty($user_id)) {
            $current_user = get_userdata($user_id);
        }
        
        if (in_array('homey_host', (array)$current_user->roles) || in_array('author', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}

if( !function_exists('api_is_admin') ) {
    function api_is_admin($user_id = null) {
        global $current_user;
        $current_user = wp_get_current_user();
        
        if(!empty($user_id)) {
            $current_user = get_userdata($user_id);
        }
        
        if (in_array('administrator', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}


if(!function_exists('decline_reservation_fn')) {
    function decline_reservation_fn() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];
                
        if( $listing_owner != $userID && !api_is_admin($userID) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_owner_text']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'declined');
        update_post_meta($reservation_id, 'res_decline_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id, true);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'declined_reservation', $email_args );
        $admin_email = get_option( 'admin_email' );
        homey_email_composer( $admin_email, 'declined_reservation', $email_args );
        wp_die();
    }
}
if(!function_exists('confirm_reservation_fn')) {
    function confirm_reservation_fn() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $no_upfront = homey_option('reservation_payment');

        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $reservation_id = intval($_POST['reservation_id']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if( $listing_owner != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('not_owner')
                )
            );
            wp_die();
        }

        // If no upfront option select then book at this step
        if($no_upfront == 'no_upfront') {

            if($is_hourly =='yes') {
                homey_hourly_booking_with_no_upfront($reservation_id);
            } else {
                homey_booking_with_no_upfront($reservation_id);
            }

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('booked')
                )
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date );

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('available')
                )
            );

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'confirm_reservation', $email_args );
            $admin_email = get_option( 'admin_email' );
            homey_email_composer( $admin_email, 'confirm_reservation', $email_args );
        }

        wp_die();
    }
}
 function rservation_detail($request)
{
            global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id;
            $blogInfo = esc_url( home_url('/') );
            //wp_get_current_user();
            $Data=array();
            $userID =   $_GET['user_id'];//$current_user->ID;

            $booking_hide_fields = homey_option('booking_hide_fields');

            $reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
            $reservation_status = $notification = $status_label = $notification = '';
            $upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
            $payment_link = '';
            if(!empty($reservationID)) {

                $post = get_post($reservationID);    
                $current_date = date( 'Y-m-d', current_time( 'timestamp', 0 ));
                $current_date_unix = strtotime($current_date );

                $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
                $total_price = get_post_meta($reservationID, 'reservation_total', true);
                $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
                $upfront_payment = homey_formatted_price($upfront_payment);
                $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

                $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
                $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
                $guests = get_post_meta($reservationID, 'reservation_guests', true);
                $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
                $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
                $res_meta   = get_post_meta($reservationID, 'reservation_meta', true);

                $booking_type = homey_booking_type_by_id($listing_id);

                $extra_expenses = homey_get_extra_expenses($reservationID);
                $extra_discount = homey_get_extra_discount($reservationID);
                
                if(!empty($extra_expenses)) {
                    $expenses_total_price = $extra_expenses['expenses_total_price'];
                    $total_price = $total_price + $expenses_total_price;
                }

                if(!empty($extra_discount)) {
                    $discount_total_price = $extra_discount['discount_total_price'];
                    $total_price = $total_price - $discount_total_price;
                }

                if(homey_option('reservation_payment') == 'full') {
                    $upfront_payment = homey_formatted_price($total_price); 
                }

                $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

                $renter_id = get_post_meta($reservationID, 'listing_renter', true);
                $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

                $owner_id = get_post_meta($reservationID, 'listing_owner', true);
                $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

                $payment_link = add_query_arg( array(
                        'reservation_id' => $reservationID,
                    ), $payment_link );

                $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);
                

                $guests_label = homey_option('cmn_guest_label');
                if($guests > 1) {
                    $guests_label = homey_option('cmn_guests_label');
                }

            }
            $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
            
            $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);
            //return $listing_owner_id;
            if( ( $userID == $listing_owner_id ) || ( $userID == $listing_renter_id ) || api_is_admin($userID) ) {
                 
                     //homey_reservation_notification($reservation_status, $reservationID); 
                     $Data['reservation_label']= esc_attr($homey_local['reservation_label']); 
                     $wc_order_id = get_wc_order_id(get_the_ID()); $wc_order_id_txt = $wc_order_id > 0 ? ', wc#'.$wc_order_id.' ' : ' ';
                     $Data['reservation_status']= $reservation_status;
                     
                     if($reservation_status == 'booked') {}
                      if($reservation_status != 'booked' && $reservation_status != 'cancelled' && $reservation_status != 'declined' && !listing_guest($reservationID,$userID)) {
                       }  if(!listing_guest($reservationID,$userID)) {  }

                        if($res_meta['no_of_days'] > 1) {
                            $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_dates_label') : homey_option('glc_day_nights_label');
                        } else {
                            $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_date_label') : homey_option('glc_day_night_label');
                        }

                        $no_of_weeks = isset($res_meta['total_weeks_count']) ? $res_meta['total_weeks_count'] : 0;
                        $no_of_months = isset($res_meta['total_months_count']) ? $res_meta['total_months_count'] : 0;

                        if($no_of_weeks > 1) {
                            $week_label = homey_option('glc_weeks_label');
                        } else {
                            $week_label = homey_option('glc_week_label');
                        }

                        if($no_of_months > 1) {
                            $month_label = homey_option('glc_months_label');
                        } else {
                            $month_label = homey_option('glc_month_label');
                        }
                        
                        $Data['reservationID']= $reservationID;
                        $Data['date_label']= esc_attr($homey_local['date_label']); 
                        $Data['month_names']= translate_month_names(esc_attr( get_the_date( get_option( 'date_format' ), $reservationID )));
                        $Data['the_date']= esc_attr( get_the_date( homey_time_format(), $reservationID ));
                        if(!empty($renter_info['photo'])) {
                        $Data['renter_photo']=  api_get_author_by_id('40', '40', 'img-circle media-object avatar', $renter_id)['photo']; }
                        $Data['renter_name']= esc_attr($renter_info['name']); 
                        $Data['title']= get_the_title($listing_id);
                        $Data['check_In']= esc_attr($homey_local['check_In']);
                        $Data['check_in_simple']= homey_format_date_simple($check_in);
                        $Data['check_Out']= esc_attr($homey_local['check_Out']); 
                        $Data['check_out_simple']= homey_format_date_simple($check_out);
                        if( $booking_type == 'per_week' ) { 
                        $Data['week_label']= esc_attr($week_label);
                        $Data['no_of_weeks']= esc_attr($no_of_weeks);
                        if( $res_meta['no_of_days'] > 0 ) { 
                        $Data['no_of_days']= esc_html__('and', 'homey').' '.esc_attr($res_meta['no_of_days']).' '.esc_attr($night_label); 
                                                }} 
                        else if( $booking_type == 'per_month' ) {
                        $Data['month_label']= esc_attr($month_label); 
                        $Data['no_of_months']= esc_attr($no_of_months);
                        if( $res_meta['no_of_days'] > 0 ) { 
                        $Data['no_of_days']= esc_html__('and', 'homey').' '.esc_attr($res_meta['no_of_days']).' '.esc_attr($night_label); 
                                                }} 
                        else if( $booking_type == 'per_day_date' ) {
                        $Data['night_label']= esc_attr($night_label); 
                        $Data['no_of_days']= esc_attr($res_meta['no_of_days']); } 
                        else {  $Data['night_label']= esc_attr($night_label);
                        $Data['no_of_days']= esc_attr($res_meta['no_of_days']); }
                        if($booking_hide_fields['guests'] != 1) {
                        $Data['guests_label']= esc_attr($guests_label); 
                        $Data['guests']= esc_attr($guests); } 
                        if(!empty($res_meta['additional_guests'])) { 
                        $Data['addinal_guest_text']= esc_attr($homey_local['addinal_guest_text']);
                        $Data['additional_guests']= esc_attr($res_meta['additional_guests']); } 
                        if(!empty($renter_msg)) { 
                        $Data['renter_msg']= esc_attr($renter_msg); }
                        $Data['payment_label']= esc_attr($homey_local['payment_label']); 
                        if($booking_type == 'per_day_date'){
                        $Data['cost_day']= calculate_reservation_cost_day_date($reservationID, $userID);
                    }
                        else{$Data['cost_day']= calculate_reservation_cost($reservationID, $userID);}
                        //homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width');
                        $Data['upfront_payment']=  $upfront_payment; 
                        return $Data;
     get_template_part('template-parts/dashboard/reservation/payment-sidebar', '', array("booking_type", $booking_type)); 

    homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); }
}

if( !function_exists('calculate_reservation_cost') ) { 
    function calculate_reservation_cost($reservation_id, $userID, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $booking_type = homey_booking_type_by_id($listing_id);

        if( $booking_type == 'per_week' ) {
            return calculate_reservation_cost_weekly($reservation_id, $userID, $collapse);
        } else if( $booking_type == 'per_month' ) {
            return calculate_reservation_cost_monthly($reservation_id, $userID, $collapse);
        } else if( $booking_type == 'per_day_date' ) {
            return calculate_reservation_cost_day_date($reservation_id, $userID, $collapse);
        } else {
            return calculate_reservation_cost_nightly($reservation_id, $userID, $collapse);
        }
    }
}

if( !function_exists('calculate_reservation_cost_monthly') ) {
    function calculate_reservation_cost_monthly($reservation_id, $userID, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_month = homey_formatted_price($reservation_meta['price_per_month'], true);
        $no_of_months = $reservation_meta['total_months_count'];
        $no_of_days = $reservation_meta['no_of_days'];
        $months_total_price = homey_formatted_price($reservation_meta['months_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];

        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_months > 1) {
            $month_label = homey_option('glc_months_label');
        } else {
            $month_label = homey_option('glc_month_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( api_is_host($userID) && $homey_invoice_buyer != $userID ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
//            $balance = $balance + $expenses_total_price; //just to exclude from payment to local
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
//            $balance = $balance - $discount_total_price; //just to exclude from payment to local
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_month).' x '.esc_attr($no_of_months).' '.esc_attr($month_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$months_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('calculate_reservation_cost_weekly') ) {
    function calculate_reservation_cost_weekly($reservation_id, $userID, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_week = homey_formatted_price($reservation_meta['price_per_week'], true);
        $no_of_days = $reservation_meta['no_of_days'];
        $no_of_weeks = $reservation_meta['total_weeks_count'];

        $weeks_total_price = homey_formatted_price($reservation_meta['weeks_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];

        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_weeks_label');
        } else {
            $week_label = homey_option('glc_week_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( api_is_host($userID) && $homey_invoice_buyer !=  $userID ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_week).' x '.esc_attr($no_of_weeks).' '.esc_attr($week_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$weeks_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('calculate_reservation_cost_nightly') ) {
    function calculate_reservation_cost_nightly($reservation_id, $userID, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = array();

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $check_in_date  = wp_kses ( isset($reservation_meta['check_in_date'])?$reservation_meta['check_in_date']:'', $allowded_html );
        $check_out_date = wp_kses ( isset($reservation_meta['check_out_date'])?$reservation_meta['check_out_date']:'', $allowded_html );
        $guests         = intval(isset($reservation_meta['guests'])?$reservation_meta['guests']:0);


        $price_per_night = homey_formatted_price(isset($reservation_meta['price_per_night'])?$reservation_meta['price_per_night']:0, true);
        $no_of_days = isset($reservation_meta['no_of_days'])?$reservation_meta['no_of_days']:0;

        $nights_total_price = homey_formatted_price(isset($reservation_meta['nights_total_price'])?$reservation_meta['nights_total_price']:0, false);

        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee'])?$reservation_meta['cleaning_fee']:0);
        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        $taxes = isset($reservation_meta['taxes'])?$reservation_meta['taxes']:0;
        $taxes_percent = isset($reservation_meta['taxes_percent'])?$reservation_meta['taxes_percent']:0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee'])?$reservation_meta['city_fee']:0);
        $security_deposit = isset($reservation_meta['security_deposit'])?$reservation_meta['security_deposit']:0;
        $additional_guests = isset($reservation_meta['additional_guests'])?$reservation_meta['additional_guests']:0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price'])?$reservation_meta['additional_guests_price']:0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price'])?$reservation_meta['additional_guests_total_price']:0;

        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $balance = isset($reservation_meta['balance'])?$reservation_meta['balance']:0;
        $total_price = isset($reservation_meta['total'])?$reservation_meta['total']:0;

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend'])?$reservation_meta['booking_has_weekend']:0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing'])?$reservation_meta['booking_has_custom_pricing']:0;
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( api_is_host($userID) && $homey_invoice_buyer != $userID ) {
            $is_host = true;
        }
        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        if($collapse) {
            $output ["cs_total"]= $local['cs_total'];
            $output ["cs_tax_fees"]= $local['cs_tax_fees'];
            $output ["total_price"]= homey_formatted_price($total_price);
            $output ["cs_view_details"]= $local['cs_view_details'];
        }
        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output ["no_of_days"]=$no_of_days;
            $output ["custom_period"]=$local['with_custom_period_and_weekend_label'];
            $output ["nights_total_price"]=$nights_total_price;

        } elseif($booking_has_weekend == 1) {
            $output ["no_of_days"]= $no_of_days;
            $output ["with_weekend_label"]= $with_weekend_label;
            $output ["nights_total_price"]= $nights_total_price;

        } elseif($booking_has_custom_pricing == 1) {
            $output ["no_of_days"]= $no_of_days;
            $output ["custom_period_label"]=$local['with_custom_period_label'];
            $output ["nights_total_price"]=$nights_total_price;

        } else {
            $output ["price_per_night"]= $price_per_night;
            $output ["no_of_days"]=$no_of_days;
            $output ["nights_total_price"]=$nights_total_price;
        }

        if(!empty($additional_guests)) {
            $output ["additional_guests"]=$additional_guests;
            $output ["add_guest_label"]=$add_guest_label;
            $output ["guests_total_price"]=homey_formatted_price($additional_guests_total_price);
        }

        if(isset($reservation_meta['cleaning_fee'])){
            if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output ["cs_cleaning_fee"]=$local['cs_cleaning_fee'];
                $output ["cleaning_fee"]=$cleaning_fee;
            }
        }

        if(!empty($extra_prices)) {
            $output ["extra_prices"]= $extra_prices['extra_html'];
        }

        if(isset($reservation_meta['city_fee'])){
            if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output ["cs_city_fee"]= $local['cs_city_fee'];
                $output ["city_fee"]=$city_fee;
            }
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output ["cs_sec_deposit"]= $local['cs_sec_deposit'];
            $output ["security_deposit"]=homey_formatted_price($security_deposit);
        }


        if(!empty($services_fee) && !$is_host) {
            $output ["cs_services_fee"]= $local['cs_services_fee'];
            $output ["services_fee"]=homey_formatted_price($services_fee);
        }

        if(!empty($extra_expenses)) {
            $output ["extra_expenses"]= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output ["extra_discount"]= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output ["cs_taxes"]= $local['cs_taxes'];
            $output ["taxes_percent"]=$taxes_percent;
            $output ["taxes"]=homey_formatted_price($taxes);
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output ["inv_total"]=$local['inv_total'];$output ["upfront_price"]=homey_formatted_price($upfront_payment);
            $output ["upfront_payment"]=$upfront_payment;

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output ["cs_payment_due"]=$local['cs_payment_due'];$output ["formatted_price"]=homey_formatted_price($upfront_payment);
                $output ["upfront_payment"]=$upfront_payment;
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output ["cs_pay_rest_1"]=$local['cs_pay_rest_1'];$output ["balance"]=homey_formatted_price($balance);
            $output ["cs_pay_rest_2"]=$local['cs_pay_rest_2'];
        }
        return $output;
    }
}

if( !function_exists('calculate_reservation_cost_day_date') ) {
    function calculate_reservation_cost_day_date($reservation_id, $userID, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $check_in_date  = wp_kses ( isset($reservation_meta['check_in_date'])?$reservation_meta['check_in_date']:'', $allowded_html );
        $check_out_date = wp_kses ( isset($reservation_meta['check_out_date'])?$reservation_meta['check_out_date']:'', $allowded_html );
        $guests         = intval(isset($reservation_meta['guests'])?$reservation_meta['guests']:0);


        $price_per_day_date = homey_formatted_price(isset($reservation_meta['price_per_day_date'])?$reservation_meta['price_per_day_date']:0, true);
        $no_of_days = isset($reservation_meta['no_of_days'])?$reservation_meta['no_of_days']:0;

        $days_total_price = homey_formatted_price(isset($reservation_meta['days_total_price'])?$reservation_meta['days_total_price']:0, false);

        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee'])?$reservation_meta['cleaning_fee']:0);
        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        $taxes = isset($reservation_meta['taxes'])?$reservation_meta['taxes']:0;
        $taxes_percent = isset($reservation_meta['taxes_percent'])?$reservation_meta['taxes_percent']:0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee'])?$reservation_meta['city_fee']:0);
        $security_deposit = isset($reservation_meta['security_deposit'])?$reservation_meta['security_deposit']:0;
        $additional_guests = isset($reservation_meta['additional_guests'])?$reservation_meta['additional_guests']:0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price'])?$reservation_meta['additional_guests_price']:0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price'])?$reservation_meta['additional_guests_total_price']:0;

        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $balance = isset($reservation_meta['balance'])?$reservation_meta['balance']:0;
        $total_price = isset($reservation_meta['total'])?$reservation_meta['total']:0;

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend'])?$reservation_meta['booking_has_weekend']:0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing'])?$reservation_meta['booking_has_custom_pricing']:0;
       $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_dates_label');
        } else {
            $night_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( api_is_host($userID) && $homey_invoice_buyer != $userID ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$days_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$days_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$days_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_day_date.' x '.$no_of_days.' '.$night_label.' <span>'.$days_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(isset($reservation_meta['cleaning_fee'])){
            if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
            }
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(isset($reservation_meta['city_fee'])){
            if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
            }
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('api_get_author_by_id') ) {
    function api_get_author_by_id($w = '36', $h = '36', $classes = 'img-responsive img-circle', $ID) {
        
        global $homey_local;
        $author = array();
        $prefix = 'homey_';
        $comma = ' ';
        $maximumPoints = 100;
        $point = 0;

        $author['is_photo'] = false;
        $author['is_email'] = false;

        $custom_img = get_template_directory_uri().'/images/avatar.png';

        $author_picture_id = get_the_author_meta( 'homey_author_picture_id' , $ID );

        $doc_verified = get_the_author_meta( 'doc_verified' , $ID);
        $author[ 'id' ] = $ID;
        $author[ 'name' ] = get_the_author_meta( 'display_name' , $ID );
        $author[ 'email' ] = get_the_author_meta( 'email', $ID );
        $author[ 'bio' ] = get_the_author_meta( 'description' , $ID );

        if( !empty( $author_picture_id ) ) {
            $point+=30;

            $author_picture_id = intval( $author_picture_id );
            if ( $author_picture_id ) {

                $photo = wp_get_attachment_url( $author_picture_id);
                
                if(!empty($photo)) {
                    $author[ 'photo' ] = $photo;
                } else {
                    $author[ 'photo' ] = esc_url($custom_img);
                }

                $author['is_photo'] = true;
            }
        } else {
            $author[ 'photo' ] = esc_url($custom_img);
        }

        //counting listings with statues
       // $author[ 'all_listing_count' ]          = homey_hm_user_listing_count($ID);
        //$author[ 'publish_listing_count' ]      = homey_hm_user_publish_listing_count($ID);
       // $author[ 'all_featured_listing_count' ] = homey_featured_listing_count($ID);

        $native_language  = get_the_author_meta( $prefix.'native_language' , $ID );
        $other_language  =  get_the_author_meta( $prefix.'other_language' , $ID );
        if(!empty($other_language) && !empty($native_language)) {
            $comma = ', ';
        }

        $author['facebook']     =  get_the_author_meta( 'homey_author_facebook' , $ID );
        $author['twitter']      =  get_the_author_meta( 'homey_author_twitter' , $ID );
        $author['linkedin']     =  get_the_author_meta( 'homey_author_linkedin' , $ID );
        $author['pinterest']    =  get_the_author_meta( 'homey_author_pinterest' , $ID );
        $author['instagram']    =  get_the_author_meta( 'homey_author_instagram' , $ID );
        $author['googleplus']   =  get_the_author_meta( 'homey_author_googleplus' , $ID );
        $author['youtube']      =  get_the_author_meta( 'homey_author_youtube' , $ID );
        $author['vimeo']        =  get_the_author_meta( 'homey_author_vimeo' , $ID );
        $author[ 'link' ] = get_author_posts_url( $ID );
        $author[ 'address' ] = get_the_author_meta( $prefix.'street_address' , $ID );
        $author[ 'country' ] = get_the_author_meta( $prefix.'country' , $ID);
        $author[ 'state' ] = get_the_author_meta( $prefix.'state' , $ID);
        $author[ 'city' ] = get_the_author_meta( $prefix.'city' , $ID);
        $author[ 'area' ] = get_the_author_meta( $prefix.'area' , $ID);
        $author[ 'is_superhost' ] = get_the_author_meta( 'is_superhost' , $ID);
        $author[ 'doc_verified' ] = $doc_verified;
        $author[ 'user_document_id' ] = get_the_author_meta( 'homey_user_document_id' , $ID );
        $author[ 'doc_verified_request' ] = get_the_author_meta( 'id_doc_verified_request' , $ID );
        $author[ 'native_language' ] = $native_language;
        $author[ 'other_language' ] = $other_language;
        $author[ 'total_earnings' ] = homey_get_host_total_earnings($ID);
        $author[ 'available_balance' ] = homey_get_host_available_earnings($ID);
        $author[ 'languages' ] = esc_attr($native_language.$comma.$other_language);

        // Emergency Contact 
        $author[ 'em_contact_name' ] = get_the_author_meta( $prefix.'em_contact_name' , $ID);
        $author[ 'em_relationship' ] = get_the_author_meta( $prefix.'em_relationship' , $ID);
        $author[ 'em_email' ] = get_the_author_meta( $prefix.'em_email' , $ID);
        $author[ 'em_phone' ] = get_the_author_meta( $prefix.'em_phone' , $ID);

        $author[ 'payout_payment_method' ] = get_the_author_meta( 'payout_payment_method' , $ID);
        $author[ 'payout_paypal_email' ] = get_the_author_meta( 'payout_paypal_email' , $ID);
        $author[ 'payout_skrill_email' ] = get_the_author_meta( 'payout_skrill_email' , $ID);

        // Beneficiary Information
        $author[ 'ben_first_name' ] = get_the_author_meta( 'ben_first_name' , $ID);
        $author[ 'ben_last_name' ] = get_the_author_meta( 'ben_last_name' , $ID);
        $author[ 'ben_company_name' ] = get_the_author_meta( 'ben_company_name' , $ID);
        $author[ 'ben_tax_number' ] = get_the_author_meta( 'ben_tax_number' , $ID);
        $author[ 'ben_street_address' ] = get_the_author_meta( 'ben_street_address' , $ID);
        $author[ 'ben_apt_suit' ] = get_the_author_meta( 'ben_apt_suit' , $ID);
        $author[ 'ben_city' ] = get_the_author_meta( 'ben_city' , $ID);
        $author[ 'ben_state' ] = get_the_author_meta( 'ben_state' , $ID);
        $author[ 'ben_zip_code' ] = get_the_author_meta( 'ben_zip_code' , $ID);

        //Wire Transfer Information
        $author[ 'bank_account' ] = get_the_author_meta( 'bank_account' , $ID);
        $author[ 'swift' ] = get_the_author_meta( 'swift' , $ID);
        $author[ 'bank_name' ] = get_the_author_meta( 'bank_name' , $ID);
        $author[ 'wir_street_address' ] = get_the_author_meta( 'wir_street_address' , $ID);
        $author[ 'wir_aptsuit' ] = get_the_author_meta( 'wir_aptsuit' , $ID);
        $author[ 'wir_city' ] = get_the_author_meta( 'wir_city' , $ID);
        $author[ 'wir_state' ] = get_the_author_meta( 'wir_state' , $ID);
        $author[ 'wir_zip_code' ] = get_the_author_meta( 'wir_zip_code' , $ID);


        if(!empty($author[ 'email' ])) {
            $point+=30;

            $author['is_email'] = true;
        }

        if($doc_verified) {
            $point+=40;
        }

        $percentage = ($point*$maximumPoints)/100;
        $author[ 'profile_status' ] = $percentage."%";
        $author[ 'is_email_verified' ] = get_the_author_meta( 'is_email_verified', $ID );

        return $author;
    }
}
?>