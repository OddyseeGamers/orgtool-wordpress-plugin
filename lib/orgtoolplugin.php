<?php

class OrgtoolPlugin {

	function createSchema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::createMembers($wpdb->prefix, $charset_collate) );
		dbDelta( self::createUnits($wpdb->prefix, $charset_collate) );
		dbDelta( self::createUnitTypes($wpdb->prefix, $charset_collate) );
		dbDelta( self::createMemberUnits($wpdb->prefix, $charset_collate) );
		error_log(">> create schema done");
	}

	function createMembers($prefix, $charset_collate) {
		$table_name = $prefix . "ot_member";
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

	function createUnits($prefix, $charset_collate) {
		$table_name = $prefix . "ot_unit";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  description text,
		  img varchar(255) DEFAULT '' NOT NULL,
		  type int(11) DEFAULT NULL,
		  parent int(11) DEFAULT NULL,
		  units int(11) DEFAULT NULL,
		  ships int(11) DEFAULT NULL,
		  members int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}

	function createUnitTypes($prefix, $charset_collate) {
		$table_name = $prefix . "ot_unit_type";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  description text,
		  units int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}

	function createMemberUnits($prefix, $charset_collate) {
		$table_name = $prefix . "ot_member_unit";
		return "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  unit int(11) DEFAULT NULL,
		  member int(11) DEFAULT NULL,
		  log int(11) DEFAULT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
	}



	function fetchAll() {
		$done = false;
		$page = 1;
		
		$members = array();
		do {
			$res = fetchFromRSI("ODDYSEE", $page++);
			$done = (sizeof($res) > 0 ? false : true);
			if (!$done) {
				$members = array_merge ($members, $res);
			}
		} while(!$done);

		error_log("members " . sizeof($members));
		$reversed = array_reverse($members);
		foreach ($reversed as $idx => $mem) {
//             error_log(" >>>  " . $idx . " = " . $mem["handle"]);
			insertOrUpdate($mem);
		}
	}


	function otp_activation() {
		error_log(">> ot_activate");
		self::createSchema();
		self::fetchAll();
	}

	function otp_deactivation() {
		error_log(">> ot_deactivate");
	}
}

?>
