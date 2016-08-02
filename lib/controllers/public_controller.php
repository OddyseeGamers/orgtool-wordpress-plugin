<?php

class Orgtool_API_Public extends WP_REST_Controller
{

	protected $namespace = 'orgtool';
//     private $base = 'members';

//     public function __construct() {
//     }

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		//$base = $this->get_post_type_base( $this->post_type );
		//$base = $this->type;

		register_rest_route($this->namespace, '(?P<id>[\w]+)/members', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_members' ),
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
			$member->ships = $ids;

			$sql = 'SELECT id FROM ' . $table_member . ' WHERE member = ' . $member->id;
			$unit_ids = $wpdb->get_results( $sql);

			$ids = array();
			foreach($unit_ids as $p) {
				array_push($ids, $p->id);
			}
			$member->memberUnits = $ids;
			unset($member["id"]);
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

?>
