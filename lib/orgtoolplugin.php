<?php

class OrgtoolPlugin {

	private function add_cap() {
		$roles = get_editable_roles();

		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
			if (isset($roles[$key])) {
				error_log("add cap " . $key);
				if ($key == "administrator") {
					foreach (getUserCaps() as $perm) {
						$role->add_cap($perm);
					}
				} else {
					$role->add_cap("read_orgtool_overview");
					$role->add_cap("read_orgtool_member_assignment");
				}
			}
//			if (isset($roles[$key]) && $role->has_cap('BUILT_IN_CAP')) {
//				$role->add_cap('THE_NEW_CAP');
//			}
		}

		foreach (getShipList() as $idx => $ship) {
            $num = 100 + $idx;
            if (xprofile_get_field_id_from_name($ship)) {
                error_log("found ship" . $ship);
            }  else {
                error_log("insert ship" . $ship);
                xprofile_insert_field(
                    array (
                        'field_group_id'  => 3,
                        'name'            => $ship,
                        'field_order'     => 100 + $idx,
                        'is_required'     => false,
                        'type'            => 'number'
                    )
                );
            }
        }
	}

	private function remove_cap() {
		$roles = get_editable_roles();

		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
			if (isset($roles[$key])) {
				foreach (getUserCaps() as $perm) {
					$role->remove_cap($perm);
				}
			}
		}
	}



	function initRSIUsers() {
		global $wpdb;
		$table_name = $wpdb->prefix . "rsi_users";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  handle tinytext NOT NULL,
		  name tinytext NOT NULL,
		  img varchar(255) DEFAULT '' NOT NULL,
		  role text,
		  roles text,
		  rank text,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  timezone text,
		  unit int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}


	function createSchema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		error_log(">> ceate schame");

		dbDelta( self::createMembers($charset_collate) );
		dbDelta( self::createUnits($charset_collate) );
		dbDelta( self::createUnitTypes($charset_collate) );
		dbDelta( self::createMemberUnits($charset_collate) );
	}

	function createMembers($charset_collate) {
		$table_name = $wpdb->prefix . "ot_member";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  handle tinytext NOT NULL,
		  avatar varchar(255) DEFAULT '' NOT NULL,
		  timezone int(11) DEFAULT NULL,
		  updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  ships int(11) DEFAULT NULL,
		  units int(11) DEFAULT NULL,
		  rewards int(11) DEFAULT NULL,
		  logs int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}

	function createUnits($charset_collate) {
		$table_name = $wpdb->prefix . "ot_unit";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  desc tinytext NOT NULL,
		  img varchar(255) DEFAULT '' NOT NULL,
		  type int(11) DEFAULT NULL,
		  parent int(11) DEFAULT NULL,
		  units int(11) DEFAULT NULL,
		  ships int(11) DEFAULT NULL,
		  members int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}

	function createUnitTypes($charset_collate) {
		$table_name = $wpdb->prefix . "ot_unit_type";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  desc tinytext NOT NULL,
		  units int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}

	function createMemberUnits($charset_collate) {
		$table_name = $wpdb->prefix . "ot_member_unit";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  unit int(11) DEFAULT NULL,
		  member int(11) DEFAULT NULL,
		  log int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}


	function install() {

		global $wpdb;

		$the_page_title = 'Org Tool';
		$the_page_name = 'org-tool';

		error_log("add option " . $the_page_title);
		// the menu entry...
		delete_option("orgtool_page_title");

		add_option("orgtool_page_title", $the_page_title, '', 'yes');
		// the slug...
		delete_option("orgtool_page_name");
		add_option("orgtool_page_name", $the_page_name, '', 'yes');
		// the id...
		delete_option("orgtool_page_id");
		add_option("orgtool_page_id", '0', '', 'yes');

		$the_page = get_page_by_title( $the_page_title );

		if ( ! $the_page ) {

		    // Create post object
		    $_p = array();
		    $_p['post_title'] = $the_page_title;
		    $_p['post_content'] = "<div> page title" . $the_page_title . "</div>";
		    $_p['post_status'] = 'publish';
		    $_p['post_type'] = 'page';
		    $_p['comment_status'] = 'closed';
		    $_p['ping_status'] = 'closed';
		    $_p['post_category'] = array(1); // the default 'Uncatrgorised'

		    // Insert the post into the database
		    $the_page_id = wp_insert_post( $_p );

		}
		else {
		    // the plugin may have been previously active and the page may just be trashed...

		    $the_page_id = $the_page->ID;

		    //make sure the page is not trashed...
		    $the_page->post_status = 'publish';
		    $the_page_id = wp_update_post( $the_page );

		}

		delete_option( 'orgtool_page_id' );
		add_option( 'orgtool_page_id', $the_page_id );

	}
	function uninstall() {
		global $wpdb;

		$the_page_title = get_option( "orgtool_page_title" );
		$the_page_name = get_option( "orgtool_page_name" );

		//  the id of our page...
		$the_page_id = get_option( 'orgtool_page_id' );
		if( $the_page_id ) {

		    wp_delete_post( $the_page_id ); // this will trash, not delete

		}

		delete_option("orgtool_page_title");
		delete_option("orgtool_page_name");
		delete_option("orgtool_page_id");
	}

	function fetchAll() {
		$res = true;
		$page = 1;
		while($res) {
			error_log("fetch " . $page);
			$res = fetchFromRSI("OODDYSEE", $page++);
		}
	}


	function otp_activation() {
		error_log(">> ot_activate");
		self::createSchema();
		self::fetchAll();

//         self::add_cap();
//         self::install();
//         self::initRSIUsers();
//         self::fetchAll();
	}

	function otp_deactivation() {
		error_log(">> ot_deactivate");
//         self::remove_cap();
//         self::uninstall();
	}

}


?>
