<?php

class Orgtool_API_ItemProp extends WP_REST_Controller
{
	protected $namespace = 'orgtool';
	private $base = 'item_props';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item_props' ),
				'permission_callback' => array( $this, 'get_itemprops_permissions_check' ),
			),
//             array(
//                 'methods'         => WP_REST_Server::CREATABLE,
//                 'callback'        => array( $this, 'create_member_unit' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
//             ),
		) );

		register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item_prop' ),
				'permission_callback' => array( $this, 'get_itemprops_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
        /*
//             array(
//                 'methods'         => WP_REST_Server::EDITABLE,
//                 'callback'        => array( $this, 'update_ship_model' ),
//                 'permission_callback' => array( $this, 'get_units_permissions_check' ),
//             ),
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
         */
		) );
	}

	public function get_itemprops_permissions_check( $request ) {
/*
		$post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
		}
*/
		return true;
	}



    public function get_item_props($request) {
        global $wpdb;
        $table_item_prop = $wpdb->prefix . "ot_item_prop";
        $searchsql = 'SELECT * FROM ' . $table_item_prop . ' order by id';
        $results = $wpdb->get_results($searchsql);

        return rest_ensure_response( array('item_props' => $results) );
    }


    public function get_item_prop($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_item_prop = $wpdb->prefix . "ot_item_prop";
        $searchsql = 'SELECT * FROM ' . $table_item_prop . ' where id = '. $id;
        $item_prop = $wpdb->get_row($searchsql);

        if ( null !== $item_prop ) {
            return array('item_prop' => $item_prop);
        } else { 
            return new WP_Error( 'error', __( 'itpem_prop not found' ), array( 'status' => 404 ) );
        }
    }

  /*
  public function create_member_unit($request) {
	// why do I have to do this?? missing arg? WP API borken?
	$data = json_decode( $request->get_body(), true );
	if ( ! empty( $data['memberUnit'] ) ) {
	  $data = $data["memberUnit"];
	}

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
     */

}

?>
