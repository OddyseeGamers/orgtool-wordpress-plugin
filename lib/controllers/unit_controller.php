<?php

class Orgtool_API_Unit
{
    protected $namespace = 'orgtool';
    private $base = 'units';
    private $base_type = 'unit_types';

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
                'callback'        => array( $this, 'get_units' ),
                //                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_unit' ),
                //                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_unit' ),
                'permission_callback' => array( $this, 'get_units_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

        register_rest_route($this->namespace, '/' . $this->base_type, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_unit_types' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $this->base_type . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_unit_type' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
            ),
        ) );
    }

    public function get_units_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id || !user_can($user_id, 'administrator')) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
/*
        $post_type = get_post_type_object( $this->post_type );

        if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }
 */
        return true;
    }



    public function get_units($_headers) {
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_unit";
        $table_member = $wpdb->prefix . "ot_member_unit";
        $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
        $results = $wpdb->get_results($searchsql);
        foreach($results as $unit) {
            $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $unit->id;
            $unit_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($unit_ids as $p) {
                array_push($ids, $p->id);
            }
            $unit->units = $ids;

            $sql = 'SELECT id FROM ' . $table_member . ' WHERE unit = ' . $unit->id;
            $member_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($member_ids as $p) {
                array_push($ids, $p->id);
            }
            $unit->memberUnits = $ids;

        }
        //     return array('units' => $results);
        $response = rest_ensure_response( array('units' => $results) );
        //     $response->header( 'Content-Type', "application/json" );
        return $response;
    }


    public function get_unit($request, $details = true) {
        global $wpdb;
        $id = (int) $request['id'];

        $table_name = $wpdb->prefix . "ot_unit";
        $table_member = $wpdb->prefix . "ot_member_unit";
        $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
        $unit = $wpdb->get_row($searchsql);

        if ( null !== $unit ) {
            if ($details) {
                $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $id;
                $unit_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($unit_ids as $p) {
                    array_push($ids, $p->id);
                }
                $unit->units = $ids;

                $sql = 'SELECT id FROM ' . $table_member . ' WHERE unit = ' . $unit->id;
                $member_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($member_ids as $p) {
                    array_push($ids, $p->id);
                }
                $unit->memberUnits = $ids;

                return array('unit' => $unit);
            } else {
                return $unit;
            }
        } else { 
            return new WP_Error( 'error', __( 'unit not found' ), array( 'status' => 404 ) );
        }
    }


    public function create_unit($request) {

        // why do I have to do this?? missing arg? WP API borken?
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['unit'] ) ) {
            $data = $data["unit"];
            //       unset($data["members"]);
        }

        //     $data["parent"] = $data["parent_id"];
        //     unset($data["parent_id"]);
        //     $data["type"] = $data["type_id"];
        //     unset($data["type_id"]);


        global $wpdb;
        $table_name = $wpdb->prefix . "ot_unit";
        $res = $wpdb->insert($table_name, $data);

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return $this->get_unit( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'unit not created' ), array( 'status' => 400 ) );
        }
    }


    public function update_unit(WP_REST_Request $request) {
        $id = (int) $request['id'];
        $unit = $this->get_unit( array("id" => $id), false );

        if ( empty( $id ) || empty( $unit->id ) ) {
            return new WP_Error( 'error', __( 'unit not found 3 '), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['unit'] ) ) {
            $data = $data["unit"];
        }
        //       $data["parent"] = $data["parent_id"];
        //       unset($data["parent_id"]);
        //       $data["type"] = $data["type_id"];
        //       unset($data["type_id"]);

/*
    if ( isset( $_headers['IF_UNMODIFIED_SINCE'] ) ) {
      // As mandated by RFC2616, we have to check all of RFC1123, RFC1036
      // and C's asctime() format (and ignore invalid headers)
      $formats = array( DateTime::RFC1123, DateTime::RFC1036, 'D M j H:i:s Y' );
      foreach ( $formats as $format ) {
        $check = WP_JSON_DateTime::createFromFormat( $format, $_headers['IF_UNMODIFIED_SINCE'] );
        if ( $check !== false ) {
          break;
        }
      }
      // If the post has been modified since the date provided, return an error.
      if ( $check && mysql2date( 'U', $post['post_modified_gmt'] ) > $check->format('U') ) {
        return new WP_Error( 'json_old_revision', __( 'There is a revision of this post that is more recent.' ), array( 'status' => 412 ) );
      }
    }
 */
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_unit";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_unit( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update unit error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }

    public function delete_unit($request) {
        $id = (int) $request['id'];
        $unit = $this->get_unit( array("id" => $id), false);

        if ( empty( $id ) || empty( $unit->id ) ) {
            return new WP_Error( 'error', __( 'unit not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_unit";
        $res = $wpdb->delete($table_name, array('id' => $id));
    }



    //////////////////////////////////////////////////////////////


    public function get_unit_types($_headers) {
        global $wpdb;
        $table_unit = $wpdb->prefix . "ot_unit";
        $table_type = $wpdb->prefix . "ot_unit_type";
        $searchsql = 'SELECT * FROM ' . $table_type . ' order by id';
        $results = $wpdb->get_results($searchsql);

        foreach($results as $type) {
            $sql = 'SELECT id FROM ' . $table_unit . ' WHERE type = ' . $type->id;
            $unit_ids = $wpdb->get_results($sql);

            $ids = array();
            foreach($unit_ids as $p) {
                array_push($ids, $p->id);
            }
            $type->unit_ids = $ids;
        }
        return array('unit_types' => $results);
    }


    public function get_unit_type($id, $details = true) {
        global $wpdb;
        $id = (int) $id;
        $table_type = $wpdb->prefix . "ot_unit_type";
        $table_unit = $wpdb->prefix . "ot_unit";
        $searchsql = 'SELECT * FROM ' . $table_type . ' where id = '. $id;
        $type = $wpdb->get_row($searchsql);

        if ( null !== $type ) {
            if ($details) {
                $sql = 'SELECT id FROM ' . $table_unit . ' WHERE type = ' . $id;
                $unit_ids = $wpdb->get_results( $sql);

                $ids = array();
                foreach($unit_ids as $p) {
                    array_push($ids, $p->id);
                }
                $type->unit_ids = $ids;
                return array('unit_type' => $type);
            } else {
                return $type;
            }
        } else { 
            return new WP_Error( 'error', __( 'unit type not found' ), array( 'status' => 404 ) );
        }
    }

}

?>
