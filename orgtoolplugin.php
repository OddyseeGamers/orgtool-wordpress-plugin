<?php
/*
Plugin Name: Org Tool Plugin
Plugin URI: http://oddysee.org
Description: Org Tool Plugin
Version: 0.1.6
Author: 
Author URI: 
License: GPLv2 or later
*/


# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(dirname(__FILE__) . "/lib/rsi_fetch.php");
require_once(dirname(__FILE__) . "/lib/orgtoolplugin.php");

require_once(ABSPATH . 'wp-content/plugins/rest-api/plugin.php');

require_once(dirname(__FILE__) . "/lib/controllers/unit_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/ship_model_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/ship_manufacturer_controller.php");

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
	header( "Content-Type: application/json" );

	$controller = new Orgtool_API_Unit();
	$controller->register_routes();

	$controller = new Orgtool_API_Member();
	$controller->register_routes();

	$controller = new Orgtool_API_ShipModel();
	$controller->register_routes();

	$controller = new Orgtool_API_ShipManufacturer();
	$controller->register_routes();
}

/////////////////////////////////////////////////
// admin page

/** Step 2 (from text above). */
add_action( 'admin_menu', 'my_plugin_menu' );

/** Step 1. */
function my_plugin_menu() {
	add_options_page( 'Orgtool Options', 'Orgtool', 'manage_options', 'orgtool', 'orgtool_options' );
}

/** Step 3. */
function orgtool_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

?>
