<?php

class Orgtool_API_MemberReward
{
    protected $namespace = 'orgtool';
    private $base = 'member_rewards';
    //     private $base_type = 'ship_types';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $base = $this->base;
        register_rest_route($this->namespace, '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_member_rewards' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_member_reward' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_member_reward' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            //             array(
            //                 'methods'         => WP_REST_Server::EDITABLE,
            //                 'callback'        => array( $this, 'update_ship_model' ),
            //                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            //             ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_member_reward' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

        /*
        register_rest_route($this->namespace, '/' . $this->base_type, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_reward_types' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
        ) );
        register_rest_route($this->namespace, '/' . $this->base_type . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_reward_type' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
        ) );
         */
    }

    public function get_rewards_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }



    public function get_member_rewards($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_reward";
        $searchsql = 'SELECT * FROM ' . $table_name . ' order by id';
        $results = $wpdb->get_results($searchsql);
    /*
    foreach($results as $ship_model) {
      $sql = 'SELECT id FROM ' . $table_name . ' WHERE parent = ' . $reward->id;
      $reward_ids = $wpdb->get_results( $sql);

      $ids = array();
      foreach($reward_ids as $p) {
        array_push($ids, $p->id);
      }
      $ship_model->reward_ids = $ids;
    }
     */
        //     return array('rewards' => $results);
        $response = rest_ensure_response( array('member_rewards' => $results) );
        //     $response->header( 'Content-Type', "application/json" );
        return $response;
    }


    public function get_member_reward($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_name = $wpdb->prefix . "ot_member_reward";
        $searchsql = 'SELECT * FROM ' . $table_name . ' where id = '. $id;
        $assignment = $wpdb->get_row($searchsql);

        if ( null !== $assignment ) {
            return array('member_reward' => $assignment);
        } else { 
            return new WP_Error( 'error', __( 'member reward not found' ), array( 'status' => 404 ) );
        }
    }

    public function create_member_reward($request) {
        // why do I have to do this?? missing arg? WP API borken?
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['memberReward'] ) ) {
            $data = $data["memberReward"];
        }

        //     $data["member"] = $data["member_id"];
        //     unset($data["member_id"]);
        //     $data["reward"] = $data["reward_id"];
        //     unset($data["reward_id"]);


        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_reward";
        $res = $wpdb->insert($table_name, $data);

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return $this->get_member_reward( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'reward not created' ), array( 'status' => 400 ) );
        }
    }

    public function delete_member_reward($request) {
        $id = (int) $request['id'];
        $reward = $this->get_member_reward( array("id" => $id), false);
        //       return array("reward" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);

        if ( empty( $id ) || empty($reward["member_reward"]) ||  empty( $reward["member_reward"]->id ) ) {
            return new WP_Error( 'error', __( 'member reward not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_member_reward";
        $res = $wpdb->delete($table_name, array('id' => $id));

        if (null !== $res) {
            //       return array("create ok" => $res, "data" => $data, "inserted id" => $wpdb->insert_id);
            return array(); //$this->get_member_reward( array("id" => $wpdb->insert_id) );
        } else {
            return new WP_Error( 'error', __( 'reward not created' ), array( 'status' => 400 ) );
        }
    }


}

?>
