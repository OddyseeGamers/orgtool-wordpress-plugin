<?php

function fetchShipsFromRSI($page) {
    error_log("fetching page " . $page);
    $memUrl = 'https://robertsspaceindustries.com/api/store/getShips';
    $data = array('storefront' => 'pledge', 'pagesize' => '255', 'page' => $page );

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );

    //	$context  = stream_context_create($options);
    //	$result = file_get_contents($memUrl, false, $context);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $memUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);
    curl_close($ch);

    $var = json_decode($result, true);
    $str = $var["data"]["html"];

    $ret =  array();
    if ($var["success"] != "1" || !strlen($str)) {
        return $ret;
    }

    error_log("------------ " . $var["success"] . " || " .  strlen($str));

    $DOM = new DOMDocument;
    $DOM->loadHTML('<?xml encoding="utf-8" ?>' . $str);
    $xpath = new DomXPath($DOM);
    $items = $xpath->query('//li[@class="ship-item"]');
    foreach ($items as $idx => $item) {
        $id = $xpath->query("@data-ship-id", $item);
        $divc = $xpath->query('./div[@class="center"]', $item)->item(0);
        $img = $xpath->query("./img/@src", $divc);
        $name = $xpath->query("./a[@class='filet']/span[contains(concat(' ', normalize-space(@class), ' '), name)]", $divc);

        $temp = explode(' - ', $name->item(0)->nodeValue);
        error_log(">>> item " . $id->item(0)->nodeValue . " | " . $temp[0] . " | " . $temp[1]);


        $divb = $xpath->query("./div[contains(concat(' ', normalize-space(@class), ' '), bottom)]/span/span", $item);
        $imgm = $xpath->query("./div[contains(concat(' ', normalize-space(@class), ' '), bottom)]/span/img/@src", $item);
        $mname = basename($imgm->item(0)->value, ".png");

        $shiparr = array( "id" => $id->item(0)->value, 
            "name" => $temp[0],
            "class" => $temp[1],
            "img" => "https://robertsspaceindustries.com" . $img->item(0)->value, 
            "crew" => $divb->item(0)->nodeValue, 
            "length" => $divb->item(1)->nodeValue, 
            "mass" => $divb->item(2)->nodeValue, 
            "mimg" => "https://robertsspaceindustries.com" . $imgm->item(0)->value,
            "mname" => $mname,
            "updated_at" => current_time( 'mysql' )
        );

        array_push($ret, $shiparr);
    }


    return $ret;
}

function sortByOrder($a, $b) {
    return $a['id'] - $b['id'];
}


function fetchShips() {
    $done = false;
    $page = 1;
    $ships = array();
    do {
        $res = fetchShipsFromRSI($page++);
        $done = (sizeof($res) > 0 ? false : true);
        if (!$done) {
            $ships = array_merge ($ships, $res);
        }
    } while(!$done);

    usort($ships, 'sortByOrder');

    $updated = 0;
    echo "<table class='table'>";
    foreach ($ships as $ship) {
        $temp = insertOrUpdateShipAsItem($ship);
        if (strlen($temp) > 0) {
            $updated += 1;
            echo $temp;
        }
    }

    echo "Ships found: " . sizeof($ships) . " | " . 
    $log = "<tr><td colspan='3'><h4> Result</h4></td></tr>" . "<tr><td>Ships found</td><td colspan='2'>" . sizeof($ships) . "</td></tr>" . "<tr><td>Ships updated</td><td colspan='2'>" .  $updated . "</td></tr>";
    echo "</table>";

//     return array("total" => sizeof($ships), "pages" => $page, "ships" => $ships, "upated" => $updated );
}

function fetchMembersFromRSI($orgname, $page) {
    error_log("fetch " . $orgname . " page " . $page);
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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $memUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);
    curl_close($ch);

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
        $temp = explode('/', $href);

        if (sizeof($temp) == 3) {
            $handle = $temp[2];
            $children = $items->item($i)->childNodes;

            for ($j = 0; $j < $children->length; $j++) {
                $child = $children->item($j);
                $cnodes = $child->childNodes;
                if ($cnodes) {
                    if ($cnodes->length == 10) {
                        $img = $cnodes->item(1)->getAttribute('src');
                        if($img) {
                            $img = "https://robertsspaceindustries.com" . $img;
                        }
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
            //								 "role" => $role,
            //                                 "roles" => implode(", ", $roles),
            //                                 "rank" => $rank);

            array_push($ret, $userarr);
        } else {
            // error_log("ignore reducted user");
        }
    }
    return $ret;
}

function fetchMembers($org) {
    $done = false;
    $page = 1;

    $members = array();
    do {
        $res = fetchMembersFromRSI($org, $page++);
        $done = (sizeof($res) > 0 ? false : true);
        if (!$done) {
            $members = array_merge ($members, $res);
        }
    } while(!$done);

    return $members;

    //     $wpmembers = fetchMembersFromWP();


/*
    error_log("members " . sizeof($members));
    $reversed = array_reverse($members);
    foreach ($reversed as $mem) {
        insertOrUpdateMember($mem);
    }
 */
}

?>
