<?php

class Orgtool_API_MemberUnit
{
    protected $namespace = 'orgtool';
    private $base = 'member_units';
    //     private $base_type = 'ship_types';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $base = $this->base;
        register_rest_route($this->namespace, '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_member_units' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_member_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_member_unit' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_member_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_member_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

        /*
        register_rest_route($this->namespace, '/' . $this->base_type, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_unit_types' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $this->base_type . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_unit_type' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
         */
    }

    public function get_units_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }



    public function get_member_units($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_unit";
        $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
        $results = $wpdb->get_results($searchsql);
    /*
    foreach($results as $ship_model) {
      $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $unit->id;
      $unit_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($unit_ids as $p) {
        array_push($ids, $p->id);
      }
      $ship_model->unit_ids = $ids;
    }
     */
        //     return array('units' => $results);
        $response = rest_ensure_response( array('member_units' => $results) );
        //     $response->header( 'Content-Type', "application/json" );
        return $response;
    }


    public function get_member_unit($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_name = $wpdb->prefix . "ot_member_unit";
        $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
        $assignment = $wpdb->get_row($searchsql);

        if ( null !== $assignment ) {
            return array('member_unit' => $assignment);
        } else { 
            return new WP_Error( 'error', __( 'member unit not found' ), array( 'status' => 404 ) );
        }
    }

    public function create_member_unit($request) {
        // why do I have to do this?? missing arg? WP API borken?
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['memberUnit'] ) ) {
            $data = $data["memberUnit"];
        }

        //		  return new WP_Error( 'error', __( array("create" => $data) ), array( 'status' => 404 ) );
        //     $data["member"] = $data["member_id"];
        //     unset($data["member_id"]);
        //     $data["unit"] = $data["unit_id"];
        //     unset($data["unit_id"]);


        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_unit";
        $res = $wpdb->insert($table_name, $data);

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return $this->get_member_unit( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'unit not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_member_unit($request) {
        $id = (int) $request['id'];

        $item = $this->get_member_unit( array("id" => $id) );

        if ( empty( $id ) || empty( $item["member_unit"]->id ) ) {
            return new WP_Error( 'error', __( $item  ), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );

        //		  return new WP_Error( 'error', __( array("update" => $data) ), array( 'status' => 404 ) );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_unit";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_member_unit( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update member_unit error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }

    public function delete_member_unit($request) {
        $id = (int) $request['id'];
        $unit = $this->get_member_unit( array("id" => $id), false);
        //       return array("unit" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);

        if ( empty( $id ) || empty($unit["member_unit"]) ||  empty( $unit["member_unit"]->id ) ) {
            return new WP_Error( 'error', __( 'member unit not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_unit";
        $res = $wpdb->delete($table_name, array('id' => $id));

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return array(); //$this->get_member_unit( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'unit not created' ), array( 'status' => 400 ) );
        }
    }


}

?>
