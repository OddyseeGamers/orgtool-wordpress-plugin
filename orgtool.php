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

register_activation_hook( __FILE__, array( 'OrgtoolPlugin', 'ot_activation' ) );
register_deactivation_hook( __FILE__, array( 'OrgtoolPlugin', 'ot_deactivation' ));

/*

add_filter( 'get_avatar', 'rsi_avatar_filter', 11, 5 );
function rsi_avatar_filter( $avatar, $user, $size, $default, $alt = '' ) {
	$userobj =  get_userdata( $user );
	if ($userobj) {
		global $wpdb;
		$overwrite = $wpdb->get_row("select d.value from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where d.user_id=$user and name=\"overwrite_avatar\"", ARRAY_A);

		if (strlen($overwrite["value"]) > 7) {
			$handle = $wpdb->get_row("select d.value from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where d.user_id=$user and name=\"handle\"", ARRAY_A);
			if (strlen($handle["value"]) > 0) {
				$img = getUserAvatar($handle["value"]);

				$DOM = new DOMDocument;
				$DOM->loadHTML($avatar);

				$items = $DOM->getElementsByTagName('img');
				if (sizeof($img) > 0 && $items->length == 1) {
					$src = $items->item(0)->setAttribute('src', $img);
					$avatar = $DOM->saveXML($items->item(0));
				}
			}
		}

		//$me_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE ID=$user_id", ARRAY_A);

	} else {
		error_log("NO " .  gettype($userobj));
	}
	return $avatar;
}

add_action( 'wp_ajax_query', 'query' ); 
add_action( 'wp_ajax_nopriv_query', 'query');

function my_theme_scripts_function() {
    error_log(">>> INIT  templete  enqueue");

     global $post;
    if ($post->post_name == "org-tool") {
	error_log(">>>> ENQUE SCRIPS ...");

	// wp_enqueue_script( 'jquery');
	// wp_enqueue_script( 'jquery', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/jquery/jquery.min.js');
	wp_enqueue_script( 'jquery-ui', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/jquery-ui/ui/jquery-ui.custom.js');
	wp_enqueue_script( 'bootstrap', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/startbootstrap-grayscale/js/bootstrap.min.js');
	wp_enqueue_script( 'grayscale', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/startbootstrap-grayscale/js/grayscale.js');
	wp_enqueue_script( 'bootstrap-multiselect', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js');
	wp_enqueue_script( 'd3', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/d3/d3.min.js');
	wp_enqueue_script( 'struc', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/js/struc.js');
	wp_enqueue_script( 'org_utils', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/js/org_utils.js');
	wp_enqueue_script( 'widgets', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/js/widgets.js');
	wp_enqueue_script( 'app', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/js/app.js');

        wp_localize_script( 'app', 'ajax_object', array( 'url'   => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( "query_nonce" )));

	wp_register_style( 'bootstrap', plugins_url("oddysee-tool/app/bower_components/startbootstrap-grayscale/css/bootstrap.min.css", __FILE__));
	wp_register_style( 'bootstrap-multiselect', plugins_url("oddysee-tool/app/bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css", __FILE__));

	wp_enqueue_script( 'bootstrap-multiselect', '/wp-content/plugins/orgtool-wordpress-plugin/oddysee-tool/app/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js');

	wp_register_style( 'grayscale', plugins_url("oddysee-tool/app/bower_components/startbootstrap-grayscale/css/grayscale.css", __FILE__));
	wp_register_style( 'org', plugins_url("oddysee-tool/app/css/org.css", __FILE__));
	wp_enqueue_style('bootstrap');
	wp_enqueue_style('bootstrap-multiselect');
	wp_enqueue_style('grayscale');
	wp_enqueue_style('org');


	//error_log("templte "  . plugins_url("oddysee-tool/app/bower_components/startbootstrap-grayscale/css/bootstrap.min.css", __FILE__));
   }
}
add_action('wp_enqueue_scripts','my_theme_scripts_function');

function query()
{
        check_ajax_referer( 'query_nonce', 'nonce' );

	$method = $_POST['method'];
	error_log("get users " . $method);
	if( $method == "reassign" ) {
		$handle = $_POST['handle'];
		$srcid = $_POST['srcunitid'];
		$destid = $_POST['destunitid'];
		$res = reassignMember($handle, $srcid, $destid);
		wp_send_json_success(array('lines' => $res));

//		if(is_nan($destid)) {
			//$temp = null;
//			wp_send_json_success("is none " . $destid);
//		} else {
//			wp_send_json_success("dest id " . $destid);
//		}

//		if ($res == 1) {
//			wp_send_json_success($res);
//		} else {
//		   wp_send_json_error( array( 'error' => $handle ) );
//		}

	} else if( $method == "getCaps" ) {
		$can = array();
		foreach (getUserCaps() as $perm) {
			if (current_user_can($perm)) {
				array_push($can, $perm);
			}
		}

//         error_log(" CARN READ> ? " . $can);
		wp_send_json_success($can);
	} else if( $method == "getMembers" ) {
		$mems = getUsers2();
		wp_send_json_success($mems);
	} else if( $method == "getShips" ) {
		$ships = getShipList();
		wp_send_json_success($ships);
	} else {
		wp_send_json_error( array( 'error' => 'unkown method' ) );
	}

        //if( true )
        //    wp_send_json_success($mems);
        //else
        //    wp_send_json_error( array( 'error' => $custom_error ) );
}

add_filter( 'template_include', 'rc_tc_template_chooser');
function rc_tc_template_chooser( $template ) {
    error_log(">>> INIT  templete " . $post->post_name);
 
     global $post;
//    error_log(">>> INIT " . $template . " | " . $post->post_name);
    // Post ID
//    $post_id = get_the_ID();
//    error_log(">>> INIT 1 "  . $post_id);
    if ($post->post_name == "org-tool") {
	  //  $file = "/wp-content/plugins/orgtool-wordpress-plugin/";
	  //  return apply_filters( 'orgtool_template',  $file );
//	wp_enqueue_style('bootstrap');
//	wp_enqueue_style('grayscale');
//	wp_enqueue_style('org');

          return rc_tc_get_template_hierarchy( 'orgtool_template_new' );
    } else {
        return $template;
   }

    // Else use custom template
 //   if ( is_single() ) {
//     error_log(">>>> RET 3");
     //   return rc_tc_get_template_hierarchy( 'orgtool_template_new' );
 //   }
 
}

function rc_tc_get_template_hierarchy( $template ) {
 
    // Get the template slug
    $template_slug = rtrim( $template, '.php' );
    $template = $template_slug . '.php';
 

    // $file = "/wp-content/plugins/orgtool-wordpress-plugin/";
    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array( 'plugin_template/' . $template ) ) ) {
        $file = $theme_file;
    }
    else {
        //$file = RC_TC_BASE_DIR . '/includes/templates/' . $template;
        //$file = "http://test.oddysee.org/wp-content/plugins/orgtool-wordpress-plugin/" . $template;;
        $file = "wp-content/plugins/orgtool-wordpress-plugin/" . $template;;
        //$file =  $template;;
    }
 
    error_log(">>> TEMPLATE " . $template . " FILE " . $file);
    return apply_filters( 'rc_repl_template_' . $template, $file );
}
 */

?>
