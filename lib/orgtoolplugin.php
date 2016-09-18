<?php

class OrgtoolPlugin {

    function createSchema() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//         dbDelta( self::createMembers($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createUnits($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createUnitTypes($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createMemberUnits($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createShipModels($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createShipManufacturer($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createShipClass($wpdb->prefix, $charset_collate) );
//         dbDelta( self::createShip($wpdb->prefix, $charset_collate) );

        dbDelta( self::createItemPropertyType($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItemProperties($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItemType($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItem($wpdb->prefix, $charset_collate) );

        error_log(">> create schema done");
    }

    function createMembers($prefix, $charset_collate) {
        $table_name = $prefix . "ot_member";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            wp_id int(11) DEFAULT NULL,
            handle tinytext,
            discord tinytext,
            avatar text,
            timezone int(11) DEFAULT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }
//           ships int(11) DEFAULT NULL,
//           units int(11) DEFAULT NULL,
//           rewards int(11) DEFAULT NULL,
//           logs int(11) DEFAULT NULL,

    function createUnits($prefix, $charset_collate) {
        $table_name = $prefix . "ot_unit";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            color tinytext,
            img text,
            type tinytext,
            parent int(11) DEFAULT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createUnitTypes($prefix, $charset_collate) {
        $table_name = $prefix . "ot_unit_type";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            img text,
            ordering int(11),
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

    function createShipModels($prefix, $charset_collate) {
        $table_name = $prefix . "ot_ship_model";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            img text,
            crew int(11) DEFAULT NULL,
            length float DEFAULT NULL,
            mass int,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            manufacturer int(11) DEFAULT NULL,
            class int(11) DEFAULT NULL,
                type int(11) DEFAULT NULL,
                UNIQUE KEY id (id)
            ) $charset_collate;";
    }

//           roles int(11) DEFAULT NULL,
//           ships int(11) DEFAULT NULL,

    function createShipManufacturer($prefix, $charset_collate) {
        $table_name = $prefix . "ot_ship_manufacturer";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            img text,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createShipClass($prefix, $charset_collate) {
        $table_name = $prefix . "ot_ship_class";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            img text,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createShip($prefix, $charset_collate) {
        $table_name = $prefix . "ot_ship";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext,
            model int(11) DEFAULT NULL,
            member int(11) DEFAULT NULL,
            hidden tinyint(1),
           available tinyint(1),
           UNIQUE KEY id (id)
       ) $charset_collate;";
    }

    function createItemPropertyType($prefix, $charset_collate) {
        $table_name = $prefix . "ot_item_prop_type";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            typeName tinytext NOT NULL,
            name tinytext NOT NULL,
            description text,
            img text,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createItemProperties($prefix, $charset_collate) {
        $table_name = $prefix . "ot_item_prop";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            img text,
            value text,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            type int(11) DEFAULT NULL,
            item int(11) DEFAULT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createItemType($prefix, $charset_collate) {
        $table_name = $prefix . "ot_item_type";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            typeName tinytext NOT NULL,
            name tinytext NOT NULL,
            description text,
            img text,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createItem($prefix, $charset_collate) {
        $table_name = $prefix . "ot_item";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext,
            description text,
            img text,
            type int(11) DEFAULT NULL,
            parent int(11) DEFAULT NULL,
            member int(11) DEFAULT NULL,
            unit int(11) DEFAULT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function initFixtures() {
        $json = file_get_contents(dirname(__FILE__) . "/../fixtures/units.json");
        $json_a = json_decode($json, true);
        foreach ($json_a["units"] as $name => $_a) {
            insertOrUpdateUnit($_a);
        }

        $json = file_get_contents(dirname(__FILE__) . "/../fixtures/unit_types.json");
        $json_a = json_decode($json, true);
        foreach ($json_a["unit_types"] as $name => $_a) {
            insertOrUpdateUnitType($_a);
        }
    }

    function fetchAll() {
        fetchShips();

//         $rsimembers = fetchMembers();
//         $rsimembersleft = mergeWPMembers($rsimembers);
//         $reversed = array_reverse($rsimembersleft);
//         foreach ($reversed as $mem) {
//             insertOrUpdateMember($mem);
//         }
    }

    function importFromWP() {
        importShipsAndAssign();
    }

    function otp_activation() {
        error_log(">> ot_activate");
        self::createSchema();
        self::fetchAll();
//         self::initFixtures();
//         self::importFromWP();
    }

    function otp_deactivation() {
        error_log(">> ot_deactivate");
        self::createSchema();
        self::fetchAll();
//         self::importFromWP();
    }
}

?>
