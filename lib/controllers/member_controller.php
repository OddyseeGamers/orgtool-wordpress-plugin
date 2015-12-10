<?php

class Orgtool_API_Member  {
  public function register_routes( $routes ) {
    $routes['/orgtool/members'] = array(
      array( array( $this, 'get_members'), WP_JSON_Server::READABLE ),
      array( array( $this, 'create_member'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
    );
    $routes['/orgtool/members/(?P<id>\d+)'] = array(
      array( array( $this, 'get_member'), WP_JSON_Server::READABLE ),
      array( array( $this, 'update_member'), WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON ),
      array( array( $this, 'delete_member'), WP_JSON_Server::DELETABLE ),
    );

    return $routes;
  }


  public function get_members($_headers) {
    global $wpdb;
    $table_name = $wpdb->prefix . "ot_member";
    $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
    $results = $wpdb->get_results($searchsql);
    /*
    foreach($results as $member) {
      $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $member->id;
      $member_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($member_ids as $p) {
        array_push($ids, $p->id);
      }
      $member->member_ids = $ids;
    }
     */
    return array('members' => $results);
  }


  public function get_member($id, $details = true) {
    global $wpdb;
    $id = (int) $id;
    $table_name = $wpdb->prefix . "ot_member";
    $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
    $member = $wpdb->get_row($searchsql);

    if ( null !== $member ) {
      /*
      if ($details) {
        $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $id;
        $member_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($member_ids as $p) {
          array_push($ids, $p->id);
        }
        $member->member_ids = $ids;
        return array('member' => $member);
      } else {
        return $member;
      }
       */
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

