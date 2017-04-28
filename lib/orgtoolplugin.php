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

        dbDelta( self::createHandles($wpdb->prefix, $charset_collate) );

        dbDelta( self::createPropertyType($wpdb->prefix, $charset_collate) );
        dbDelta( self::createProperty($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItemType($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItem($wpdb->prefix, $charset_collate) );
        dbDelta( self::createItemProperty($wpdb->prefix, $charset_collate) );

        dbDelta( self::createReward($wpdb->prefix, $charset_collate) );
        dbDelta( self::createRewardType($wpdb->prefix, $charset_collate) );
        dbDelta( self::createMemberReward($wpdb->prefix, $charset_collate) );

        dbDelta( self::createLog($wpdb->prefix, $charset_collate) );

        error_log(">> create schema done");
    }

    function createMembers($prefix, $charset_collate) {
        $table_name = $prefix . "ot_member";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            wp_id int(11) DEFAULT NULL,
            name tinytext NOT NULL,
            avatar text,
            timezone int(11) DEFAULT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createHandles($prefix, $charset_collate) {
        $table_name = $prefix . "ot_handle";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type tinytext,
            name tinytext,
            login tinytext,
            handle tinytext,
            img text,
            member int(11) DEFAULT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

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
            reward int(11) DEFAULT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
    }

    function createPropertyType($prefix, $charset_collate) {
        $table_name = $prefix . "ot_prop_type";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            typeName tinytext NOT NULL,
            name tinytext NOT NULL,
            description text,
            img text,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    function createProperty($prefix, $charset_collate) {
        $table_name = $prefix . "ot_prop";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description text,
            img text,
            value text,
            unit text,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            type int(11) DEFAULT NULL,
            item int(11) DEFAULT NULL,
            PRIMARY KEY (id)
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
            permissions int(11) DEFAULT '0'
            PRIMARY KEY (id)
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
            hidden bit(1) DEFAULT NULL,
            available bit(1) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    function createItemProperty($prefix, $charset_collate) {
        $table_name = $prefix . "ot_item_prop";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            item int(11) DEFAULT NULL,
            prop int(11) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    function createReward($prefix, $charset_collate) {
        $table_name = $prefix . "ot_reward";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type int(11) DEFAULT NULL,
            name tinytext NOT NULL,
            description tinytext,
            img tinytext,
            level int(11) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    function createRewardType($prefix, $charset_collate) {
        $table_name = $prefix . "ot_reward_type";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            description tinytext,
            img tinytext,
            level int(11) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    function createMemberReward($prefix, $charset_collate) {
        $table_name = $prefix . "ot_member_reward";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member int(11) DEFAULT NULL,
            reward int(11) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }


    function createLog($prefix, $charset_collate) {
        $table_name = $prefix . "ot_log";
        return "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            comp text,
            action text,
            msg text,
            target int(11) DEFaulT NULL,
            member int(11) DEFAULT NULL,
            timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    }

    //////////////////////////////////////

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
            //                      if (isset($roles[$key]) && $role->has_cap('BUILT_IN_CAP')) {
            //                              $role->add_cap('THE_NEW_CAP');
            //                      }
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


    function install() {
        self::uninstall();

        $the_page_title = 'Org Tool';
        if (empty(get_option("orgtool_slug"))) {
            update_option("orgtool_slug", "orgtool");
        }

        $slug = get_option("orgtool_slug");
        $idx = strrpos($slug, '/');

        $base = $slug;
        $parent = ""; 
        $idx = strrpos($slug, '/');
        if ($idx) {
            $base = substr($slug, $idx + 1);
            $parent = substr($slug, 0, $idx);
        }

        // the menu entry...
        add_option("orgtool_page_title", $the_page_title, '', 'yes');
        add_option("orgtool_page_id", '0', '', 'yes');

        $the_page = get_page_by_path( $slug );
        if ( ! $the_page ) {
            // Create post object
            $_p = array();
            $_p['post_title'] = $the_page_title;
            $_p['post_name'] = $base;
            $_p['post_content'] = "";
            $_p['post_status'] = 'publish';
            $_p['post_type'] = 'page';
            $_p['comment_status'] = 'closed';
            $_p['ping_status'] = 'closed';
            $_p['post_category'] = array(1); // the default 'Uncatrgorised'

            if (!empty($parent)) {
                $the_parent = get_page_by_path( $parent );
                if ( ! $the_parent ) {
                    error_log(">>> _NO_ parent ");
                } else {
                    $_p['post_parent'] = $the_parent->ID;
                }
            }

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
        //  the id of our page...
        $the_page_id = get_option( 'orgtool_page_id' );
        if( $the_page_id ) {
            wp_delete_post( $the_page_id, true );
        }

        delete_option("orgtool_page_title");
        delete_option("orgtool_page_id");
    }


    //////////////////////////////////////

    function initFixtures() {
        $json = file_get_contents(dirname(__FILE__) . "/../fixtures/units.json");
        $json_a = json_decode($json, true);
        foreach ($json_a["units"] as $name => $_a) {
            insertUnit($_a);
        }

        $json = file_get_contents(dirname(__FILE__) . "/../fixtures/unit_types.json");
        $json_a = json_decode($json, true);
        foreach ($json_a["unit_types"] as $name => $_a) {
            insertUnitType($_a);
        }
    }

    function otp_activation() {
        error_log(">> ot_activate");
        self::createSchema();
        self::initFixtures();
        self::add_cap();
        self::install();
    }

    function otp_deactivation() {
        error_log(">> ot_deactivate");
        self::remove_cap();
        self::uninstall();
    }

    /////////////////////////////////////////////////
    // admin bar
    function otp_show_admin_bar($bool) {
        global $post;
        if (is_page(get_option("orgtool_slug")) && get_option("hide_adminbar") == "1") {
            return false;
        }
        return current_user_can('read');
    }


    function otp_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        if (get_option("overwrite_avatar") == "1") {
            $user = false;

            if ( is_numeric( $id_or_email ) ) {
                $id = (int) $id_or_email;
                $user = get_user_by( 'id' , $id );
            } elseif ( is_object( $id_or_email ) ) {
                if ( ! empty( $id_or_email->user_id ) ) {
                    $id = (int) $id_or_email->user_id;
                    $user = get_user_by( 'id' , $id );
                }
            } else {
                $user = get_user_by( 'email', $id_or_email );	
            }

            if ( $user && is_object( $user ) ) {
                global $wpdb;
                $searchsql = 'SELECT * FROM ' . $wpdb->prefix . 'ot_member where wp_id = "'. $user->data->ID . '"';
                $otuser = $wpdb->get_row($searchsql);
                $oid = 0;

                if ( null !== $otuser ) {
                    $img = $otuser->avatar;
                    if ( null !== $img ) {
    //                     $avatar = 'YOUR_NEW_IMAGE_URL';
                        $avatar = "<img alt='{$alt}' src='{$img}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
                    }
                }
            }
        }

        return $avatar;
    }


    function add_ot_profile($wp_admin_bar) {
        if (get_option("add_profile_link") == "1") {
            $uid = get_current_user_id();
            global $wpdb;
            $searchsql = 'SELECT * FROM ' . $wpdb->prefix . 'ot_member where wp_id = "'. $uid . '"';
            $otuser = $wpdb->get_row($searchsql);
            $oid = 0;
            if ( null !== $otuser ) {
                $current_user = wp_get_current_user();
                $oid = $otuser->id;
                $profile_url = "/" . get_option("orgtool_slug") . "/#/members/" . $oid;

                $wp_admin_bar->add_menu( array(
                    'parent' => 'user-actions',
                    'id'     => 'user-info-ot',
                    'title'  => 'Orgtool Profile',
//                     'title'  => $user_info,
                    'href'   => $profile_url,
//                     'meta'   => array(
//                         'tabindex' => -1,
//                     ),
                ) );

            } else { 
                // TODO ???
            }
        }
    }

    function get_orgtool_template( $template ) {
        if ( is_page( get_option("orgtool_slug") )  ) {
            return dirname(__FILE__) . "/../templates/orgtool_template.php";
        }
        return $template;
    }


    /////////////////////////////
    // ADMIN PAGE

    function orgtool_plugin_create_menu() {
        //create new top-level menu
        add_menu_page('Orgtool Plugin Settings', 'Orgtool', 'administrator', __FILE__, array('OrgtoolPlugin', 'orgtool_admin_page')); // , plugins_url('/images/icon.png', __FILE__) );

        //call register settings function
        add_action( 'admin_init', array('OrgtoolPlugin', 'register_orgtool_plugin_settings') );
    }


    function register_orgtool_plugin_settings() {
        //register our settings
        register_setting( 'orgtool-plugin-settings-group', 'orgtool_slug' );
        register_setting( 'orgtool-plugin-settings-group', 'rsi_org' );
        register_setting( 'orgtool-plugin-settings-group', 'overwrite_avatar' );
        register_setting( 'orgtool-plugin-settings-group', 'hide_adminbar' );
        register_setting( 'orgtool-plugin-settings-group', 'add_profile_link' );
    }

    function orgtool_admin_page() {
        if (!empty($_POST['save-submit'])) {
            if (isset($_POST['rsi_org'])) {
                update_option('rsi_org', $_POST['rsi_org']);
            } 

            if (isset($_POST['orgtool_slug'])) {
                update_option('orgtool_slug', $_POST['orgtool_slug']);
                self::install();
            } 

            foreach(array("overwrite_avatar", "hide_adminbar", "add_profile_link") as $op) {
                if (!isset($_POST[$op]) || empty($_POST[$op])) {
                    update_option($op, '0');
                } else {
                    update_option($op, $_POST[$op]);
                }
            }
        }

        include dirname(__FILE__) . '/../templates/admin-page.php';

        if (!empty($_POST['fetch-ships-submit'])) {
            fetchShips();
        }
    }
}

?>
