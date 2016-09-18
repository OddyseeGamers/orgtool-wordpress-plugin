<?php


function fuzzySearch($wp, $ots) {
    //     error_log("    - SEARCH " . $wp["wp_id"] . " " . sizeof($ots));

    $res = array();
    for ($i = 0; $i < sizeof($ots); $i++) {
        //         error_log("     > id " . $ots[$i]->id . " wp_id " . $ots[$i]->wp_id
        //                                                 . " handle " . $ots[$i]->handle
        //                                                 . ", name " . $ots[$i]->name
        //                                                 . ", time " . $ots[$i]->updated_at);
        //                                                         . ", assets " . $meta["assets"]
        //                                                         . ", units " . $meta["units"] );
        if ($ots[$i]->wp_id == $wp["wp_id"]) {
            //             error_log("       > found wp_id !!" . $wp["wp_id"]); // . "!! id " . $ots[$i]->["id"] . " wp_id " . $ots[$i]->wp_id . " handle " . $ots[$i]->handle . ", name " . $ots[$i]->name . ", time " . $ots[$i]->updated_at);
            $res = (array)$ots[$i];
            return $res;
        } else if (containsAny((array)$ots[$i], $wp)) {
            if ($res) {
                error_log("  > multiple best name matchs id: " . $res["id"] . ", wp_ip " . $res["wp_id"] . ", <-> " . $ots[$i]->name . ", time " . $ots[$i]->updated_at);
            }
            $res = (array)$ots[$i];
        }
    }

    return $res;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function mergeWPMembers($rsimembers) {
    global $wpdb;
    //     $sql= "select d.user_id as wp_id, u.display_name as name, d.value as handle "
    //         . "from wp_users as u "
    //         . "left join wp_bp_xprofile_data as d on u.ID = d.user_id "
    //         . "where d.field_id = 2";

    //     $sql= "select ID as wp_id, display_name as name from wp_users";
    $sql = "select ID as wp_id, display_name as name, user_status as spam from wp_users";
    $members = $wpdb->get_results($sql, ARRAY_A);

    error_log("RSI members " . sizeof($rsimembers));
    error_log("WP members " . sizeof($members));

    $toremove = array();

    $ottbl = $wpdb->prefix . "ot_member";
    $untbl = $wpdb->prefix . "ot_member_unit";
    $asstbl = $wpdb->prefix . "ot_ship";
    foreach($members as $wpmem) {
        if ($wpmem["spam"] == "1") {
            error_log("*** skipping potential spam user: " . $wpmem["wp_id"] . ", name " . $wpmem["name"] . " -------------------------------");
            continue;
        }
        unset($wpmem["spam"]);
        $state = array("wp" => "ok", "rsi" => "unknown", "general" => "unknown");
        error_log("--- WP user " . $wpmem["wp_id"] . ", name " . $wpmem["name"] . " -------------------------------");


        $handle = $wpdb->get_var("select d.value from " . $wpdb->prefix . "bp_xprofile_fields as f " 
            . " left join " . $wpdb->prefix  . "bp_xprofile_data as d on f.id = d.field_id "
            . " where d.user_id=" . $wpmem["wp_id"] . " and name=\"handle\"");
        if ($handle) {
            error_log("  * has WP handle: " . $handle);
            $wpmem["handle"] = $handle;
        } else{
            error_log("  - no WP handle");
            //             $state["wp"] = "no handle";
        }

        $htemp = $wpmem["handle"];
        if(!$htemp) {
            $htemp = $wpmem["name"];
        }

        $tz = $wpdb->get_var("select d.value from "  . $wpdb->prefix . "bp_xprofile_fields as f "
            . " left join "  . $wpdb->prefix . "bp_xprofile_data as d on f.id = d.field_id "
            . " where d.user_id=" . $wpmem["wp_id"]. " and name=\"timezone\"");
        if ($tz) {
            $wpmem["timezone"] = (int)$tz;
            //             error_log("  * has WP timezone: " . $tz);
        } else {
            //             error_log("  - no WP timezone");
        }

        $overwriteval = $wpdb->get_var("select d.value from "  . $wpdb->prefix . "bp_xprofile_fields as f "
            . " left join "  . $wpdb->prefix . "bp_xprofile_data as d on f.id = d.field_id "
            . " where d.user_id=" . $wpmem["wp_id"]. " and name=\"overwrite_avatar\"");
        $skip = (strpos($overwriteval, 'Use RSI Avatar') !== false);


        //         error_log(" -- search OT user");
        $sql =  'SELECT * FROM ' . $ottbl . ' WHERE wp_id = "' . $wpmem["wp_id"] . '" ' 
            . ' OR handle like "%' . $htemp  . '%" '
            . ' OR handle like "%' . $wpmem["name"] . '%" '
            . ' OR name like "%' . $htemp  . '%" '
            . ' OR name like "%' . $wpmem["name"] . '%" '
            . ' order by updated_at';
        //         error_log("  - sql " . $sql);
        $otmems = $wpdb->get_results($sql);


        $otmem = null;
        if (sizeof($otmems) == 0) {

            //             $otmem = array( "wp_id" => $wpmem["wp_id"], 
            //                             "name" => $wpmem["name"], 
            //                             "handle" => $wpmem["handle"],
            //                             "updated_at" => current_time( 'mysql' ) );

            //             error_log("  * new OT user");
        } else if (sizeof($otmems) == 1) {

            $otmem = (array)$otmems[0];
            $state["general"] = "ok";
            error_log("  * found OT user: id " . $otmem["id"] .  ', wp_id ' . $otmem["wp_id"] . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]);

        } else if (sizeof($otmems) > 1) {

            $otmem = fuzzySearch($wpmem, $otmems);
            if ($otmem) {
                if ($otmem["wp_id"]) {
                    error_log("  * found OT user: id " . $otmem["id"] .  ', wp_id ' . $otmem["wp_id"] . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]);
                    $state["general"] = "ok";
                } else {
                    error_log("  * best name match out of "  . sizeof($otmems) . ", id: " . $otmem["id"] . ", wp_ip " . $otmem["wp_id"] . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]. ", time " . $otmem["updated_at"]);
                    $state["general"] = "fuzzy";
                }
            } else {
                //                 error_log("  * fuzzzy search failed "); // . $test1["wp_id"]);
                error_log("  * found OT multiple matches " . sizeof($otmems) . ", fuzzy search failed");
                //                 error_log("  * new OT user, not in OT");
                //                 error_log("  * new OT user, create from WP, wp_id: " . $otmem["wp_id"]  . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]);
            }


            /*
            $tempmem = array();
            for ($i = 0; $i < sizeof($otmems); $i++) {



                error_log("     > id " . $otmems[$i]->id . " wp_id " . $otmems[$i]->wp_id
                                                        . " handle " . $otmems[$i]->handle
                                                        . ", name " . $otmems[$i]->name
                                                        . ", time " . $otmems[$i]->updated_at);
//                                                         . ", assets " . $meta["assets"]
//                                                         . ", units " . $meta["units"] );
//                 error_log("     " . $i . " handle " . $otmems[$i]->handle . ", name " . $otmems[$i]->name . ", time " . $otmems[$i]->updated_at);
                if ($otmems[$i]->wp_id == $wpmem["wp_id"]) {
//                     $otmem = (array)$otmems[$i];
//                     error_log("       > found wp_id " . $otmems[$i]["id"] . " wp_id " . $otmems[$i]->wp_id . " handle " . $otmems[$i]->handle . ", name " . $otmems[$i]->name . ", time " . $otmems[$i]->updated_at);
                    $state["general"] = "ok";
                    $tempmem = (array)$otmems[$i];
//                     error_log("   > id match SET " . $tempmem["id"] . ", wp_id " . $tempmem["wp_id"] . ", name " . $tempmem["name"]. ", time " . $tempmem["updated_at"]);
//                     break;
                } else if (containsAny((array)$otmems[$i], $wpmem)) {
                    if ($otmem) {
                        error_log("  > multiple best name matchs id: " . $otmem["id"] . ", wp_ip " . $otmem["wp_id"] . ", <-> " . $otmems[$i]->name . ", time " . $otmems[$i]->updated_at);
                    }
                    $otmem = (array)$otmems[$i];
                    $state["general"] = "ok";
                }
            }

            if ($tempmem) {
                error_log("  * id match id: " . $tempmem["id"] . ", wp_id " . $tempmem["wp_id"] . ", handle " . $tempmem["handle"] . ", name " . $tempmem["name"] . ", time " . $tempmem["updated_at"]);

                $otmem = $tempmem;
            } else {
//                 if ($maxassets > 0) {
//                     error_log("    > most assest, id: " . $otmems[$maxassetsidx]->id . ", assets: " . $maxassets);
//                 }
//                 if ($maxunits > 0) {
//                     error_log("    > most units, id: " . $otmems[$maxunitsidx]->id . ", units: " . $maxunits);
//                 }


                if ($otmem) {
                    error_log("  * best name match id: " . $otmem["id"] . ", wp_ip " . $otmem["wp_id"] . ", name " . $otmem["name"]. ", time " . $otmem["updated_at"]);
    //                 error_log("  * found OT user after 2nd pass: id: " . $otmem["id"] . ", wp_ip " . $otmem["wp_id"] . ", name " . $otmem["name"]. ", time " . $otmem["updated_at"]);
    //                 if ($tempmem["id"] != $otmem["id"]) {
    //                     error_log("     > look closer, id: " . $tempmem["id"] . ", wp_id " . $tempmem["wp_id"] . ", name " . $tempmem["name"]. ", time " . $tempmem["updated_at"]);
    //                 }
                } else {

                    $otmem = array( "wp_id" => $wpmem["wp_id"], 
                                        "name" => $wpmem["name"], 
                                        "handle" => $wpmem["handle"],
                                        "updated_at" => current_time( 'mysql' ) );
                    error_log("  * new OT user, create from WP, wp_id: " . $otmem["wp_id"]  . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]);
    //                 error_log("  * OT user update [newest] " . $otmem["id"] . " | " . $otmem["wp_id"] . ", name " . $otmem["name"]. ", time " . $otmem["updated_at"]);
                }
            }
             */

            //             $otmem = (array)$otmems[0];
            //             error_log(" >> OT user update [newest] " . $otmem["id"] . " | " . $otmem["wp_id"] . ", name " . $otmem["name"]. ", time " . $otmem["updated_at"]);
        } else {
            error_log("--> OT user ??? " . sizeof($otmems));
            $state["general"] = "error";
            return;
        }


        if (!$otmem) {
            //             error_log(" -- NO OT user, create one?");

            $otmem = array( "wp_id" => $wpmem["wp_id"], 
                "name" => $wpmem["name"], 
                "handle" => $wpmem["handle"],
                "updated_at" => current_time( 'mysql' ) );
            error_log("  * new OT user, create from WP, wp_id: " . $otmem["wp_id"]  . ", handle " . $otmem["handle"] . ", name " . $otmem["name"]);
            $state["general"] = "ok";
        }

        $otmem["wp_id"] = $wpmem["wp_id"];

        if ($skip) {
            //             error_log("  * ignore WP avatar [overwrite_avatar=1]");
        } else {
            if ($otmem["avatar"]) {
                //                 error_log("  * ignore WP avatar [overwrite_avatar=0, but already set]"); // . $otmem["avatar"]);
            } else {
                $avatar = get_avatar_url($wpmem["wp_id"]);
                if ($avatar) {
                    $otmem["avatar"] = $avatar;
                    error_log("  * set WP avatar: [overwrite_avatar=0] " . $otmem["avatar"]);
                } else {
                    error_log("  - could not set WP avatar: " . $otmem["avatar"]);
                }
                //                 $otmem["avatar"] = $rsimem["avatar"];
                //                 error_log("  * set RSI avatar: [overwrite_avatar=1]" . $otmem["avatar"] . " | " . $skip);
            }
        }

        //         error_log(" -- search RSI user");
        $idx = -1;
        for($i = 0; $i < sizeof($rsimembers) && $idx < 0; $i++) {
            if (containsAny($rsimembers[$i], $otmem)) {
                //                 error_log("    >> found rsi user, handle: " . $rsimem["handle"]  . ', name: ' . $rsimem["name"]);
                $idx = $i;
            }
        }

        if($idx >= 0) {
            //             error_log("  * found RSI user ");
            $rsimem = $rsimembers[$idx];

            error_log("  * found RSI user, handle: " . $rsimem["handle"]  . ', name: ' . $rsimem["name"]);
            array_push($toremove, $idx);
            $state["rsi"] = "ok";

            //             $otmem["name"] = $rsimem["name"];
            //             $otmem["handle"] = $rsimem["handle"];
            //             $otmem["avatar"] = $rsimem["avatar"];
            $otmem["updated_at"] = $rsimem["updated_at"];

            if ($otmem["name"]) {
                //                 error_log("  * ignore RSI name"); // . $otmem["name"]);
            } else {
                $otmem["name"] = $rsimem["name"];
                error_log("  * set RSI name: " . $otmem["name"]);
            }

            if ($otmem["handle"] && ($otmem["handle"] == $rsimem["handle"])) {
                $eq = ($otmem["handle"] == $rsimem["handle"]);
                //                 error_log("  * ignore RSI handle");// . $otmem["handle"] . ' =?= ' . $rsimem["handle"] );
            } else {
                $otmem["handle"] = $rsimem["handle"];
                error_log("  * set RSI handle: " . $otmem["handle"]);
            }


            if (!$skip) {
                //                 error_log("  * ignore RSI avatar [overwrite_avatar=0]");
            } else {
                if ($otmem["avatar"]) {
                    //                     error_log("  * ignore RSI avatar [overwrite_avatar=1, but already set]"); // . $otmem["avatar"]);
                } else {
                    $otmem["avatar"] = $rsimem["avatar"];
                    error_log("  * set RSI avatar: [overwrite_avatar=1]" . $otmem["avatar"]);
                }
            }
            //             error_log(" >> RSI user merged,  handle: " . $rsimem["handle"]  . ', name: ' . $rsimem["name"]);
        } else {
            if ($wpmem["handle"]) {
                error_log("  - RSI user not found, WP handle: " . $wpman["handle"] . ", OT handle: "  . $otmem["handle"]);
                //                 $wpmem["handle"] = $handle;
            } else {
                //                 $wpmem["handle"] = $wpmem["name"];
                error_log("  - RSI user not found and no WP handle, OT handle: " . $otmem["handle"]);
            }
        }


        // rsimem = "name" "handle" "avatar"  "updated_at"
        // otmem = id,wp_id,name,handle,avatar,timezone,updated_at,rewards,logs
        // wpmem = wp_id, name, handle


        //         if ($state["wp"] == "ok" && $state["rsi"] == "ok") {
        //             $state["general"] = "ok";
        //         } else if ($state["wp"] != "ok") {
        //             $state["general"] = $state["wp"];
        //         } else if ($state["rsi"] != "ok") {
        //             $state["general"] = $state["rsi"];
        //         }

        //         $skip = (strpos($overwriteval, 'Use RSI Avatar') !== false);

        if (startsWith($otmem["avatar"], "/")) {
            $otmem["avatar"] = "https://robertsspaceindustries.com" . $otmem["avatar"];
            error_log("  * correct RSI avatar: " . $otmem["avatar"]);
        }



        error_log(">>>>>>>>>>>>>>>>>>>>> ");
        error_log(">> state wp: " . $state["wp"] . ", rsi: " . $state["rsi"] . ", general: " . $state["general"]);
        error_log(">> id: " . $otmem["id"] . ", wp_ip " . $otmem["wp_id"] . ", handle " . $otmem["handle"] . ", name " . $otmem["name"] . ", time " . $otmem["updated_at"]);
        error_log(">> avatar: " . $otmem["avatar"]);

        if(isset($otmem["id"])) {

            error_log(">> UPDATE << user " . $otmem["id"] . " WP: " . $wpmem["wp_id"]);
            $wpdb->update($ottbl, $otmem, array( 'id' => $otmem["id"]));
            //             $ship["class"] = $result->id;
        } else {
            error_log(">> INSERT << user " . $wpmem["wp_id"]);
            unset($otmen["id"]);
            $ot_id = $wpdb->insert($ottbl, $otmem);
            //             $ship["class"] = $wpdb->insert_id;
        }
        error_log(">>>>>>>>>>>>>>>>>>>>> ");




        // rsimem = "name" "handle" "avatar"  "updated_at"
        // otmem = id,wp_id,name,handle,avatar,timezone,updated_at,rewards,logs
        // wpmem = wp_id, name, handle

    }


    error_log("RSI members to remove " . sizeof($toremove));
    sort($toremove, SORT_NUMERIC);
    $reversed = array_reverse($toremove);
    foreach($reversed as $d) {
        unset($rsimembers[$d]);
    }

    error_log("RSI members todo " . sizeof($rsimembers));
    return $rsimembers;
}

function containsAny($a, $b) {
    $an = preg_replace('/\s+/', '', strtolower($a["name"]));
    $ah = preg_replace('/\s+/', '', strtolower($a["handle"]));
    $bn = preg_replace('/\s+/', '', strtolower($b["name"]));
    $bh = preg_replace('/\s+/', '', strtolower($b["handle"]));

    if ($ah == $bh || $an == $bh || $ah == $bn || $an == $bn ) {
        //         error_log("       > found match " . $an . ' - ' . $ah . ' || ' . $an . ' - ' . $ah);
        return true;
    } else {
        return false;
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
        error_log("update only avatar for now, handle: " . $user["handle"] . ", name: " . $user["name"]);
        //         error_log("update only avatar for now (TODO add options), handle: " . $user["handle"] . ", name: " . $user["name"] . ", avatar: " . $user["avatar"]);
        $wpdb->update($table_name, array("avatar" => $user["avatar"]), array( 'handle' => $user["handle"]));

        //         error_log("skip (TODO add options) " . $user["handle"] . " | " . $user["name"]);
        //         $wpdb->update($table_name, $user, array( 'handle' => $user["handle"]));

    } else {
        error_log("insert " . $user["handle"] . " | " . $user["name"]);
        //         $wpdb->insert($table_name, $user);
    }
}



function getOldStruc() {
    return array(0 => "ODDYSEE", 1 => "LID", 2 => "Operations", 3 => "Trade Logistics", 4 => "Tech Salvage", 5 => "Trading", 6 => "Mining", 7 => "Logistics", 8 => "Base Operations", 9 => "Salvage", 10 => "Boarding", 11 => "Technology", 12 => "Ordinance", 13 => "Operations", 14 => "Intel", 15 => "Public Relations", 16 => "Contracts", 17 => "Racing", 18 => "Recruiting", 19 => "Pathfinder", 20 => "Cartography", 21 => "Navigation", 22 => "Operations", 23 => "SOD", 24 => "1st Fleet", 25 => "Light Fighters", 26 => "Heavy Fighters", 27 => "Assault/ Bombers", 28 => "Recon", 29 => "Gunships/ Transports", 30 => "Capital Ships Command", 31 => "2nd Fleet", 32 => "Light Fighters", 33 => "Heavy Fighters", 34 => "Assault/ Bombers", 35 => "Recon", 36 => "Gunships/ Transports", 37 => "Capital Ships Command", 38 => "Marine Command", 39 => "Marine Squads");
}

function insertOrUpdateShipAsItem($ship) {
    global $wpdb;
// "id" => $id->item(0)->value, 
// "name" => $temp[0],
// "class" => $temp[1],
// "img" => $img->item(0)->value, 
// "crew" => $divb->item(0)->nodeValue, 
// "length" => $divb->item(1)->nodeValue, 
// "mass" => $divb->item(2)->nodeValue, 
// "mimg" => $imgm->item(0)->value,
// "mname" => $mname,
// "updated_at" => current_time( 'mysql' )

    // check if there is an item prop of type manufacturer
    $table_itype = $wpdb->prefix . "ot_item_type";
    $result = $wpdb->get_row( 'SELECT * FROM ' . $table_itype . ' WHERE typeName = "manufacturer"');
    $manuType = $result->id;
    if(!isset($manuType)) {
        $res = $wpdb->insert($table_itype, array("typeName" => "manufacturer", "name" =>  "Manufacturer"));
        $manuType = $wpdb->insert_id;
//     } else {
//         $wpdb->update($table_itype, array("img" => $ship['mimg']), array( 'id' => $manuType));
    }

    // check if there is an item of type manufacturer
    $table_item = $wpdb->prefix . "ot_item";
    $result = $wpdb->get_row( 'SELECT * FROM ' . $table_item . ' WHERE name = "' . $ship["mname"] . '" and type = "' . $manuType . '"');
    $manu = $result->id;
    if(!isset($manu)) {
        $res = $wpdb->insert($table_item, array("type" => $manuType, "name" => $ship['mname'], "img" => $ship["mimg"] ));
        $manu = $wpdb->insert_id;
    }

    unset($ship['mname']);
    unset($ship['mimg']);


    // check if there is an item prop of type pmodel
    $result = $wpdb->get_row( 'SELECT * FROM ' . $table_itype . ' WHERE typeName = "model"');
    $modelType = $result->id;
    if(!isset($modelType)) {
        $res = $wpdb->insert($table_itype, array("typeName" => "model", "name" => "Model"));
        $modelType = $wpdb->insert_id;
//     } else {
//         $wpdb->update($table_itype, array("img" => $ship['img']), array( 'id' => $modelType));
    }

    // check if there is an item of type Model
    $result = $wpdb->get_row( 'SELECT * FROM ' . $table_item . ' WHERE name = "' . $ship["name"] . '" and parent = "' . $modelType . '"');
    $model = $result->id;
    if(!isset($model)) {
        $res = $wpdb->insert($table_item, array("type" => $modelType, "parent" => $manu, "name" => $ship['name'], "img" => $ship["img"] ));
        $model = $wpdb->insert_id;
    }


    /*
    $props = array();
    $table_ptype = $wpdb->prefix . "ot_item_prop_type";
    $table_prop = $wpdb->prefix . "ot_item_prop";
    foreach ($array("class", "crew", "length", "mass") as $prop) {
        $propval = $ship[$prop];

        $result = $wpdb->get_row( 'SELECT * FROM ' . $table_ptype . ' WHERE name = "' . $prop . '"');
        $ptid = $result->id;
        if(!isset($ptid)) {
            $res = $wpdb->insert($table_ptype, array( "typeName" => "stats", "name" => $prop));
            $ptid = $wpdb->insert_id;
        }

        $result = $wpdb->get_row( 'SELECT * FROM ' . $table_prop . ' WHERE type = "' . $ptid . '" and value = "' . $propval . '"');
        $pid = $result->id;
        if(!isset($pid)) {
            $res = $wpdb->insert($table_prop, array( "name" => $prop, "value" => $propval));
            $pid = $wpdb->insert_id;
        }
        
        array_push($props, $pid);

        unset($ship[$prop]);
    }
     */


    /*

    $table_ship = $wpdb->prefix . "ot_ship_model";
    $results = $wpdb->get_row( 'SELECT * FROM ' . $table_ship . ' WHERE id = "' . $ship["id"] . '"');

    if(isset($results->id)) {
        error_log("ship update " . $ship["id"] . " | " . $ship["name"]);
        $wpdb->update($table_ship, $ship, array( 'id' => $ship["id"]));

    } else {
        error_log("ship insert " . $ship["id"] . " | " . $ship["name"]);
        $wpdb->insert($table_ship, $ship);
    }
    */
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
