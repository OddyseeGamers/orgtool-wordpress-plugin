<?php

class Orgtool_API_Ship extends WP_REST_Controller
{
	private $namespace = 'orgtool';
	private $base = 'ships';
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
				'callback'        => array( $this, 'get_ships' ),
				'permission_callback' => array( $this, 'get_units_permissions_check' ),
			),
//             array(
//                 'methods'         => WP_REST_Server::CREATABLE,
//                 'callback'        => array( $this, 'create_ship_model' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
//             ),
		) );
		register_rest_route($this->namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_ship' ),
				'permission_callback' => array( $this, 'get_units_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
//             array(
//                 'methods'         => WP_REST_Server::EDITABLE,
//                 'callback'        => array( $this, 'update_ship_model' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
//             ),
//             array(
//                 'methods'  => WP_REST_Server::DELETABLE,
//                 'callback' => array( $this, 'delete_ship_model' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
//                 'args'     => array(
//                     'force'    => array(
//                         'default'      => false,
//                     ),
//                 ),
//             ),
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
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
		) );
		 */
	}

	public function get_units_permissions_check( $request ) {
/*
		$post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
		}
*/
		return true;
	}



  public function get_ships($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_ship";
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
	$response = rest_ensure_response( array('ships' => $results) );
//     $response->header( 'Content-Type', "application/json" );
	return $response;
  }


  public function get_ship($request) {
    global $wpdb;
	$id = (int) $request['id'];
    $table_name = $wpdb->prefix . "ot_ship";
    $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
    $ship = $wpdb->get_row($searchsql);

    if ( null !== $ship ) {
		return $ship;
    } else { 
      return new WP_Error( 'error', __( 'ship not found' ), array( 'status' => 404 ) );
    }
  }
}

?>
