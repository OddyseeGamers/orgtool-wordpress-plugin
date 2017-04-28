<?php

class Orgtool_API_Session
{
    protected $namespace = 'orgtool';
    private $base = 'sessions';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $base = $this->base;
        register_rest_route($this->namespace, '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_session' ),
                'permission_callback' => array( $this, 'get_session_permissions_check' ),
            ),
        ) );
    }

    public function get_session_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }


    public function create_session($request) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        $session = array();
        $user = array();
        $caps = getUserCaps();
        $current_user = get_user_by("ID", $user_id);

        $src = '';
        $str = get_avatar($user_id, 32);
        $DOM = new DOMDocument;
        $DOM->loadHTML($str);

        $items = $DOM->getElementsByTagName('img');
        if ($items->length == 1) {
            $src = $items->item(0)->getAttribute('src');

        }
        if (0 === strpos($src, '//')) {
            $src = "https:" . $src;
        }


        global $wpdb;
        $searchsql = 'SELECT * FROM ' . $wpdb->prefix . 'ot_member where wp_id = "'. $user_id . '"';
        $otuser = $wpdb->get_row($searchsql);
        //	      return new WP_Error( 'error', __( 'WTF logged in' . $otuser), array( 'status' => 401 ) );

        $ot_id = 0;
        if ( null !== $otuser ) {
            $ot_id = $otuser->id;
        } else { 

            /*
            $searchsql = 'SELECT * FROM ' . $wpdb->prefix . 'ot_member where wp_id is null and (name like "%'. $current_user->user_login . '%" or name like "%' . $current_user->display_name . '%")';
            $otusers = $wpdb->get_results($searchsql);
            $len = sizeof($otusers);

            if ($len == 1) {
                $ot_id = $otusers[0]->id;
                $res = $wpdb->update($wpdb->prefix . "ot_member", array( 'wp_id' => $user_id), array( 'id' => $ot_id));
                if (false !== $res ) {
                } else {
                    return new WP_Error( 'error', __( 'update member error ' . $res->last_error), array( 'status' => 404 ) );
                }

            } else if ($len > 1) {
                $response = new WP_REST_Response( array("errors" =>array( array("attribute" => "user", "message" => "Sorry but your username is ambiguous, please contect one of our admins")) ));
                $response->set_status( 422 );
                return $response;
            } else {
             */
                $data = array("name" => $current_user->display_name, "wp_id" => $user_id, "avatar" => $src);
                $res = $wpdb->insert($wpdb->prefix . "ot_member", $data);
                if (null !== $res) {
                    $ot_id = $wpdb->insert_id;
                } else {
                    return new WP_Error( 'error', __( 'member not created' ), array( 'status' => 400 ) );
                }
//             } 
        }


        $user["id"] = $ot_id;
        $user["wp_id"] = $user_id;
        $user["user_login"] = $current_user->user_login;
        $user["user_nicename"] = $current_user->user_nicename;
        $user["display_name"] = $current_user->display_name;
        $user["user_status"] = $current_user->user_status;
        $user["user_registered"] = $current_user->user_registered;


        $user["img"] = $src;
        $user["isadmin"] = user_can($user_id, 'administrator');
        //return rest_ensure_response( array('session' => array( "id" => 2, array('user' => $user))));
        return rest_ensure_response( array('session' => array('id' => LOGGED_IN_COOKIE, 'user' => $user)));
    }

}

?>
