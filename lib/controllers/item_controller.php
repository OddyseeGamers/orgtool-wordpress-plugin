<?php

class Orgtool_API_Item
{
    protected $namespace = 'orgtool';
    private $base = 'items';
    private $base_type = 'item_types';

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



        register_rest_route($this->namespace, '/' . $this->base_type, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item_types' ),
//                 'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item_type' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
        ) );

        register_rest_route($this->namespace, '/' . $this->base_type . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item_type' ),
//                 'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item_type' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_item_type' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => array(
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
        $table_item = $wpdb->prefix . "ot_item";
        $searchsql = 'SELECT * FROM ' . $table_item . ' order by id';
        $results = $wpdb->get_results($searchsql);

        foreach($results as $item) {
            $item->items = $this->get_items_for_item($item->id);
            $item->item_props = $this->get_itemprops_for_item($item->id);
        }

        //rest_get_server()->send_header("Content-Type", "application/json");
//         @header( "Content-Type: application/json" );
//         error_log(">> header: " . $_SERVER['Content-Type']);

        //	return array('items' => array( 'fata' => 'latal') ) ;
        return new WP_REST_Response( array('items' => $results), 200 );
        //	return rest_ensure_response( array('items' => $results) );
    }


    public function get_item($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_item = $wpdb->prefix . "ot_item";
        $searchsql = 'SELECT * FROM ' . $table_item . ' where id = '. $id;
        $item = $wpdb->get_row($searchsql);

        if ( null !== $item ) {
            $item->items = $this->get_items_for_item($item->id);
            $item->item_props = $this->get_itemprops_for_item($item->id);
            return array('item' => $item);
        } else { 
            return new WP_Error( 'error', __( 'item not found' ), array( 'status' => 404 ) );
        }
    }

    private function get_itemprops_for_item($itemid) {
        global $wpdb;
        $table_prop = $wpdb->prefix . "ot_item_prop";
        $sql = 'SELECT id FROM ' . $table_prop . ' WHERE item = ' . $itemid;
        $prop_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($prop_ids as $p) {
            array_push($ids, $p->id);
        }
        return $ids;
    }

    private function get_items_for_item($itemid) {
        global $wpdb;
        $table_prop = $wpdb->prefix . "ot_item";
        $sql = 'SELECT id FROM ' . $table_prop . ' WHERE parent = ' . $itemid;
        $prop_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($prop_ids as $p) {
            array_push($ids, $p->id);
        }
        return $ids;
    }

    /////////////////////////////////

    public function create_item($request) {
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['item'] ) ) {
            $data = $data["item"];
        }

        // if !member || !model > error...

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item";
        $res = $wpdb->insert($table_name, $data);
        if (null !== $res) {
            return $this->get_item( array("id" => $wpdb->insert_id ));
        } else {
            return new WP_Error( 'error', __( 'item not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_item($request) {
        $id = (int) $request['id'];
        $item = $this->get_item( array("id" => $id), false );

        if ( empty( $id ) || empty( $item["item"]->id ) ) {
            return new WP_Error( 'error', __( 'item not found 3 '), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_item( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update item error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }


    public function delete_item($request) {
        $id = (int) $request['id'];
        $item = $this->get_item( array("id" => $id), false);

        if ( empty( $id ) || empty( $item["item"]->id ) ) {
            return new WP_Error( 'error', __( 'item not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item";
        $res = $wpdb->delete($table_name, array('id' => $id));
        return array();
    }


    /*
  public function create_ship($request) {
    $data = json_decode( $request->get_body(), true );
    if ( ! empty( $data['ship'] ) ) {
      $data = $data["ship"];
    }

    // if !member || !model > error...

    global $wpdb;
    $table_name = $wpdb->prefix . "ot_ship";
    $res = $wpdb->insert($table_name, $data);
    if (null !== $res) {
       return $this->get_ship( array("id" => $wpdb->insert_id ));
    } else {
      return new WP_Error( 'error', __( 'ship not created' ), array( 'status' => 400 ) );
    }
  }

  public function create_ship_model($request) {

    $data = array();

    if ( ! empty( $request['unit'] ) ) {
      $data = $request["unit"];
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
       return $this->get_unit( $wpdb->insert_id );
    } else {
      return new WP_Error( 'error', __( 'unit not created' ), array( 'status' => 400 ) );
    }
  }


  public function update_ship_model($request) {
      $id = (int) $request['id'];

      $ship_model = $this->get_ship_model( $id, false );

      if ( empty( $id ) || empty( $ship_model->id ) ) {
          return new WP_Error( 'error', __( 'unit not found 3 '), array( 'status' => 404 ) );
      }

      $data = array();
//       $data['name'] = $request['name'];
//       $data['description'] = $request['description'];
//       $data['color'] = $request['color'];
//       $data['img'] = $request['img'];
//       $data['parent'] = $request['parent_id'];
//       $data['type'] = $request['type_id'];

//     $data["parent"] = $data["parent_id"];
//     unset($data["parent_id"]);
//     $data["type"] = $data["type_id"];
//     unset($data["type_id"]);

    global $wpdb;
    $table_name = $wpdb->prefix . "ot_ship_model";
    $res = $wpdb->update($table_name, $data, array( 'id' => $id));
    if (false !== $res ) {
        return $this->get_ship_model( $id );
    } else {
        return new WP_Error( 'error', __( 'update ship_model error ' . $res->last_error), array( 'status' => 404 ) );
    }
  }

  public function delete_ship_model($request) {
      $id = (int) $request['id'];
      $unit = $this->get_ship_model( $id , false);

      if ( empty( $id ) || empty( $unit->id ) ) {
          return new WP_Error( 'error', __( 'ship_model not found 2 '), array( 'status' => 404 ) );
      }
      global $wpdb;
      $table_name = $wpdb->prefix . "ot_ship_model";
      $res = $wpdb->delete($table_name, array('id' => $id));
  }

     */

    //////////////////////////////////////////////////////////////

    public function get_item_types($request) {
        global $wpdb;
        $table_type = $wpdb->prefix . "ot_item_type";
        $searchsql = 'SELECT * FROM ' . $table_type . ' order by id';
        $results = $wpdb->get_results($searchsql);

        foreach($results as $item_type) {
            $item_type->items = $this->get_items_for_type($item_type->id);
        }

        return rest_ensure_response( array('item_types' => $results) );
    }


    public function get_item_type($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_type = $wpdb->prefix . "ot_item_type";
        $searchsql = 'SELECT * FROM ' . $table_type . ' where id = '. $id;
        $item_type = $wpdb->get_row($searchsql);

        if ( null !== $item_type ) {
            $item_type->items = $this->get_items_for_type($item_type->id);
            return array('item_type' => $item_type);
        } else { 
            return new WP_Error( 'error', __( 'item_type not found' ), array( 'status' => 404 ) );
        }
    }

    private function get_items_for_type($itemid) {
        global $wpdb;
        $table_item = $wpdb->prefix . "ot_item";
        $sql = 'SELECT id FROM ' . $table_item . ' WHERE type = ' . $itemid;
        $item_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($item_ids as $p) {
            array_push($ids, $p->id);
        }
        return $ids;
    }


    public function create_item_type($request) {
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['itemType'] ) ) {
            $data = $data["itemType"];
        }

        // if !member || !model > error...

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item_type";
        $res = $wpdb->insert($table_name, $data);
        if (null !== $res) {
            return $this->get_item_type( array("id" => $wpdb->insert_id ));
        } else {
            return new WP_Error( 'error', __( 'item type not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_item_type($request) {
        $id = (int) $request['id'];
        $itemType = $this->get_item_type( array("id" => $id), false );

        if ( empty( $id ) || empty( $itemType["item_type"]->id ) ) {
            return new WP_Error( 'error', __( 'item type not found 3 :' . $id ), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item_type";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_item_type( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update item type error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }

    public function delete_item_type($request) {
        $id = (int) $request['id'];
        $item_type = $this->get_item_type( array("id" => $id), false);

        //	  return new WP_Error( 'error', __( 'item_type not found  ' . $id ), array( 'status' => 404 ) );

        if ( empty( $id ) || empty( $item_type["item_type"]->id ) ) {
            return new WP_Error( 'error', __( 'item_type not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_item_type";
        $res = $wpdb->delete($table_name, array('id' => $id));
        return array();
    }

}

?>
