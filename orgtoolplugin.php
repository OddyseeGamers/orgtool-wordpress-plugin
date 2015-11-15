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

?>
