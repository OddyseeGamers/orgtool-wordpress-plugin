<?php



function mergeWPMembers($rsimembers) {
	global $wpdb;
	$sql= "select d.user_id as wp_id, u.name, d.value as handle "
		. "from wp_users as u "
		. "left join wp_bp_xprofile_data as d on u.ID = d.user_id "
		. "where d.field_id = 2";
	$members = $wpdb->get_results($sql);

	error_log(" >> update user " . sizeof($members));

	$memtbl = $wpdb->prefix . "ot_member";
	foreach($members as $mem) {
		$otmem = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE wp_id = "' . $mem["wp_id"] .'"');

		error_log(" >> update user " . $mem->wp_id . ', res: ' . sizeof($tomem));

		if(!isset($otmem->wp_id)) {
			error_log(" >> inser new user " . $mem->wp_id);

//             $ot_id = $wpdb->insert($memtbl, otmem);
//             $ship["class"] = $wpdb->insert_id;
		} else {
			error_log(" >> update user " . $mem->wp_id);
//             $ship["class"] = $result->id;
		}

//         $res = $wpdb->update($memtbl, $data, array( 'id' => $id));
	}
}








function getOldStruc() {
	return array(0 => "ODDYSEE", 1 => "LID", 2 => "Operations", 3 => "Trade Logistics", 4 => "Tech Salvage", 5 => "Trading", 6 => "Mining", 7 => "Logistics", 8 => "Base Operations", 9 => "Salvage", 10 => "Boarding", 11 => "Technology", 12 => "Ordinance", 13 => "Operations", 14 => "Intel", 15 => "Public Relations", 16 => "Contracts", 17 => "Racing", 18 => "Recruiting", 19 => "Pathfinder", 20 => "Cartography", 21 => "Navigation", 22 => "Operations", 23 => "SOD", 24 => "1st Fleet", 25 => "Light Fighters", 26 => "Heavy Fighters", 27 => "Assault/ Bombers", 28 => "Recon", 29 => "Gunships/ Transports", 30 => "Capital Ships Command", 31 => "2nd Fleet", 32 => "Light Fighters", 33 => "Heavy Fighters", 34 => "Assault/ Bombers", 35 => "Recon", 36 => "Gunships/ Transports", 37 => "Capital Ships Command", 38 => "Marine Command", 39 => "Marine Squads");
}

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

function importShipsAndAssign() {
	global $wpdb;
	$table_name = $wpdb->prefix . "ot_member";
	$table_model = $wpdb->prefix . "ot_ship_model";
	$table_unit = $wpdb->prefix . "ot_unit";
	$table_assin = $wpdb->prefix . "rsi_users";

	$pdata = $wpdb->prefix . "bp_xprofile_data";
	$pfields = $wpdb->prefix . "bp_xprofile_fields";


	$sql = 'SELECT * FROM ' . $table_name . ' order by id';
	$members = $wpdb->get_results($sql);

	$oldstruc = getOldStruc();
	foreach($members as $mem) {
		if (!empty($mem->handle)) {
			$sql = 'SELECT unit FROM ' . $table_assin . ' where handle = "' . $mem->handle . '"';
			$unitid = $wpdb->get_var($sql);
			if ($unitid > 0) {
				$match = array_key_exists($unitid, $oldstruc);
				if ($match == 1) {
					$sql = 'SELECT id FROM ' . $table_unit . ' where name like "%' . $oldstruc[$unitid] . '%"';
					$newunitid = $wpdb->get_var($sql);
					if ($newunitid > 0) {
						insertOrUpdateMemberAssign($mem, $newunitid);
					} else {
						error_log("#### old unit " . $unitid . ' not found');
					}
				}
			}
		}
/*
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
				} else {
					error_log(" ---> error, unknow ship" . $ship->name);
				}
			}
			
			if (sizeof($ships) > 0) {
				insertOrUpdateMemberShips($mem, $ships);
			}
		}
 */
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

function insertOrUpdateMemberAssign($user, $unitid) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ot_member_unit";

	$sql = 'SELECT count(*) FROM ' . $table_name . ' WHERE member = ' . $user->id . ' and unit = ' . $unitid;
	$count = $wpdb->get_var($sql);

	if ($count == 0) {
		error_log("---- asign user  " . $user->name . ' to ' . $unitid);
//         $wpdb->insert($table_name, array("member" => $user->id, "unit" => $unitid));
	} else {
		error_log("#### already asigned user  " . $user->name . ' to ' . $unitid);
	}

}


?>
