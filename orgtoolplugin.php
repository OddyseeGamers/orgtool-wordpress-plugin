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

register_activation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_activation' ) );
register_deactivation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_deactivation' ));

add_action( 'json_api', function( $controller, $method ) {
    header( "Access-Control-Allow-Origin: *" );
}, 10, 2 );


function add_orgtool_controller($controllers) {
  $controllers[] = 'orgtool';
  return $controllers;
}
add_filter('json_api_controllers', 'add_orgtool_controller');

function set_orgtool_controller_path() {
  return dirname(__FILE__) . '/orgtoolcontroller.php';
}
add_filter('json_api_orgtool_controller_path', 'set_orgtool_controller_path');


?>
