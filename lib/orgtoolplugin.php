<?php

class OrgtoolPlugin {

	function createSchema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		error_log(">> ceate schame");

		/*
		dbDelta( self::createMembers($charset_collate) );
		dbDelta( self::createUnits($charset_collate) );
		dbDelta( self::createUnitTypes($charset_collate) );
		dbDelta( self::createMemberUnits($charset_collate) );
		 */
	}
/*
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
 */


	function fetchAll() {
		$res = true;
		$page = 1;
//         while($res) {
//             error_log("fetch " . $page);
//             $res = fetchFromRSI("OODDYSEE", $page++);
//         }
	}


	function otp_activation() {
		error_log(">> ot_activate");
		self::createSchema();
//         self::fetchAll();
	}

	function otp_deactivation() {
		error_log(">> ot_deactivate");
	}
}

?>
