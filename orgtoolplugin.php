<?php
/*
Plugin Name: Org Tool Plugin
Plugin URI: http://oddysee.org
Description: Org Tool Plugin
Version: 0.4.4
Author: Martin Skowronski
Author URI: 
License: GPLv2 or later
 */


# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OT_PATH', plugin_dir_path( __FILE__ ) );

require_once(dirname(__FILE__) . "/lib/helper.php");
require_once(dirname(__FILE__) . "/lib/rsi_fetch.php");
require_once(dirname(__FILE__) . "/lib/orgtoolplugin.php");

require_once(dirname(__FILE__) . "/lib/controllers/session_controller.php");

require_once(dirname(__FILE__) . "/lib/controllers/lfg_controller.php");

require_once(dirname(__FILE__) . "/lib/controllers/unit_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/handle_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_unit_controller.php");

require_once(dirname(__FILE__) . "/lib/controllers/item_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/prop_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/item_prop_controller.php");

require_once(dirname(__FILE__) . "/lib/controllers/reward_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_reward_controller.php");


require_once(dirname(__FILE__) . "/lib/controllers/public_controller.php");

/////////////////////////////////////////////////
// hooks

register_activation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_activation' ) );
register_deactivation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_deactivation' ));


/////////////////////////////////////////////////
// api

add_action( 'rest_api_init', 'orgtool_api_init', 0 );

function orgtool_api_init() {
    header( "Access-Control-Allow-Origin: *" );
    header( "Access-Control-Allow-Headers: Content-Type");
    header( "Access-Control-Expose-Headers: Content-Type");
    header( "Content-Type: application/json" );

    rest_get_server()->send_header("Content-Type", "application/json");

    $controller = new Orgtool_API_Session();
    $controller->register_routes();

    $controller = new Orgtool_API_Unit();
    $controller->register_routes();

    $controller = new Orgtool_API_Member();
    $controller->register_routes();

    $controller = new Orgtool_API_Handle();
    $controller->register_routes();

    $controller = new Orgtool_API_LFG();
    $controller->register_routes();

    $controller = new Orgtool_API_MemberUnit();
    $controller->register_routes();

    $controller = new Orgtool_API_Item();
    $controller->register_routes();

    $controller = new Orgtool_API_Prop();
    $controller->register_routes();

    $controller = new Orgtool_API_ItemProp();
    $controller->register_routes();

    $controller = new Orgtool_API_Reward();
    $controller->register_routes();

    $controller = new Orgtool_API_MemberReward();
    $controller->register_routes();

    // public
    $controller = new Orgtool_API_Public();
    $controller->register_routes();

}

/////////////////////////////////////////////////
// orrtool overwrites wordpress avatar
add_filter('get_avatar' , array('OrgtoolPlugin', 'otp_avatar') , 1 , 5 );


/////////////////////////////////////////////////
// admin page
add_action('admin_menu', array ('OrgtoolPlugin', 'orgtool_plugin_create_menu'));


/////////////////////////////////////////////////
// admin bar
add_filter('show_admin_bar', array('OrgtoolPlugin', 'otp_show_admin_bar') );


add_action('admin_bar_menu', array('OrgtoolPlugin', 'add_ot_profile'), 99);


/////////////////////////////////////////////////
// template
add_filter('template_include', array('OrgtoolPlugin', 'get_orgtool_template'));


/////////////////////////////////////////////////
// ember app
add_action('wp_enqueue_scripts','load_ember_app', 100);
function load_ember_app() {
    global $post;

    if ( is_page( get_option("orgtool_slug") )  ) {
        wp_dequeue_script( 'jquery-ui-core' );
        wp_deregister_script( 'jquery-ui-core' );
        wp_dequeue_script( 'jquery-ui-widget' );
        wp_deregister_script( 'jquery-ui-widget' );

        wp_dequeue_script( 'vendor' );
        wp_dequeue_style( 'vendor' );
        wp_dequeue_script( 'orgtool' );
        wp_dequeue_style( 'orgtool' );

        wp_enqueue_script( 'vendor', plugins_url('/orgtool/dist/assets/vendor.js', __FILE__), '', time() );
        wp_enqueue_style( 'vendor', plugins_url('/orgtool/dist/assets/vendor.css', __FILE__), '', time() );
        wp_enqueue_script( 'orgtool', plugins_url('/orgtool/dist/assets/orgtool.js', __FILE__), '', time() );
        wp_enqueue_style( 'orgtool', plugins_url('/orgtool/dist/assets/orgtool.css', __FILE__), '', time() );
    }
}

?>
