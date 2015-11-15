<?php

function fetchFromRSI($orgname, $page) {
	error_log("refetch MAMENERs" . $orgname);
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

	$ret =  array();

	if (!strlen($str) || $var["success"] != "1" ) {
		error_log("empty result?");
		return $ret;
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

			error_log("add user");
			$userarr = array( "name" => $name, 
								"handle" => $handle,
								"avatar" => $img, 
								"updated_at" => current_time( 'mysql' ) );

			array_push($ret, $userarr);
//             $rolearr = array( "role" => $role,
//                                 "roles" => implode(", ", $roles),
//                                 "rank" => $rank);
//             insertOrUpdate($userarr, $rolearr);
//             insertOrUpdate($userarr);
		} else {
			// ignore reducted user
			// error_log("ignore reducted user");
		}
	}
	return $ret;
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

function insertOrUpdate($user) {
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
