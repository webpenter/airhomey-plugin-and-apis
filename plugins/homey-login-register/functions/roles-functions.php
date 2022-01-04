<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/08/16
 * Time: 10:38 PM
 */

if( !function_exists('homey_add_theme_caps') ) {
    function homey_add_theme_caps() {
        

        $role = get_role('homey_host');
        $role->add_cap('level_2');

        $role = get_role('homey_sales');
        $role->add_cap('level_2');

    }
    add_action('admin_init', 'homey_add_theme_caps');
}
?>