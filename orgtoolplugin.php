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

require_once(dirname(__FILE__) . "/lib/rsi_fetch.php");
require_once(dirname(__FILE__) . "/lib/orgtoolplugin.php");

/////////////////////////////////////////////////
// hooks

register_activation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_activation' ) );
register_deactivation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_deactivation' ));


/////////////////////////////////////////////////
// api

function orgtool_api_init() {
	global $orgtool_api_unit;

	header( "Access-Control-Allow-Origin: *" );
	header( "Access-Control-Allow-Headers: Content-Type");
	header( "Access-Control-Expose-Headers: Content-Type");
	header( "Content-Type: application/json" );

	require_once(dirname(__FILE__) . "/lib/controllers/unit_controller.php");
	require_once(dirname(__FILE__) . "/lib/controllers/member_controller.php");

	$orgtool_api_unit = new Orgtool_API_Unit();
	$orgtool_api_member = new Orgtool_API_Ember();
	add_filter( 'json_endpoints', array( $orgtool_api_unit, 'register_routes' ) );
	add_filter( 'json_endpoints', array( $orgtool_api_member, 'register_routes' ) );
}
add_action( 'wp_json_server_before_serve', 'orgtool_api_init' );


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
