<?php

function getUserCaps() {
	return array("read_orgtool_overview", "edit_orgtool_overview", "delete_orgtool_overview",
		"read_orgtool_member_assignment", "edit_orgtool_member_assignment", "delete_orgtool_member_assignment",
		"read_orgtool_member_management", "edit_orgtool_member_management", "delete_orgtool_member_management",
		"read_orgtool_training", "edit_orgtool_training", "delete_orgtool_training",
		"read_orgtool_missions", "edit_orgtool_missions", "delete_orgtool_missions");
}

function getShipList() {
    return array(// Roberts Space Industries
                    "Aurora", "Aurora Essential (ES)","Aurora Marque (MR)","Aurora Clipper (CL)","Aurora Deluxe (LX)","Aurora Legionnaire (LN)",
                    "Bengal-class Carrier",
                    "Constellation Andromeda","Constellation Taurus","Constellation Aquila","Constellation Phoenix",
                    "Mover Transport","Pegasus-class Escort Carrier","X-7","Zeus","Orion",
                    // Origin Jumpworks
                    "300i","315p","325a","350r",
                    "600 Series","890 Jump","85x Runabout","M50","X3",
                    // Aegis Dynamics
                    "Avenger","Gladius","Idris-P","Idris-M","Javelin","Reclaimer","Redeemer","Retaliator","Terrapin","Vanguard",
                    // Drake Interplanetary
                    "Cutlass Blue","Cutlass Red","Cutlass Black","Caterpillar","Dragonfly","Herald",
                    // Anvil Aerospace
                    "Carrack","Crucible","Gladiator","Hurricane",
                    "F7A Hornet","F7C Hornet","F7C-M Super Hornet","F7C-R Hornet Tracker","F7C-S Hornet Ghost",
                    // Musashi Industrial & Starflight Concern",
                    "Freelancer", "Freelancer DUR","Freelancer MIS","Freelancer MAX",
                    "Hull A","Hull B","Hull C","Hull D","Hull E",
                    "Reliant","Starfarer","Starfarer Gemini",
                    // Kruger Intergalactic
                    "P-52 Merlin","P-72 Archimedes",
                    // Consolidated Outland
                    "Mustang Alpha","Mustang Beta","Mustang Delta","Mustang Gamma","Mustang Omega",
                    // Vanduul
                    "Scythe","Glaive",
                    // Xi'An
                    "Khartu",
                    // Banu
                    "Merchantman");
}

function getUsers2() {
	global $wpdb;
	$table_name = $wpdb->prefix . "rsi_users";
//	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_name);


	$sql = 'select ru.*, wp_id, temp.wp_profile from oddyse5_wp978.wp_rsi_users as ru left join ( '
. 'select d.value as wp_handle, u.ID as wp_id, '
. 'CONCAT(\'{\', GROUP_CONCAT(\'"\', d.field_id, \'":"\', d.value, \'"\' ORDER BY d.value DESC SEPARATOR \',\'),\'}\') as wp_profile '
. 'FROM oddyse5_wp978.wp_users as u join oddyse5_wp978.wp_bp_xprofile_data as d on u.ID = d.user_id '
. 'where d.field_id = 2 '
. 'or d.field_id = 3 '
. 'or d.field_id > 300 '
. 'group by wp_id '
. ') as temp on ru.handle = temp.wp_handle '
. 'union '
. 'select ru.*, wp_id, temp.wp_profile from oddyse5_wp978.wp_rsi_users as ru right join ( '
. 'select d.value as wp_handle, u.ID as wp_id, '
. 'CONCAT(\'{\', GROUP_CONCAT(\'"\', d.field_id, \'":"\', d.value, \'"\' ORDER BY d.value DESC SEPARATOR \',\'),\'}\') as wp_profile '
. 'FROM oddyse5_wp978.wp_users as u join oddyse5_wp978.wp_bp_xprofile_data as d on u.ID = d.user_id '
. 'where d.field_id = 2 '
. 'or d.field_id = 3 '
. 'or d.field_id > 300 '
. 'group by wp_id '
. ') as temp on ru.handle = temp.wp_handle';
	$results = $wpdb->get_results($sql);
//	$results = $wpdb->get_results('SELECT * FROM ' . $table_name);
	return $results;
}

function getUsers() {
	global $wpdb;
	$table_name = $wpdb->prefix . "rsi_users";
//	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_name);


	$sql = 'select * from ('
		. 'select r.*, d2.value '
		. '	from wp_rsi_users as r '
		. '    right join wp_bp_xprofile_data as d on d.value = r.handle '
		. '    right join wp_bp_xprofile_data as d2 on d.user_id = d2.user_id '
		. '    where d2.field_id = 3 '
		. 'union all '
		. 'select r.*, null as value '
		. '	from wp_rsi_users as r '
		. '    left join wp_bp_xprofile_data as d on d.value = r.handle '
		. '    left join wp_bp_xprofile_data as d2 on d.user_id = d2.user_id '
		. ') as temp '
		. 'where temp.handle is not null '
		. 'group by temp.handle '
		. 'order by temp.id desc';

	$results = $wpdb->get_results($sql);
//	$results = $wpdb->get_results('SELECT * FROM ' . $table_name);
	return $results;
}

function getUserAvatar($handle) {
	global $wpdb;
	$table_name = $wpdb->prefix . "rsi_users";
	$sql = 'SELECT img FROM ' . $table_name . ' WHERE handle = "' . $handle . '"';

	$img = $wpdb->get_var($sql);
	if (sizeof($img) > 0) {
		return "http://robertsspaceindustries.com/" . $img;
	}
	return $img;
}

function reassignMember($handle, $srcid, $destid) {
	global $wpdb;
	$table_name = $wpdb->prefix . "rsi_users";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE handle = "' . $handle .'"');
	$ret = 0;
	if(isset($results->handle)) {
		if ($destid == "NaN") {
			$ret = $wpdb->update($table_name, array( 'unit' => NULL), array( 'handle' => $handle), $format = null);
		} else {
			$ret = $wpdb->update($table_name, array( 'unit' => $destid), array( 'handle' => $handle));
		}
	}
	return $ret;
}

function fetchFromRSI($orgname, $page) {
error_log("refetch MAMENERs");
	$memUrl = 'https://robertsspaceindustries.com/api/orgs/getOrgMembers';
	$data = array('symbol' => $orgname, 'pagesize' => '255', 'page' => $page );

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($memUrl, false, $context);

	$var = json_decode($result, true);
	$str = $var["data"]["html"];

	if (!strlen($str) || $var["success"] != "1" ) {
		return false;
	}

	$DOM = new DOMDocument;
	$DOM->loadHTML('<?xml encoding="utf-8" ?>' . $str);

	$items = $DOM->getElementsByTagName('a');
	for ($i = 0; $i < $items->length; $i++) {
		$href = $items->item($i)->getAttribute('href');
		$temp = split('/', $href);

		if (sizeof($temp) == 3) {
			$handle = $temp[2];
			$children = $items->item($i)->childNodes;

			for ($j = 0; $j < $children->length; $j++) {
				$child = $children->item($j);
				$cnodes = $child->childNodes;
				if ($cnodes) {
					if ($cnodes->length == 10) {
						$img = $cnodes->item(1)->getAttribute('src');
					} else if ($cnodes->length == 5) {
						$role = $cnodes->item(1)->childNodes->item(1)->nodeValue;
						$roles = array();
						if ($cnodes->item(1)->childNodes->length >= 4 && $cnodes->item(1)->childNodes->item(3)->childNodes->length > 0 ) {
							$roleitems = $cnodes->item(1)->childNodes->item(3)->getElementsByTagName('li');
							if ($roleitems) {
								for ($k = 0; $k < $roleitems->length; $k++) {
									array_push($roles, $roleitems->item($k)->nodeValue);
								}
							}
						}

						if ($cnodes->item(3)->childNodes->length >= 2) {
							$name = $cnodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue;
							$rank = $cnodes->item(3)->childNodes->item(5)->nodeValue;
						}
					}
				}
			}

			$userarr = array( "name" => $name, 
								"handle" => $handle,
								"avatar" => $img, 
								"updated_at" => current_time( 'mysql' ) );
			$rolearr = array( "role" => $role,
								"roles" => implode(", ", $roles),
								"rank" => $rank);
			insertOrUpdate($userarr, $rolearr);
		} else {
			// ignore reducted user
			// error_log("ignore reducted user");
		}
	}
	return true;
}

//           id mediumint(9) NOT NULL AUTO_INCREMENT,
//           name tinytext NOT NULL,
//           handle tinytext NOT NULL,
//           avatar varchar(255) DEFAULT '' NOT NULL,
//           timezone int(11) DEFAULT NULL,
//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
//           ships int(11) DEFAULT NULL,
//           units int(11) DEFAULT NULL,
//           rewards int(11) DEFAULT NULL,
//           logs int(11) DEFAULT NULL,

function insertOrUpdate($user, $roles) {
	global $wpdb;

	$handle  = $user["handle"];
/*
	$user_id = $wpdb->get_var("select d.user_id from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where name=\"handle\" and value=\"$handle\"");

	if ($user_id) {
		error_log(" handle set: " . $handle . " | " . $user_id );
		$tz = (int)$wpdb->get_var("select d.value from {$wpdb->prefix}bp_xprofile_fields as f left join {$wpdb->prefix}bp_xprofile_data as d on f.id = d.field_id where d.user_id=$user_id and name=\"timezone\"");
		error_log(">>>> TIMEZONE " . $tz);
		$user["timezone"] = $tz;
	}
*/
	$table_name = $wpdb->prefix . "ot_member";
	$results = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE handle = "' . $user["handle"] .'"');

	if(isset($results->handle)) {
		error_log("update " . $user["handle"] . " | " . $user["name"]);
		$wpdb->update($table_name, $user, array( 'handle' => $user["handle"]));
		
	} else {
//		error_log("insert " . $user["handle"] . " | " . $user["name"]);
		$wpdb->insert($table_name, $user);
	}
}


?>
