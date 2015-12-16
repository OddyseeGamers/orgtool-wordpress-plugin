<?php

class Orgtool_API_Member extends WP_REST_Controller
{

	private $namespace = 'orgtool';
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
//			array(
//				'methods'         => WP_REST_Server::CREATABLE,
//				'callback'        => array( $this, 'create_unit' ),
//				'permission_callback' => array( $this, 'get_units_permissions_check' ),
//			),
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
//			array(
//				'methods'         => WP_REST_Server::EDITABLE,
//				'callback'        => array( $this, 'update_unit' ),
//				'permission_callback' => array( $this, 'get_units_permissions_check' ),
//			),
//			array(
//				'methods'  => WP_REST_Server::DELETABLE,
//				'callback' => array( $this, 'delete_unit' ),
//				'permission_callback' => array( $this, 'get_units_permissions_check' ),
//				'args'     => array(
//					'force'    => array(
//						'default'      => false,
//					),
//				),
//			),
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
    $results = $wpdb->get_results($searchsql);

    $table_ship = $wpdb->prefix . "ot_ship";
    foreach($results as $member) {
      $sql = 'SELECT id FROM ' . $table_ship . ' WHERE member = ' . $member->id;
      $ship_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($ship_ids as $p) {
        array_push($ids, $p->id);
      }
      $member->ship_ids = $ids;

		$sql = 'SELECT unit FROM ' . $table_member . ' WHERE member = ' . $member->id;
		$unit_ids = $wpdb->get_results( $sql);

		$ids = array();
		foreach($unit_ids as $p) {
			array_push($ids, $p->unit);
		}
		$member->unit_ids = $ids;
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
		  $member->ship_ids = $ids;

		$sql = 'SELECT unit FROM ' . $table_member . ' WHERE member = ' . $member->id;
		$unit_ids = $wpdb->get_results( $sql);

		$ids = array();
		foreach($unit_ids as $p) {
			array_push($ids, $p->unit);
		}
		$member->unit_ids = $ids;

        return array('member' => $member);
      } else {
        return $member;
      }

        return array('member' => $member);
    } else { 
      return new WP_Error( 'error', __( 'member not found' ), array( 'status' => 404 ) );
    }
  }


  public function create_member($data = "", $_headers = array() ) {
    if (array_key_exists("member", $data)) {
      $data = $data["member"];
//       unset($data["members"]);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $res = $wpdb->insert($table_name, $data);
    return $this->get_member( $wpdb->insert_id );
  }


  public function update_member( $id, $data = "", $_headers = array() ) {
    $id = (int) $id;
    $member = $this->get_member( $id, false );

    if ( empty( $id ) || empty( $member->id ) ) {
      return new WP_Error( 'error', __( 'member not found 3 '), array( 'status' => 404 ) );
    }

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
    $table_name = $wpdb->prefix . "ot_member";
    $res = $wpdb->update($table_name, $data, array( 'id' => $id));
    return $this->get_member( $id );
  }

  public function delete_member($id, $force = false) {
    $id = (int) $id;
    $member = $this->get_member( $id , false);

    if ( empty( $id ) || empty( $member->id ) ) {
      return new WP_Error( 'error', __( 'member not found 2 '), array( 'status' => 404 ) );
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $res = $wpdb->delete($table_name, array('id' => $id));
  }
}

?>
