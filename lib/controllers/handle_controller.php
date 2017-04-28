<?php

class Orgtool_API_Handle
{
    protected $namespace = 'orgtool';
    private $base = 'handles';
    private $base_type = 'handles';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_items' ),
//                 'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
        ) );

        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item' ),
//                 'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

    }

    public function get_items_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            //         if (!$user_id || !user_can($user_id, 'administrator')) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }



    public function get_items($request) {
        global $wpdb;
        $table_item = $wpdb->prefix . "ot_handle";
        $searchsql = 'SELECT * FROM ' . $table_item . ' order by id';
        $results = $wpdb->get_results($searchsql);


        return new WP_REST_Response( array('handles' => $results), 200 );
    }


    public function get_item($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_item = $wpdb->prefix . "ot_handle";
        $searchsql = 'SELECT * FROM ' . $table_item . ' where id = '. $id;
        $item = $wpdb->get_row($searchsql);

        if ( null !== $item ) {
            return array('handle' => $item);
        } else { 
            return new WP_Error( 'error', __( 'handle not found' ), array( 'status' => 404 ) );
        }
    }


    /////////////////////////////////

    public function create_item($request) {
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['handle'] ) ) {
            $data = $data["handle"];
        }

        // if !member || !model > error...

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_handle";
        $res = $wpdb->insert($table_name, $data);
        if (null !== $res) {
            return $this->get_item( array("id" => $wpdb->insert_id ));
        } else {
            return new WP_Error( 'error', __( 'handle not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_item($request) {
        $id = (int) $request['id'];
        $item = $this->get_item( array("id" => $id), false );

        if ( empty( $id ) || empty( $item["handle"]->id ) ) {
            return new WP_Error( 'error', __( 'handle not found 3 '), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_handle";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_item( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update handle error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }


    public function delete_item($request) {
        $id = (int) $request['id'];
        $item = $this->get_item( array("id" => $id), false);

        if ( empty( $id ) || empty( $item["handle"]->id ) ) {
            return new WP_Error( 'error', __( 'handle not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_handle";
        $res = $wpdb->delete($table_name, array('id' => $id));
        return array();
    }


}

?>
