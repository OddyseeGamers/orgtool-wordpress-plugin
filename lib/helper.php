<?php

function insertOrUpdateShip($ship) {
	global $wpdb;

	$mname = $ship['mname'];
	$mimg = $ship['mimg'];
	$table_manu = $wpdb->prefix . "ot_ship_manufacturer";
	$result = $wpdb->get_row( 'SELECT * FROM ' . $table_manu . ' WHERE name = "' . $mname . '"');
	if(isset($result->id)) {
		$ship["manufacturer"] = $result->id;
	} else {
		$res = $wpdb->insert($table_manu, array( "name" => $mname, "img" => $mimg ));
		$ship["manufacturer"] = $wpdb->insert_id;
	}

	$sclass = $ship['class'];
	$table_class = $wpdb->prefix . "ot_ship_class";
	$result = $wpdb->get_row( 'SELECT * FROM ' . $table_class . ' WHERE name = "' . $sclass . '"');
	if(isset($result->id)) {
		$ship["class"] = $result->id;
	} else {
		$res = $wpdb->insert($table_class, array( "name" => $sclass));
		$ship["class"] = $wpdb->insert_id;
	}

	unset($ship['mname']);
	unset($ship['mimg']);

	$table_ship = $wpdb->prefix . "ot_ship_model";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_ship . ' WHERE id = "' . $ship["id"] . '"');

	if(isset($results->id)) {
		error_log("ship update " . $ship["id"] . " | " . $ship["name"]);
		$wpdb->update($table_ship, $ship, array( 'id' => $ship["id"]));

	} else {
		error_log("ship insert " . $ship["id"] . " | " . $ship["name"]);
		$wpdb->insert($table_ship, $ship);
	}
}



function insertOrUpdateMember($user) {
	global $wpdb;

	$handle = $user["handle"];
	$wp_id = $wpdb->get_var("select d.user_id from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where name=\"handle\" and value=\"$handle\"");

	if ($wp_id) {
		$user["wp_id"] = $wp_id;
		error_log(" handle : " . $handle . "  found for user " . $wp_id );
		$tz = $wpdb->get_var("select d.value from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where d.user_id=$wp_id and name=\"timezone\"");
		if ($tz) {
			$user["timezone"] = (int)$tz;
		}
	}

	$table_name = $wpdb->prefix . "ot_member";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE handle = "' . $user["handle"] .'"');

	if(isset($results->handle)) {
		error_log("update " . $user["handle"] . " | " . $user["name"]);
		$wpdb->update($table_name, $user, array( 'handle' => $user["handle"]));

	} else {
		error_log("insert " . $user["handle"] . " | " . $user["name"]);
		$wpdb->insert($table_name, $user);
	}
}

function insertOrUpdateUnit($unit) {
	global $wpdb;

	$table_unit = $wpdb->prefix . "ot_unit";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_unit . ' WHERE id = "' . $unit["id"] . '"');

	unset($unit['leader_ids']);
	unset($unit['pilot_ids']);
	unset($unit['unit_ids']);

	if(isset($results->id)) {
		error_log("unit update " . $unit["id"] . " | " . $unit["name"]);
		$wpdb->update($table_unit, $unit, array( 'id' => $unit["id"]));

	} else {
		error_log("unit insert " . $unit["id"] . " | " . $unit["name"]);
		$wpdb->insert($table_unit, $unit);
	}
}


function insertOrUpdateUnitType($type) {
	global $wpdb;

	$table_type = $wpdb->prefix . "ot_unit_type";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_type . ' WHERE id = "' . $type["id"] . '"');

	if(isset($results->id)) {
		error_log("unit type update " . $type["id"] . " | " . $type["name"]);
		$wpdb->update($table_type, $type, array( 'id' => $type["id"]));

	} else {
		error_log("unit type insert " . $type["id"] . " | " . $type["name"]);
		$wpdb->insert($table_type, $type);
	}
}

function insertOrUpdateShips() {
	global $wpdb;
	$table_name = $wpdb->prefix . "ot_member";
	$pdata = $wpdb->prefix . "bp_xprofile_data";
	$pfields = $wpdb->prefix . "bp_xprofile_fields";

	$table_model = $wpdb->prefix . "ot_ship_model";

	$sql = 'SELECT * FROM ' . $table_name . ' order by id';
	$members = $wpdb->get_results($sql);

	foreach($members as $mem) {
		if (!empty($mem->wp_id)) {

			$sql = 'SELECT fil.id, fil.name, dat.value FROM ' . $pdata . ' as dat ' .
					'  right join ' . $pfields . ' as fil on fil.id = dat.field_id ' .
					'  where fil.group_id = 3' .
					'  and dat.user_id = ' . $mem->wp_id;
			$ships = $wpdb->get_results($sql);
			foreach($ships as $ship) {
				$sql = 'SELECT id FROM ' . $table_model . ' where name like "%' . $ship->name . '%"';
				$mod = $wpdb->get_var($sql);
				if (null !== $mod) {
					$ship->model_id = $mod;
//                     error_log(" ---> " . $ship->name . ' -> found: ' . $mod);
				} else {
					error_log(" ---> error, unknow ship" . $ship->name);
				}
			}
			
			if (sizeof($ships) > 0) {
//                 error_log("updating member wp id " . $mem->wp_id);
				insertOrUpdateMemberShips($mem, $ships);
			}
		}
	}
}

function insertOrUpdateMemberShips($user, $ships) {
//     error_log(" >> update user " . $user->id . ', ships: ' . sizeof($ships));
	global $wpdb;
	$table_name = $wpdb->prefix . "ot_ship";
	foreach($ships as $ship) {
		if ( !empty($ship->model_id) && !empty($ship->value) && $ship->value > 0) {
			$sql = 'SELECT count(*) FROM ' . $table_name . ' WHERE member = ' . $user->id . ' and model = ' . $ship->model_id;
			$count = $wpdb->get_var($sql);
			if ($count < $ship->value) {
				$diff = $ship->value - $count;
				for ($i = $count; $i < $ship->value; $i++) {
					error_log("         - " . $ship->name . " id  " . $ship->model_id . "  count: " . $i);
//                     $wpdb->insert($table_name, array("name" => $ship->name, "model" => $ship->model_id, "member" => $user->id));
				}
			}

		}
	}
}


?>
