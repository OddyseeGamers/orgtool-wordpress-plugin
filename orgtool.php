<?php
/*
Plugin Name: Org Tool Plugin
Plugin URI: http://oddysee.org
Description: Org Tool Plugin
Version: 0.1
Author: 
Author URI: 
License: GPLv2 or later
*/

@include_once "lib/rsi_user.php";
// @include_once "lib/orgplugin.php";
@include_once "lib/orgtoolplugin.php";

register_activation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_activation' ) );
register_deactivation_hook( __FILE__, array( 'OrgtoolPlugin', 'otp_deactivation' ));

?>
