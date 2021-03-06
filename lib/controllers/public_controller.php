<?php

class Orgtool_API_Public
{

    protected $namespace = 'orgtool';
    //     private $base = 'members';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        //$base = $this->get_post_type_base( $this->post_type );
        //$base = $this->type;


        register_rest_route($this->namespace, '(?P<org>[\w]+)/members', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_members' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
        ) );

    }

    public function get_members_permissions_check( $request ) {
/*
        $post_type = get_post_type_object( $this->post_type );

        if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }
 */
        return true;
    }


    public function get_members($request) {
        $org = $request['org'];

        if (strtolower($org) != "oddysee") {
            return new WP_Error( 'error', __( 'not found' ), array( 'status' => 404 ) );
        } 
        $query = $request->get_query_params();

/*
                if (array_key_exists("tz", $query)) {
        return array("search for" => $org, "query" => $query);
        } else {
        return array("org" => $org, "val" => intval($query["tz"]));
        }
 */
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member";
        $table_member = $wpdb->prefix . "ot_member_unit";
        $table_unit = $wpdb->prefix . "ot_unit";
        $tbl_item = $wpdb->prefix . "ot_item";
        $tbl_type = $wpdb->prefix . "ot_item_type";

        $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
        if (array_key_exists("tz", $query)) {
            if (is_array($query["tz"])) {
                $searchsql = 'SELECT * FROM ' . $table_name . ' where timezone in (' . join(", ", $query["tz"]) . ') order by timezone';
            } else {
                $searchsql = 'SELECT * FROM ' . $table_name . ' where timezone = ' . intval($query["tz"]) . ' order by timezone';
            }
        }

        $results = $wpdb->get_results($searchsql);
        foreach($results as $member) {
            $sql = 'select it.name, itt.name as type, pit.name as parent, ittt.name as parentType from ' . $tbl_item . ' as it '
                   . ' left join ' . $tbl_item . ' as pit on it.parent = pit.id'
                   . ' left join ' . $tbl_type . ' as itt on it.type = itt.id'
                   . ' left join ' . $tbl_type . ' as ittt on pit.type = ittt.id'
                   . ' where it.member = ' . $member->id;
            $items = $wpdb->get_results( $sql);

            $member->items = $items;

            $sql = 'SELECT u.name FROM ' . $table_member . ' as m left join ' . $table_unit . ' as u on m.unit = u.id WHERE m.member = ' . $member->id;
            $units = $wpdb->get_results( $sql);

            $member->units = $units;
            unset($member->id);
            unset($member->wp_id);
            unset($member->updated_at);
            unset($member->logs);
        }

        return array('members' => $results);
    }


    public function get_member($request, $details = true) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_name = $wpdb->prefix . "ot_member";
        $table_member = $wpdb->prefix . "ot_member_unit";
        $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
        $member = $wpdb->get_row($searchsql);

        if ( null !== $member ) {

            if ($details) {
                $table_ship = $wpdb->prefix . "ot_ship";
                $sql = 'SELECT id FROM ' . $table_ship . ' WHERE member = ' . $member->id;
                $ship_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($ship_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->ships = $ids;

                $sql = 'SELECT id FROM ' . $table_member . ' WHERE member = ' . $member->id;
                $unit_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($unit_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->member_units = $ids;

                return array('member' => $member);
            } else {
                return $member;
            }

            return array('member' => $member);
        } else { 
            return new WP_Error( 'error', __( 'member not found' ), array( 'status' => 404 ) );
        }
    }
}

?>
