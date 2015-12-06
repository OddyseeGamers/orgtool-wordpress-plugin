<?php

class JSON_API_Orgtool_Controller {

  public function members() {
	  global $wpdb;
	  $table_name = $wpdb->prefix . "ot_member";
	  $results = $wpdb->get_results('SELECT * FROM ' . $table_name);
	  wp_send_json(array('members' => $results));
  }


  public function ship_models() {
	  global $wpdb;
	  $table_name = $wpdb->prefix . "ot_ship_model";
	  $results = $wpdb->get_results('SELECT * FROM ' . $table_name);
	  wp_send_json(array('ship_models' => $results));
  }

/*
  public function units() {
	  global $wpdb;
	  $table_name = $wpdb->prefix . "ot_unit";
	  $results = $wpdb->get_results('SELECT * FROM ' . $table_name . ' order by id');
	  foreach($results as $idx => $unit) {
		  $unit_ids = $wpdb->get_row( 'SELECT id FROM ' . $table_unit . ' WHERE parent = "' . $unit["id"] . '"');
//		  array_push($unit, array('unit_ids' => $unit_ids));
	  }
	  wp_send_json(array('units' => $results));
  }
 */
  public function units($id) {
	  global $json_api;
	  global $wpdb;
	  $table_name = $wpdb->prefix . "ot_unit";
	  $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';

	  if ($json_api->query->id) {
		  $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $json_api->query->id;
		  $results = $wpdb->get_row($searchsql);
		  //               $json_api->error("Missing 'id' parameter.");
		  //               wp_send_json(array('query' => $json_api->query));
		  wp_send_json(array('unit' => $results));
	  }

	  $results = $wpdb->get_results($searchsql);
	  //       wp_send_json(array('query' => $json_api->query));
	  foreach($results as $unit) {
		  //               wp_die( __( 'ooops ' . $unit ));
		  //               wp_send_json($unit->id);
		  //               if ($unit->id == "2") {
		  $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $unit->id;
		  $unit_ids = $wpdb->get_results( $sql);

		  $ids = array();
		  foreach($unit_ids as $p) {
			  array_push($ids, $p->id);
		  }
		  $unit->unit_ids = $ids;
		  //wp_send_json(array('size' => sizeof($unit_ids), "name" => $unit->name, 'sql' => $sql));
		  //                       wp_send_json($unit);
		  //             }
		  //                       wp_send_json(array('size' => sizeof($unit_ids)));
		  //                       wp_send_json(array('unit_ids' => $unit_ids));
		  //               } else {
		  //                     wp_die( __( 'ooops ' ) );
		  //               }
		  //               array_push($unit, array('unit_ids' => $unit_ids));
	  }
	  wp_send_json(array('units' => $results));
  }



}

?>

