<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 02/02/16
 * Time: 6:40 PM
 */
/*-----------------------------------------------------------------------------------*/
// Paypal functions - fave get paypal access token
/*-----------------------------------------------------------------------------------*/

if( !function_exists('homey_get_paypal_access_token') ):

    function homey_get_paypal_access_token( $url, $postArgs, $clientID=null, $SecretID=null ) {
        if(is_null($clientID) && is_null($SecretID)){
            $clientID   = homey_option('paypal_client_id');
            $SecretID   = homey_option('paypal_client_secret_key');
        }

        $curl = curl_init( $url );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $clientID . ":" . $SecretID);
        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs );
        $response = curl_exec( $curl );

        if (empty($response)) {
            die(curl_error($curl));
            curl_close($curl);
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                echo "Received error: " . $info['http_code']. "\n";
                echo "Raw response:".$response."\n";
                die();
            }
        }
        // Convert json format to PHP array
        $response = json_decode( $response );
        return $response->access_token;
    }

endif; // end

/*-----------------------------------------------------------------------------------*/
// Paypal functions - fave execute paypal request
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_execute_paypal_request') ):

    function homey_execute_paypal_request( $url, $jsonData, $access_token ) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$access_token,
            'Accept: application/json',
            'Content-Type: application/json'
        ));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        $response = curl_exec( $curl );
        if (empty($response)) {
            return true;
            die(curl_error($curl));
            curl_close($curl);
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                echo "Received error: " . $info['http_code']. "\n";
                echo "Raw response:".$response."\n";
                die();
            }
        }
        $jsonResponse = json_decode($response, TRUE);
        return $jsonResponse;
    }

endif; // end


/*-----------------------------------------------------------------------------------*/
// Paypal functions - fave execute paypal Patch
/*-----------------------------------------------------------------------------------*/
if( !function_exists('execute_paypal_request_patch') ):
function execute_paypal_request_patch( $url, $jsonData, $access_token ) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$access_token,
        'Accept: application/json',
        'Content-Type: application/json'
    ));

    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec( $curl );
    if (empty($response)) {
        return true;
        die(curl_error($curl));
        curl_close($curl);
    } else {
        $info = curl_getinfo($curl);
        curl_close($curl);
        if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
            echo "Received error: " . $info['http_code']. "\n";
            echo "Raw response:".$response."\n";
            die();
        }
    }
    $jsonResponse = json_decode($response, TRUE);
    return $jsonResponse;
}
endif;

if( !function_exists('homey_paypal_post_call') ):

    function homey_paypal_post_call($url, $post_data, $access_token) {
    
        $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'sslverify' => false,
                'blocking' => true,
                'body' =>  $post_data,
                'headers' => [
                    'Authorization' =>'Bearer '.$access_token,
                    'Accept'        =>'application/json',
                    'Content-Type'  =>'application/json'
                ],
        );
        
        $res = wp_remote_post( $url, $args ); 
      
        if ( is_wp_error( $res ) ) {
            $error_message = $res->get_error_message();
            wp_die($error_message);

        } else {
            $body = wp_remote_retrieve_body( $res );
            $json_response = json_decode( $body, true );
        }

        return $json_response;
    }
endif;

/*-----------------------------------------------------------------------------------*/
// Curl Request function - fave execute any curl request
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_execute_curl_request') ):

    function homey_execute_curl_request( $url, $data=null, $access_token=null, $is_post=true ) {

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, $is_post);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$access_token,
            'Accept: application/json',
            'Content-Type: application/json'
        ));

        if($data != null){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec( $curl );
        if (empty($response)) {
            die(curl_error($curl));
            curl_close($curl);
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                echo "Received error: " . $info['http_code']. "\n";
                echo "Raw response:".$response."\n";
                die();
            }
        }
        $jsonResponse = json_decode($response, TRUE);
        return $jsonResponse;
    }

endif; // end

if( !function_exists('get_payment_api_url') ):
    function get_payment_api_url($payment_gateway=null, $is_live=0){
        $host = 'Please insert payment method';

        if($payment_gateway == 'stripe'){
            $host = 'https://api.sandbox.stripe.com';
            // Check if stripe live
            if ($is_live == 'live') {
                $host = 'https://api.stripe.com';
            }
        }

        if($payment_gateway == 'paypal'){
            $host = 'https://api.sandbox.paypal.com';
            // Check if paypal live
            if ($is_live == 'live') {
                $host = 'https://api.paypal.com';
            }
        }
        return $host;
    }
endif; // end

if( !function_exists('homey_getMethodPaypalAccessToken') ):

    function homey_getMethodPaypalAccessToken( $url, $postArgs, $clientID=null, $SecretID=null ) {
        if(is_null($clientID) && is_null($SecretID)){
            $clientID   = homey_option('paypal_client_id');
            $SecretID   = homey_option('paypal_client_secret_key');
        }

        $curl = curl_init( $url );
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $clientID . ":" . $SecretID);
        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs );
        $response = curl_exec( $curl );

        if (empty($response)) {
            die(curl_error($curl));
            curl_close($curl);
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                echo "Received error: " . $info['http_code']. "\n";
                echo "Raw response:".$response."\n";
                die();
            }
        }
        // Convert json format to PHP array
        $response = json_decode( $response );
        return $response->access_token;
    }

endif; // end