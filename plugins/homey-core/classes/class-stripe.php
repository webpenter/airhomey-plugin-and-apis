<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Stripe {
    
        

        private $userID;
        private $user_email;
        private $display_name;
        private $stripe_secret_key;
        private $stripe_pub_key;
        private $currency;
        private $payment_intent;
        private $payment_intent_secret;
        
        function __construct() {
            $current_user   =   wp_get_current_user();

            $this->userID                  =    $current_user->ID;
            $this->user_email              =    $current_user->user_email;
            $this->display_name              =    $current_user->display_name;
            $this->currency                =    esc_html( homey_option('payment_currency', 'USD') );
            $this->stripe_secret_key       =    trim( homey_option('stripe_secret_key') );
            $this->stripe_pub_key          =    trim( homey_option('stripe_publishable_key') );

            if(class_exists('\Stripe\Stripe')){
                \Stripe\Stripe::setApiKey(  $this->stripe_secret_key );
            }else{
                return;
            }

        }
 
    
    
        /**
        * Create a Payment Intent
        */

        function homey_stripe_paymenet_intent($amount, $metadata, $description) {
    
            if( $this->currency == 'JPY') {
                $amount = $amount;
            } else {
                $amount = $amount * 100;
            }

            $payment_intent = \Stripe\PaymentIntent::create([
                "amount"                => $amount,
                "currency"              => $this->currency,
                "payment_method_types"  => ["card"],
                //"receipt_email"         =>  $this->user_email,
                "description"           =>  $description,
                "metadata"              =>  $metadata
            ]);


            $this->payment_intent           =   $payment_intent['id'];
            $this->payment_intent_secret    =   $payment_intent['client_secret'];
            
        }
         
     
        /**
        * create stripe card form
        */
        function homey_stripe_form($amount, $metadata, $description){

            $this->homey_stripe_paymenet_intent($amount, $metadata, $description);

            if( isset($metadata['payment_type']) && $metadata['payment_type'] == 'featured_fee' ) {
                $redirect_type = "back_to_listing_with_featured";
            } else if( isset($metadata['payment_type']) && $metadata['payment_type'] == 'reservation_fee'  ) {
                $redirect_type = "reservation_detail_link";
            }
            ?>

            <div id="stripe_main_wrap" style="display: none;">
                <div class="row">
                    <fieldset>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="stripe_cardholder_name"><?php esc_html_e('Name','homey-core')?></label>
                                <input class="form-control" id="stripe_cardholder_name" type="text" placeholder="<?php esc_html_e('Your Name','homey-core'); ?>" required value="<?php echo esc_attr($this->display_name); ?>">
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="stripe_cardholder_email"><?php esc_html_e('Email','homey-core')?></label>
                                <input class="form-control" id="stripe_cardholder_email" type="text" placeholder="<?php esc_html_e('Your Email','homey-core'); ?>" required value="<?php echo esc_attr($this->user_email); ?>">
                            </div>
                        </div>

                        <input type="hidden" id="redirect_type" value="<?php echo esc_attr($redirect_type); ?>">
                    </fieldset>
                </div>

                <div class="row">
                    <fieldset> 
                        <div class="col-sm-12 col-xs-12">
                            <div id="card-errors"></div>
                            <div class="form-group">
                                <div id="homey_stripe_card" class="StripeElement StripeElement--empty form-control"></div>
                            </div>
                         </div>
                    </fieldset>

                    <div class="col-sm-12 col-xs-12">
                        <button type="submit" id="homey_stripe_submit_btn" class="btn btn-success btn-full-width" data-secret="<?php echo $this->payment_intent_secret; ?> "><?php echo esc_html__('Process Payment', 'homey'); ?></button>
                    </div>

                    <div id="homey_stripe_message" class="col-sm-12 col-xs-12" style="display: none;">
                        <div class="text-info">
                            <?php esc_html_e('Please wait while we confirm your payment!','homey-core')?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
        }
        
}