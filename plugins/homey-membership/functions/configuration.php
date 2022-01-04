<?php

require_once("../../../../wp-load.php");

class configuration
{
    public static function get_memebership_setting( $id ) {
        $hm_options = get_option('hm_memberships_options');

        return $hm_options['stripe_sk'];
    }
}