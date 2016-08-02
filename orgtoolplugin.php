<?php
/*
Plugin Name: Org Tool Plugin
Plugin URI: http://oddysee.org
Description: Org Tool Plugin
Version: 0.1.8
Author: 
Author URI: 
License: GPLv2 or later
*/


# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(dirname(__FILE__) . "/lib/helper.php");
require_once(dirname(__FILE__) . "/lib/rsi_fetch.php");
require_once(dirname(__FILE__) . "/lib/orgtoolplugin.php");

require_once(ABSPATH . 'wp-content/plugins/rest-api/plugin.php');

require_once(dirname(__FILE__) . "/lib/controllers/unit_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/ship_model_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/ship_manufacturer_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/ship_controller.php");
require_once(dirname(__FILE__) . "/lib/controllers/member_unit_controller.php");

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

	$controller = new Orgtool_API_Unit();
	$controller->register_routes();

	$controller = new Orgtool_API_Member();
	$controller->register_routes();

	$controller = new Orgtool_API_ShipModel();
	$controller->register_routes();

	$controller = new Orgtool_API_ShipManufacturer();
	$controller->register_routes();

	$controller = new Orgtool_API_Ship();
	$controller->register_routes();

	$controller = new Orgtool_API_MemberUnit();
	$controller->register_routes();

	$controller = new Orgtool_API_Public();
	$controller->register_routes();
}

/////////////////////////////////////////////////
// admin page


add_action('admin_menu', 'my_cool_plugin_create_menu');

function my_cool_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('My Cool Plugin Settings', 'Cool Settings', 'administrator', __FILE__, 'my_cool_plugin_settings_page'); // , plugins_url('/images/icon.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'register_my_cool_plugin_settings' );
}


function register_my_cool_plugin_settings() {
	//register our settings
	register_setting( 'my-cool-plugin-settings-group', 'new_option_name' );
	register_setting( 'my-cool-plugin-settings-group', 'some_other_option' );
	register_setting( 'my-cool-plugin-settings-group', 'option_etc' );
}

function my_cool_plugin_settings_page() {
?>
<div class="wrap">
<h2>Your Plugin Name</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">New Option Name</th>
        <td><input type="text" name="new_option_name" value="<?php echo esc_attr( get_option('new_option_name') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Some Other Option</th>
        <td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Options, Etc.</th>
        <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }

/*

///////////////////////////////////////////////////////////////////////////////////////

// add the admin options page
add_action('admin_menu', 'orgtool_admin_add_page');
function orgtool_admin_add_page() {
  add_options_page('Orgtool Options Page', 'Orgtool Menu', 'manage_options', 'orgtool', 'orgtool_options_page');
}

function orgtool_options_page() {
?>
  <div>
  <h2>Orgtool Settings</h2>
  Options relating to the Orgtool Plugin.
  <form action="options.php" method="post">
    <?php settings_fields('orgtool_options'); ?>
    <?php do_settings_sections('orgtool'); ?>
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
  </form></div>
<?php
}


add_action('admin_init', 'orgtool_admin_init');
function orgtool_admin_init(){
  register_setting( 'orgtool_options', 'orgtool_options', 'orgtool_options_validate' );
  add_settings_section('orgtool_main', 'Main Settings', 'orgtool_section_text', 'orgtool');
  add_settings_field('orgtool_text_string', 'Orgtool Text Input', 'orgtool_setting_string', 'orgtool_main');
}

function orgtool_section_text() {
  echo '<p>Main description of this section here.</p>';
}

function orgtool_setting_string() {
  $options = get_option('orgtool_options');
  echo "<input id='orgtool_text_string' name='orgtool_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}
 */
/*
function orgtool_options_validate($input) {
  $newinput['text_string'] = trim($input['text_string']);
  if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
    $newinput['text_string'] = '';
  }
  return $newinput;
}
*/
/*
function orgtool_options_validate($input) {
  $options = get_option('orgtool_options');
  $options['text_string'] = trim($input['text_string']);
  if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
    $options['text_string'] = '';
  }
  return $options;
}
 */

///////////////////////////////////////////////////////////////////////////////////////
/*
add_action( 'admin_menu', 'my_plugin_menu' );


function my_plugin_menu() {
	add_options_page( 'Orgtool Options', 'Orgtool', 'manage_options', 'orgtool', 'orgtool_options' );
}


function orgtool_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}
*/
?>
