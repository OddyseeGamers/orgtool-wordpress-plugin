<?php

class Orgtool_API_Member
{

    protected $namespace = 'orgtool';
    private $base = 'members';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        //$base = $this->get_post_type_base( $this->post_type );
        //$base = $this->type;

        register_rest_route($this->namespace, '/' . $this->base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_members' ),
//                 'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_member' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_member' ),
//                 'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_member' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_member' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

    }

    public function get_members_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
//         if (!$user_id || !user_can($user_id, 'administrator')) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }


    public function get_members($_headers) {
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member";
        $table_units = $wpdb->prefix . "ot_member_unit";
        $table_rewards = $wpdb->prefix . "ot_member_reward";
        $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';

/*
    $searchsql = "select ru.*, temp.wp_id, temp.wp_profile from oddyse5_wp978.wp_ot_member as ru left join ( "
                . "select d.value as wp_handle, u.ID as wp_id, "
                . "CONCAT('{', GROUP_CONCAT('\"', d.field_id, '\":\"', d.value, '\"' ORDER BY d.value DESC SEPARATOR ','),'}') as wp_profile "
                . "FROM oddyse5_wp978.wp_users as u join oddyse5_wp978.wp_bp_xprofile_data as d on u.ID = d.user_id "
                . "where d.field_id = 2 "
                . "or d.field_id = 3 "
                . "or d.field_id > 300 " 
                . "group by wp_id "
                . ") as temp on ru.handle = temp.wp_handle "
                . "union "
                . "select ru.*, temp.wp_id, temp.wp_profile from oddyse5_wp978.wp_ot_member as ru right join ( "
                . "select d.value as wp_handle, u.ID as wp_id, "
                . "CONCAT('{', GROUP_CONCAT('\"', d.field_id, '\":\"', d.value, '\"' ORDER BY d.value DESC SEPARATOR ','),'}') as wp_profile "
                . "FROM oddyse5_wp978.wp_users as u join oddyse5_wp978.wp_bp_xprofile_data as d on u.ID = d.user_id "
                . "where d.field_id = 2 "
                . "or d.field_id = 3 "
                . "or d.field_id > 300 " 
                . "group by wp_id "
                . ") as temp on ru.handle = temp.wp_handle";
 */

        $results = $wpdb->get_results($searchsql);

        foreach($results as $member) {
            $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_handle WHERE member = ' . $member->id;
            $hand_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($hand_ids as $p) {
                array_push($ids, $p->id);
            }
            $member->handles = $ids;


            $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_item WHERE member = ' . $member->id;
            $item_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($item_ids as $p) {
                array_push($ids, $p->id);
            }
            $member->items = $ids;

            $sql = 'SELECT id FROM ' . $table_units . ' WHERE member = ' . $member->id;
            $unit_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($unit_ids as $p) {
                array_push($ids, $p->id);
            }
            $member->memberUnits = $ids;

            $sql = 'SELECT id FROM ' . $table_rewards . ' WHERE member = ' . $member->id;
            $rew_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($rew_ids as $p) {
                array_push($ids, $p->id);
            }
            $member->memberRewards = $ids;
        }

        return array('members' => $results);
    }


    public function get_member($request, $details = true) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_name = $wpdb->prefix . "ot_member";
        $table_units = $wpdb->prefix . "ot_member_unit";
        $table_rewards = $wpdb->prefix . "ot_member_reward";
        $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
        $member = $wpdb->get_row($searchsql);

        if ( null !== $member ) {

            if ($details) {
                $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_handle WHERE member = ' . $member->id;
                $hand_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($hand_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->handles = $ids;

                $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_item WHERE member = ' . $member->id;
                $item_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($item_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->items = $ids;

                $sql = 'SELECT id FROM ' . $table_units . ' WHERE member = ' . $member->id;
                $unit_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($unit_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->memberUnits = $ids;

                $sql = 'SELECT id FROM ' . $table_rewards . ' WHERE member = ' . $member->id;
                $rew_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($rew_ids as $p) {
                    array_push($ids, $p->id);
                }
                $member->memberRewards = $ids;

                return array('member' => $member);
            } else {
                return $member;
            }

            return array('member' => $member);
        } else { 
            return new WP_Error( 'error', __( 'member not found' ), array( 'status' => 404 ) );
        }
    }


    public function create_member($request) {
//         $user_id = get_current_user_id();
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id || !user_can($user_id, 'administrator')) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }

        // why do I have to do this?? missing arg? WP API borken?
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['member'] ) || $data['member'] == [] ) {
            //return array("debug"=>1);
            $data = $data["member"];
        }
        if ( empty( $data['name']) ) {
            $data["name"] = "";
            //return array("debug"=> $data);
        }


        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member";
        $res = $wpdb->insert($table_name, $data);

        //  return array("data" => $data, "id" => $wpdb->insert_id, "rese" => $res);

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return $this->get_member( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'member not created' ), array( 'status' => 400 ) );
        }

    }


    public function update_member($request) { //  $id, $data = "", $_headers = array() ) {
//         $user_id = get_current_user_id();
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        $data = json_decode( $request->get_body(), true );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'permission denied -> ' . $user_uid ), array( 'status' => 550 ) );
        }
        if (!user_can($user_id, 'administrator') && $data["wp_id"] != $user_id) {
            return new WP_Error( 'error', __( 'permission denied 2 -> ' ), array( 'status' => 550 ) );
        }

        if (!user_can($user_id, 'administrator')) {
            unset($data["wp_id"]);
        }
        $id = (int) $request['id'];

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member";

        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_member( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update member error ' . $res->last_error), array( 'status' => 404 ) );
        }

    }

    public function delete_member($request) {
        $id = (int) $request['id'];
        $member = $this->get_member( array("id" => $id), false);

        if ( empty( $id ) || empty( $member->id ) ) {
            return new WP_Error( 'error', __( 'member not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member";
        $res = $wpdb->delete($table_name, array('id' => $id));
    }
}

?>
