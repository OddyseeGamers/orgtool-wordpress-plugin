<?php

class JSON_API_Orgtool_Controller {

  public function members() {
	  global $wpdb;
	  $table_name = $wpdb->prefix . "ot_member";
	  $results = $wpdb->get_results('SELECT * FROM ' . $table_name);
	  error_log(">>> res", $results->length);
	  return $results;
  }

}

?>

