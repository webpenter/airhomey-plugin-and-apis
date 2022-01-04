<?php
namespace Homeymembership;

use PHPMailer\PHPMailer\Exception;
use Stripe\Stripe;

//require_once __DIR__ . '/../functions/configuration.php';

class StripeService
{
    private $template_url ;
    function __construct()
    {
        require_once __DIR__ . "/../vendor/autoload.php";
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        $hm_options = get_option('hm_memberships_options');

        \Stripe\Stripe::setApiKey($hm_options['stripe_sk']);
    }

    public function createCheckoutSession($planId, $is_homey_membership, $callback_url, $currency='USD', $postId, $tax_rate_id=null)
    {
        $data = [
            'payment_method_types' => ['card'],
            'subscription_data' => [
                'items' => [[
                    'plan' => $planId,
                ]],
                'metadata' => [
                    'payment_type' => 'subscription_fee',
                    'userID' => get_current_user_id()
                ]
            ],
            'success_url' => $callback_url.'?payment_gateway=stripe&is_homey_membership=1&postId='.$postId.'&success=1&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $callback_url.'?payment_gateway=stripe&is_homey_membership=1&postId='.$postId.'&cancel=1',
        ];

        if($tax_rate_id != null && !empty(trim($tax_rate_id))){
            $data['subscription_data']['default_tax_rates'] = [$tax_rate_id];
        }

        $session = \Stripe\Checkout\Session::create($data);
        return $session;
    }

    public function getStripeResponse()
    {
        $body = @file_get_contents('php://input');
        $event_json = json_decode($body);
        return $event_json;
    }
}
?>