<?php
/*
Plugin Name: Homey Membership
Plugin URI:  http://themeforest.net/user/favethemes
Description: Adds membership functionality for homey theme
Version:     1.0.0
Author:      Favethemes
Author URI:  http://themeforest.net/user/favethemes
License:     GPL2
*/


namespace HomeyMembership;

use HmTrueMembershipMetaBox;

class HomeyMembership
{

    function __construct()
    {
        add_action('init', array($this, 'HomeyMemberShipInit'));

        add_action('init', array($this, 'HomeySubscriptionsInit'));
        add_action('admin_init', array($this, 'hm_register_settings'));
        add_action('manage_hm_subscriptions_posts_columns', array($this, 'HomeySubscriptionsColumns'));
        add_action('manage_hm_subscriptions_posts_custom_column', array($this, 'HomeySubscriptionsColumnsData'), '', 2);

        // Fires after WordPress has finished loading, but before any headers are sent.
        add_action('init', array($this, 'script_enqueuer'));
        add_action('admin_menu', array($this, 'home_membership_menu'));
        add_action('admin_notices', array($this, 'hm_membership_admin_notice__error'));


        add_action('wp_ajax_nopriv_stripe_membership_sessions', array($this, 'stripe_membership_sessions'));
        add_action('wp_ajax_stripe_membership_sessions', array($this,'stripe_membership_sessions'));
    }

    function home_membership_menu()
    {
        add_menu_page("Homey Memberships", "Homey Memberships", "manage_options", "homey_memberships_package", false, "dashicons-tickets", 22);
        add_options_page('Homey Membership Settings', 'Homey Membership Settings', 'manage_options', 'hm_memberships_settings', array($this, 'hm_memberships_settings_page'));
    }

    function HomeyMemberShipInit()
    {
        $supports = array('title', 'thumbnail');

        $name = 'Homey Memberships';
        $post_type_name = 'hm_' . strtolower(str_replace(' ', '_', $name));
        $singular_name = 'Homey Membership';
        register_post_type(
            $post_type_name,
            array(
                'labels' => array(
                    'name' => _x($name, 'post type general name'),
                    'singular_name' => _x($singular_name, 'post type singular name'),
                    'menu_name' => _x($name, 'admin menu'),
                    'name_admin_bar' => _x($singular_name, 'add new on admin bar'),
                    'add_new' => _x('Add New', strtolower($name)),
                    'add_new_item' => __('Add New ' . $singular_name),
                    'new_item' => __('New ' . $singular_name),
                    'edit_item' => __('Edit ' . $singular_name),
                    'view_item' => __('View ' . $singular_name),
                    'all_items' => __($name),
                    'search_items' => __('Search ' . $name),
                    'parent_item_colon' => __('Parent :' . $name),
                    'not_found' => __('No ' . strtolower($name) . ' found.'),
                    'not_found_in_trash' => __('No ' . strtolower($name) . ' found in Trash.')
                ),
                'show_in_menu' => 'homey_memberships_package',
                'public' => true,
                'has_archive' => strtolower($name),
                'hierarchical' => false,
                'rewrite' => array('slug' => 'homey-membership'),
                'menu_icon' => 'dashicons-carrot',
                'supports' => $supports
            )
        );
    }

    function HomeySubscriptionsInit()
    {
        $supports = array('title', 'thumbnail');

        $name = 'Subscriptions';
        $post_type_name = 'hm_' . strtolower(str_replace(' ', '_', $name));
        $singular_name = 'Subscription';
        register_post_type(
            $post_type_name,
            array(
                'labels' => array(
                    'name' => _x($name, 'post type general name'),
                    'singular_name' => _x($singular_name, 'post type singular name'),
                    'menu_name' => _x($name, 'admin menu'),
                    'name_admin_bar' => _x($singular_name, 'add new on admin bar'),
                    'add_new' => _x('Add New', strtolower($name)),
                    'add_new_item' => __('Add New ' . $singular_name),
                    'new_item' => __('New ' . $singular_name),
                    'edit_item' => __('Edit ' . $singular_name),
                    'view_item' => __('View ' . $singular_name),
                    'all_items' => __($name),
                    'search_items' => __('Search ' . $name),
                    'parent_item_colon' => __('Parent :' . $name),
                    'not_found' => __('No ' . strtolower($name) . ' found.'),
                    'not_found_in_trash' => __('No ' . strtolower($name) . ' found in Trash.')
                ),
                'show_in_menu' => 'homey_memberships_package',
                'public' => true,
                'has_archive' => strtolower($name),
                'hierarchical' => false,
                'rewrite' => array('slug' => $name),
                'menu_icon' => 'dashicons-carrot',
                'supports' => $supports
            )
        );
    }

    function HomeySubscriptionsColumns($columns)
    {
        $columns = array(
            "cb" => '<input type="checkbox">',
            "title" => 'Buyer',
            "status" => 'Status',
            "total_listings" => 'Total Listings',
            "detail_sub_id" => 'Detail Subscription id',
            //"remaining_listings" => 'Remaining Listings',
            "payment_gateway" => 'Payment Gateway',
            "expiry_date" => 'Expiry Date',
            "purchase_date" => 'Purchase Date',
        );
        return $columns;
    }

    function HomeySubscriptionsColumnsData($column, $postID)
    {
        switch ($column) {
            case 'status':
                $status = get_post_meta($postID, 'hm_subscription_detail_status', true);
                echo ($status == 'active') ? 'Active' : 'Expired';
                break;

            case 'payment_gateway':
                $payment_gateway = get_post_meta($postID, 'hm_subscription_detail_payment_gateway', true);
                echo $payment_gateway;
                break;

            case 'total_listings':
                $totalListing = get_post_meta($postID, 'hm_subscription_detail_total_listings', true);
                echo empty($totalListing) ? "Unlimited Listings" : $totalListing;
                break;

            case 'detail_sub_id':
                $detail_sub_id = get_post_meta($postID, 'hm_subscription_detail_sub_id', true);
                echo $detail_sub_id;
                break;

//            case 'remaining_listings':
//                $totalListing = get_post_meta($postID, 'hm_subscription_detail_total_listings', true);
//                $currentListing = count_user_posts($user_id,'listing');
//
//                $text_message = empty($totalListing) ? "Unlimited Listings" : $totalListing - $currentListing;
//                echo '<span title="Remaining Listings: ' . $text_message . '">' . $text_message . '</span>';
//
//                break;

            case 'expiry_date':
                $expiry_date = get_post_meta($postID, 'hm_subscription_detail_expiry_date', true);
                echo $expiry_date;
                break;

            case 'plan_price':
                $plan_price = get_post_meta($postID, 'hm_settings_package_price', true);
                echo $plan_price;
                break;

            case 'purchase_date':
                $purchase_date = get_post_meta($postID, 'hm_subscription_detail_purchase_date', true);
                echo $purchase_date;
                break;
        }
    }

    function script_enqueuer()
    {

        // Register the JS file with a unique handle, file location, and an array of dependencies
        wp_register_script("hm_register_script", plugin_dir_url(__FILE__) . 'assets/js/hm_register_script.js', array('jquery'));

        // localize the script to your domain name, so that you can reference the url to admin-ajax.php file easily
        wp_localize_script('hm_register_script', 'hm_register_scriptAjax', array('hm_register_scriptAjaxUrl' => admin_url('admin-ajax.php')));

        // enqueue jQuery library and the script you registered above
        wp_enqueue_script('jquery');
        wp_enqueue_script('hm_register_script');
    }

    function hm_memberships_settings_page()
    {
        ?>
        <h2>Homey Memberships Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('hm_memberships_options');
            do_settings_sections('hm_memberships'); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>"/>
        </form>
        <?php
    }

    function hm_register_settings()
    {
        register_setting('hm_memberships_options', 'hm_memberships_options', 'hm_memberships_options_validate');
        add_settings_section('homey_settings', '', array($this, 'hm_memberships_section_text'), 'hm_memberships');

        add_settings_field('hm_memberships_setting_currency', esc_html__('Currency', 'homey'), array($this, 'hm_memberships_setting_currency'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_free_numOf_listings', esc_html__('Free Number Of Listings', 'homey'), array($this, 'hm_memberships_setting_free_numOf_listings'), 'hm_memberships', 'homey_settings');

        //stripe settings
        add_settings_field('hm_memberships_setting_stripe_status', esc_html__('Stripe Status', 'homey'), array($this, 'hm_memberships_setting_stripe_status'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_stripe_sk', esc_html__('Stripe Secret Key ', 'homey'), array($this, 'hm_memberships_setting_stripe_sk'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_stripe_pk', esc_html__('Stripe Publishable Key ', 'homey'), array($this, 'hm_memberships_setting_stripe_pk'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_stripe_webhook', esc_html__('Stripe Webhook Key ', 'homey'), array($this, 'hm_memberships_setting_stripe_webhook'), 'hm_memberships', 'homey_settings');

        //paypal settings
        add_settings_field('hm_memberships_setting_paypal_status', esc_html__('Paypal Status', 'homey'), array($this, 'hm_memberships_setting_paypal_status'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_paypal_client_id', esc_html__('Paypal Client ID', 'homey'), array($this, 'hm_memberships_setting_paypal_client_id'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_setting_paypal_sk', esc_html__('Paypal Secret Key ', 'homey'), array($this, 'hm_memberships_setting_paypal_sk'), 'hm_memberships', 'homey_settings');
        add_settings_field('hm_memberships_paypal_rec_email', esc_html__('Paypal Receiving Email', 'homey'), array($this, 'hm_memberships_paypal_rec_email'), 'hm_memberships', 'homey_settings');
     
        //woocommerce settings
        add_settings_field('hm_memberships_setting_wcomrce_status', esc_html__('Woocommerce Status', 'homey'), array($this, 'hm_memberships_setting_wcomrce_status'), 'hm_memberships', 'homey_settings');
        
    }

    function hm_memberships_section_text()
    {
        echo '<p>Settings related to homey memberships</p>';
    }

    function hm_memberships_setting_currency()
    {
        $options = get_option('hm_memberships_options');
        $currency = !empty($options['currency']) ? $options['currency'] : 'USD';
        $currencies = array('USD' => 'USD', 'EUR' => 'EUR', 'AUD' => 'AUD', 'ARS' => 'ARS', 'AZN' => 'AZN', 'BRL' => 'BRL', 'CAD' => 'CAD',
            'CHF' => 'CHF', 'COP' => 'COP', 'CZK' => 'CZK', 'DKK' => 'DKK', 'HKD' => 'HKD', 'HUF' => 'HUF', 'IDR' => 'IDR', 'ILS' => 'ILS', 'INR' => 'INR',
            'JMD' => 'JMD', 'JPY' => 'JPY', 'KOR' => 'KOR', 'KSH' => 'KSH', 'LKR' => 'LKR', 'MYR' => 'MYR', 'MXN' => 'MXN', 'MUR' => 'MUR',
            'NGN' => 'NGN', 'NOK' => 'NOK', 'NZD' => 'NZD', 'PEN' => 'PEN', 'PHP' => 'PHP', 'PLN' => 'PLN', 'GBP' => 'GBP', 'RUB' => 'RUB',
            'SGD' => 'SGD', 'SEK' => 'SEK', 'TWD' => 'TWD', 'THB' => 'THB', 'TRY' => 'TRY', 'VND' => 'VND', 'ZAR' => 'ZAR');
        echo "<select id='hm_memberships_setting_currency' name='hm_memberships_options[currency]' >";
        foreach ($currencies as $key => $currencyText) {
            $selected = ($currency == $key) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $key . '" ">' . $currencyText . '</option>';
        }
        echo "</select>";
    }

    function hm_memberships_setting_free_numOf_listings()
    {
        $options = get_option('hm_memberships_options');
        $free_numOf_listings = isset($options['free_numOf_listings']) ? $options['free_numOf_listings'] : 0;
        echo "<input style='width: 20%' id='hm_memberships_setting_free_numOf_listings' name='hm_memberships_options[free_numOf_listings]' type='text' value='" . esc_attr($free_numOf_listings) . "' />";
    }

    function hm_memberships_setting_stripe_status()
    {
        $options = get_option('hm_memberships_options');
        $stripe_status = !empty($options['stripe_status']) ? $options['stripe_status'] : 'disabled';
        $statuses = array('disabled' => esc_html__('Disable', 'homey'), 'sandbox' => esc_html__('Sandbox', 'homey'), 'live' => esc_html__('Live', 'homey'));
        echo "<select id='hm_memberships_setting_stripe_status' name='hm_memberships_options[stripe_status]' >";
        foreach ($statuses as $key => $statusText) {
            $selected = ($stripe_status == $key) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $key . '" ">' . $statusText . '</option>';
        }
        echo "</select>";
    }

    function hm_memberships_setting_stripe_sk()
    {
        $options = get_option('hm_memberships_options');
        $stripe_sk = isset($options['stripe_sk']) ? $options['stripe_sk'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_stripe_sk' name='hm_memberships_options[stripe_sk]' type='text' value='" . esc_attr($stripe_sk) . "' />";
    }

    function hm_memberships_setting_stripe_pk()
    {
        $options = get_option('hm_memberships_options');
        $stripe_pk = isset($options['stripe_pk']) ? $options['stripe_pk'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_stripe_pk' name='hm_memberships_options[stripe_pk]' type='text' value='" . esc_attr($stripe_pk) . "' />";
    }

    function hm_memberships_setting_stripe_webhook()
    {
        $options = get_option('hm_memberships_options');
        $webhook = isset($options['webhook']) ? $options['webhook'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_webhook' name='hm_memberships_options[webhook]' type='text' value='" . esc_attr($webhook) . "' />";
    }

    function hm_memberships_setting_paypal_status()
    {
        $options = get_option('hm_memberships_options');
        $paypal_status = !empty($options['paypal_status']) ? $options['paypal_status'] : 'disabled';
        $statuses = array('disabled' => esc_html__('Disable', 'homey'), 'sandbox' => esc_html__('Sandbox', 'homey'), 'live' => esc_html__('Live', 'homey'));;
        echo "<select id='hm_memberships_setting_paypal_status' name='hm_memberships_options[paypal_status]' >";
        foreach ($statuses as $key => $statusText) {
            $selected = ($paypal_status == $key) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $key . '" ">' . $statusText . '</option>';
        }
        echo "</select>";
    }

    function hm_memberships_setting_paypal_client_id()
    {
        $options = get_option('hm_memberships_options');
        $paypal_client_id = isset($options['paypal_client_id']) ? $options['paypal_client_id'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_paypal_client_id' name='hm_memberships_options[paypal_client_id]' type='text' value='" . esc_attr($paypal_client_id) . "' />";
    }

    function hm_memberships_setting_paypal_sk()
    {
        $options = get_option('hm_memberships_options');
        $paypal_sk = isset($options['paypal_sk']) ? $options['paypal_sk'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_paypal_sk' name='hm_memberships_options[paypal_sk]' type='text' value='" . esc_attr($paypal_sk) . "' />";
    }

    function hm_memberships_paypal_rec_email()
    {
        $options = get_option('hm_memberships_options');
        $paypal_rec_email = isset($options['paypal_rec_email']) ? $options['paypal_rec_email'] : '';
        echo "<input style='width: 40%' id='hm_memberships_setting_paypal_rec_email' name='hm_memberships_options[paypal_rec_email]' type='text' value='" . esc_attr($paypal_rec_email) . "' />";
    }

    function hm_memberships_setting_wcomrce_status()
    {
        $options = get_option('hm_memberships_options');
        $wcomm_status = !empty($options['wcomm_status']) ? $options['wcomm_status'] : 'disabled';
        $statuses = array('disabled' => esc_html__('Disable', 'homey'), 'enable' => esc_html__('Enable', 'homey'));;
        echo "<select id='hm_memberships_setting_wcomrce_status' name='hm_memberships_options[wcomm_status]' >";
        foreach ($statuses as $key => $statusText) {
            $selected = ($wcomm_status == $key) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $key . '" ">' . $statusText . '</option>';
        }
        echo "</select>";
    }

    function hm_membership_admin_notice__error()
    {
        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'hm_homey_memberships') {
            $options = get_option('hm_memberships_options');
            if (@$options['stripe_status'] != "disabled") {
                if (empty($options['stripe_sk'])) {
                    $class = 'notice notice-error is-dismissible';
                    $message = __('Please set Credentials for Stripe payment method to create memberships with stripe.', 'homey');

                    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

                    echo '<script type="text/javascript">
                        window.onload = function() {
                            document.getElementById("publish").disabled = true;
                        }
                    </script>';
                } else {
                    $response = homey_execute_curl_request("https://api.stripe.com/v1/charges", '', $options['stripe_sk'], '', true);
                    $response_object = [];
                    if (is_string($response)) {
                        $response_object = json_decode($response);
                    }

                    if (isset($response_object->error->message)) {
                        $class = 'notice notice-error is-dismissible';
                        $message = __('Stripe Gateway: ' . $response_object->error->message, 'homey');

                        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

                        echo '<script type="text/javascript">
                            window.onload = function() {
                                document.getElementById("publish").disabled = true;
                            }
                        </script>';
                    }
                }
            }

            if (@$options['paypal_status'] != "disabled") {
                if (empty($options['paypal_sk'])) {
                    $class = 'notice notice-error is-dismissible';
                    $message = __('Please set Credentials for PayPal payment method or disable.', 'homey');

                    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

                    echo '<script type="text/javascript">
                        window.onload = function() {
                            document.getElementById("publish").disabled = true;
                        }
                    </script>';
                } else {
                    $is_paypal_live = $options['paypal_status'];
                    $paypal_host = get_payment_api_url('paypal', $is_paypal_live);

                    $url = $paypal_host . '/v1/oauth2/token';
                    $postArgs = 'grant_type=client_credentials';
                    $response = homey_get_paypal_access_token($url, $postArgs, $options['paypal_client_id'], $options['paypal_sk']);
                    $response_object = json_decode($response);

                    if (isset($response_object->error)) {
                        $class = 'notice notice-error is-dismissible';
                        $message = __('Paypal Gateway: ' . $response_object->error . ', ' . $response_object->error_description, 'homey');

                        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

                        echo '<script type="text/javascript">
                            window.onload = function() {
                                document.getElementById("publish").disabled = true;
                            }
                        </script>';
                    }
                }
            }
        }
    }


    function stripe_membership_sessions()
    {
        require_once __DIR__ . '/Service/StripeService.php';

        $stripeService = new StripeService();

        $stripe_processor_link = $_POST['stripe_processor_link'];

        $planId = $_POST["planId"];
        $currency = $_POST["currency"];
        $postID = $_POST["postID"];
        $tax_rate_id = $_POST["tax_id_stripe"];

        $session = $stripeService->createCheckoutSession($planId, 1, $stripe_processor_link, $currency, $postID, $tax_rate_id);
        echo json_encode($session);
        wp_die();
    }
}

$hm_mem_class = new HomeyMembership();

include(plugin_dir_path(__FILE__) . 'Classes/hm_true_membership_meta_box.php');

$options = array(
    array(
        'id' => 'hm_settings', // metabox ID, this is also used as custom field prefix
        'name' => 'Package Details', // title
        'post_type' => array('hm_homey_memberships'), // post types
        'position' => 'normal', // position
        'priority' => 'high', // priority
        'args' => array(
            array(
                'id' => 'bill_period',
                'name' => 'bill_period',
                'type' => 'select',
                'options' => array(
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly'
                ),
                'label' => 'Billing Period',
                'description' => ''
            ),
            array(
                'id' => 'billing_frequency',
                'name' => 'billing_frequency',
                'type' => 'text',
                'label' => 'Billing Frequency',
                'default' => 1,
                'description' => ''
            ),
            array(
                'id' => 'listings_included',
                'name' => 'listings_included',
                'type' => 'text',
                'label' => 'How many listings are included?',
                'description' => ''
            ),
            array(
                'id' => 'unlimited_listings',
                'name' => 'unlimited_listings',
                'type' => 'checkbox',
                'label' => 'Unlimited Listings',
                'description' => ''
            ),
            array(
                'id' => 'featured_listings',
                'name' => 'featured_listings',
                'type' => 'text',
                'label' => 'How many featured listings are included?',
                'description' => ''
            ),
            array(
                'id' => 'package_price',
                'name' => 'package_price',
                'type' => 'text',
                'label' => 'Package Price',
                'description' => ''
            ),
            array(
                'id' => 'stripe_package_id',
                'name' => 'stripe_package_id',
                'type' => 'text',
                'label' => 'Package Stripe Id',
                'disabled' => 'disabled',
                'description' => 'Automatically will be assigned package on Stripe.'
            ),
            array(
                'id' => 'paypal_package_id',
                'name' => 'paypal_package_id',
                'type' => 'text',
                'label' => 'Package Paypal Id',
                'disabled' => 'disabled',
                'description' => 'Automatically will be assigned package on PayPal.'
            ),
            array(
                'id' => 'visibility',
                'name' => 'visibility',
                'type' => 'select',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                ),
                'label' => 'Is it visible?',
                'description' => ''
            ),
            array(
                'id' => 'tax_id_stripe',
                'name' => 'tax_id_stripe',
                'type' => 'text',
                'label' => 'Tax Id Stripe',
                'description' => 'Enter Tax ID of your stripe account.'
            ),
            array(
                'id' => 'tax_id_paypal',
                'name' => 'tax_id_paypal',
                'type' => 'text',
                'label' => 'Tax % for Paypal',
                'description' => 'Enter the value in the number only.'
            ),
            array(
                'id' => 'popular_featured',
                'name' => 'popular_featured',
                'type' => 'select',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                ),
                'default' => 'No',
                'label' => 'Is Popular/Featured?',
                'description' => ''
            )
        )
    )
);

foreach ($options as $option) {
    new HmTrueMembershipMetaBox($option);
}

$subscription_options = array(
    array(
        'id' => 'hm_subscription_detail', // metabox ID, this is also used as custom field prefix
        'name' => 'Subscription Details', // title
        'post_type' => array('hm_subscriptions'), // post types
        'position' => 'normal', // position
        'priority' => 'high', // priority
        'args' => array(
            array(
                'id' => 'payment_gateway',
                'name' => 'payment_gateway',
                'type' => 'select',
                'options' => array(
                    'paypal' => 'Paypal',
                    'stripe' => 'Stripe'
                ),
//                'disabled' => 'disabled',
                'label' => 'Payment Gateway',
                'description' => ''
            ),
            array(
                'id' => 'status',
                'name' => 'status',
                'type' => 'select',
                'options' => array(
                    'active' => 'Active',
                    'expired' => 'Expired'
                ),
//                'disabled' => 'disabled',
                'label' => 'Status',
                'description' => ''
            ),
            array(
                'id' => 'total_listings',
                'name' => 'total_listings',
                'type' => 'text',
//                'disabled' => 'disabled',
                'label' => 'Total Listings',
                'description' => 'Total Available Listings'
            ),
            array(
                'id' => 'sub_id',
                'name' => 'sub_id',
                'type' => 'text',
//                'disabled' => 'disabled',
                'label' => 'Subscription id',
                'description' => 'Detail Subscription id'
            ),
//            array(
//                'id' => 'remaining_listings',
//                'name' => 'remaining_listings',
//                'type' => 'text',
//                'disabled' => 'disabled',
//                'label' => 'Remaining Listings',
//                'description' => 'Remaining Listings'
//            ),
            array(
                'id' => 'expiry_date',
                'name' => 'expiry_date',
                'type' => 'text',
//                'disabled' => 'disabled',
                'label' => 'Expiry Date',
                'description' => 'Expiry Date'
            ),
            array(
                'id' => 'purchase_date',
                'name' => 'purchase_date',
                'type' => 'text',
//                'disabled' => 'disabled',
                'label' => 'Purchase Date',
                'description' => 'Purchase Date'
            )
        )
    )
);

foreach ($subscription_options as $option) {
    new HmTrueMembershipMetaBox($option);
}
