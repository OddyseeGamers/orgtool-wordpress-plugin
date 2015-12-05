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


  public function units() {
          global $wpdb;
          $table_name = $wpdb->prefix . "ot_unit";
          $results = $wpdb->get_results('SELECT * FROM ' . $table_name);
          wp_send_json(array('unit' => $results);
  }
}

?>

