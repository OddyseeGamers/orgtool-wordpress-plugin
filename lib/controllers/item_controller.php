<?php

class Orgtool_API_Item extends WP_REST_Controller
{
	protected $namespace = 'orgtool';
	private $base = 'item';
	private $base_type = 'item_type';

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
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );
        /*
		register_rest_route($this->namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
		) );

		register_rest_route($this->namespace, '/' . $this->base_class, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item_types' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );
		register_rest_route($this->namespace, '/' . $this->base_class . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item_type' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
		) );
         */
	}

	public function get_items_permissions_check( $request ) {
/*
		$post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
		}
*/
		return true;
	}



  public function get_items($request) {
    global $wpdb;
    $table_item = $wpdb->prefix . "ot_item";
    $searchsql = 'SELECT * FROM ' . $table_item . ' order by id';
    $results = $wpdb->get_results($searchsql);

    $table_prop = $wpdb->prefix . "ot_item_prop";
    foreach($results as $item) {
      $sql = 'SELECT id FROM ' . $table_prop . ' WHERE item = ' . $item->id;
      $item_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($item_ids as $p) {
        array_push($ids, $p->id);
      }
      $item->items = $ids;
	}

//     return array('units' => $results);
	$response = rest_ensure_response( array('items' => $results) );
//     $response->header( 'Content-Type', "application/json" );
	return $response;
  }

    /*

  public function get_ship_model($request) {
    global $wpdb;
	  $id = (int) $request['id'];
    $table_name = $wpdb->prefix . "ot_ship_model";
    $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
    $unit = $wpdb->get_row($searchsql);

//     $id = (int) $id;
    if ( null !== $unit ) {
		return array('ship_model' => $unit);
    } else { 
      return new WP_Error( 'error', __( 'unit not found' ), array( 'status' => 404 ) );
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



  //////////////////////////////////////////////////////////////

  public function get_ship_classes($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_ship_class";
    $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
    $results = $wpdb->get_results($searchsql);

    $table_ship = $wpdb->prefix . "ot_ship_model";
    foreach($results as $ship_class) {
      $sql = 'SELECT id FROM ' . $table_ship . ' WHERE class = ' . $ship_class->id;
      $ship_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($ship_ids as $p) {
        array_push($ids, $p->id);
      }
      $ship_class->ship_models = $ids;
	}

//     return array('units' => $results);
	$response = rest_ensure_response( array('ship_classes' => $results) );
//     $response->header( 'Content-Type', "application/json" );
	return $response;
  }


  public function get_ship_class($request) {
    global $wpdb;
	$id = (int) $request['id'];
    $table_name = $wpdb->prefix . "ot_ship_class";
    $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
    $ship_class = $wpdb->get_row($searchsql);

	if ( null !== $ship_class ) {
		$table_ship = $wpdb->prefix . "ot_ship_model";

		$sql = 'SELECT id FROM ' . $table_ship . ' WHERE class = ' . $ship_class->id;
		$ship_ids = $wpdb->get_results( $sql);

		$ids = array();
		foreach($ship_ids as $p) {
			array_push($ids, $p->id);
		}
		$ship_class->ship_models = $ids;

		return array('ship_class' => $ship_class);
	} else { 
		return new WP_Error( 'error', __( 'ship_class not found' ), array( 'status' => 404 ) );
	}
  }
*/

}

?>