<?php

class Orgtool_API_Member extends WP_REST_Controller
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
				'permission_callback' => array( $this, 'get_members_permissions_check' ),
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
				'permission_callback' => array( $this, 'get_members_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
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
/*
		$post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
		}
*/
		return true;
	}


  public function get_members($_headers) {
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $table_member = $wpdb->prefix . "ot_member_unit";
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

    $table_ship = $wpdb->prefix . "ot_ship";
    foreach($results as $member) {
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
		$member->memberUnits = $ids;
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


  public function create_member($request) {

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


 //   return array("data" => $data, "rwa" => $request->get_body(), "json" =>$request->get_params());


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


  /*
	  log_error(">>>>> CREATE MEMBER " . sizeof($data));
	  return array();
    if (array_key_exists("member", $data)) {
      $data = $data["member"];
//       unset($data["members"]);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $res = $wpdb->insert($table_name, $data);
    return $this->get_member( $wpdb->insert_id );


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
   */


  public function update_member($request) { //  $id, $data = "", $_headers = array() ) {
	$data = json_decode( $request->get_body(), true );
//	if ( ! empty( $data['member'] ) || $data['member'] == [] ) {
//	  $data = $data["member"];
//	}

	$id = (int) $request['id'];
//return array("id" => $id, "data" => $data, "body" => $request->get_body());
//    $id = (int) $id;

	    global $wpdb;
	    $table_name = $wpdb->prefix . "ot_member";

        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
	if (false !== $res ) {
		return $this->get_member( array("id" => $id) );
	} else {
		return new WP_Error( 'error', __( 'update member error ' . $res->last_error), array( 'status' => 404 ) );
	}

/*
    $member = $this->get_member( $id, false );

    if ( empty( $id ) || empty( $member->id ) ) {
      return new WP_Error( 'error', __( 'member not found 3 '), array( 'status' => 404 ) );
    }
*/
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

/*
    $id = (int) $id;
    $member = $this->get_member( $id , false);

    if ( empty( $id ) || empty( $member->id ) ) {
      return new WP_Error( 'error', __( 'member not found 2 '), array( 'status' => 404 ) );
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $res = $wpdb->delete($table_name, array('id' => $id));
*/
  }
}

?>
