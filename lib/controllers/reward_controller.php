<?php

class Orgtool_API_Reward
{
    protected $namespace = 'orgtool';
    private $base = 'rewards';
    private $base_type = 'reward_types';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_rewards' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_reward' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
        ) );

        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_reward' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_reward' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_reward' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );



        register_rest_route($this->namespace, '/' . $this->base_type, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_reward_types' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_reward_type' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
        ) );

        register_rest_route($this->namespace, '/' . $this->base_type . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_reward_type' ),
//                 'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_reward_type' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_reward_type' ),
                'permission_callback' => array( $this, 'get_rewards_permissions_check' ),
                'args'            => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );
    }

    public function get_rewards_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
/*
        $post_type = get_post_type_object( $this->post_type );

        if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }
 */
        return true;
    }



    public function get_rewards($request) {
        global $wpdb;
        $table_reward = $wpdb->prefix . "ot_reward";
        $searchsql = 'SELECT * FROM ' . $table_reward . ' order by id';
        $results = $wpdb->get_results($searchsql);

        foreach($results as $reward) {
            $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_member_unit WHERE reward = ' . $reward->id;
            $rew_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($rew_ids as $p) {
                array_push($ids, $p->id);
            }
            $reward->memberUnits = $ids;

            $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_member_reward WHERE reward = ' . $reward->id;
            $memrew_ids = $wpdb->get_results( $sql);

            $ids = array();
            foreach($memrew_ids as $mr) {
                array_push($ids, $mr->id);
            }
            $reward->memberRewards = $ids;

            //        $reward->items = $this->get_items_for_item($item->id);
            //       $item->item_props = $this->get_itemprops_for_item($item->id);
        }

        return new WP_REST_Response( array('rewards' => $results), 200 );
    }


    public function get_reward($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_reward = $wpdb->prefix . "ot_reward";
        $searchsql = 'SELECT * FROM ' . $table_reward . ' where id = '. $id;
        $reward = $wpdb->get_row($searchsql);

        $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_member_unit WHERE reward = ' . $id;
        $rew_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($rew_ids as $p) {
            array_push($ids, $p->id);
        }
        $reward->memberUnits = $ids;

        $sql = 'SELECT id FROM ' . $wpdb->prefix . 'ot_member_reward WHERE reward = ' . $id;
        $memrew_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($memrew_ids as $mr) {
            array_push($ids, $mr->id);
        }
        $reward->memberRewards = $ids;

        if ( null !== $reward ) {
            //        $reward->items = $this->get_items_for_item($item->id);
            //        $item->item_props = $this->get_itemprops_for_item($item->id);
            return array('reward' => $reward);
        } else { 
            return new WP_Error( 'error', __( 'reward not found' ), array( 'status' => 404 ) );
        }
    }


    /////////////////////////////////

    public function create_reward($request) {
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['reward'] ) ) {
            $data = $data["reward"];
        }

        // if !member || !model > error...

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward";
        $res = $wpdb->insert($table_name, $data);
        if (null !== $res) {
            return $this->get_reward( array("id" => $wpdb->insert_id ));
        } else {
            return new WP_Error( 'error', __( 'reward not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_reward($request) {
        $id = (int) $request['id'];
        $reward = $this->get_reward( array("id" => $id), false );

        if ( empty( $id ) || empty( $reward["reward"]->id ) ) {
            return new WP_Error( 'error', __( 'reward not found 3 '), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_reward( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update reward error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }


    public function delete_reward($request) {
        $id = (int) $request['id'];
        $reward = $this->get_reward( array("id" => $id), false);

        if ( empty( $id ) || empty( $reward["reward"]->id ) ) {
            return new WP_Error( 'error', __( 'reward not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward";
        $res = $wpdb->delete($table_name, array('id' => $id));
        return array();
    }


    //////////////////////////////////////////////////////////////

    public function get_reward_types($request) {
        global $wpdb;
        $table_type = $wpdb->prefix . "ot_reward_type";
        $searchsql = 'SELECT * FROM ' . $table_type . ' order by id';
        $results = $wpdb->get_results($searchsql);

        foreach($results as $reward_type) {
            $reward_type->rewards = $this->get_rewards_for_type($reward_type->id);
        }

        return rest_ensure_response( array('reward_types' => $results) );
    }


    public function get_reward_type($request) {
        global $wpdb;
        $id = (int) $request['id'];
        $table_type = $wpdb->prefix . "ot_reward_type";
        $searchsql = 'SELECT * FROM ' . $table_type . ' where id = '. $id;
        $reward_type = $wpdb->get_row($searchsql);

        if ( null !== $reward_type ) {
            $reward_type->rewards = $this->get_rewards_for_type($reward_type->id);
            return array('reward_type' => $reward_type);
        } else { 
            return new WP_Error( 'error', __( 'reward_type not found' ), array( 'status' => 404 ) );
        }
    }

    private function get_rewards_for_type($rewardid) {
        global $wpdb;
        $table_reward = $wpdb->prefix . "ot_reward";
        $sql = 'SELECT id FROM ' . $table_reward . ' WHERE type = ' . $rewardid;
        $reward_ids = $wpdb->get_results( $sql);

        $ids = array();
        foreach($reward_ids as $p) {
            array_push($ids, $p->id);
        }
        return $ids;
    }


    public function create_reward_type($request) {
        $data = json_decode( $request->get_body(), true );
        if ( ! empty( $data['rewardType'] ) ) {
            $data = $data["rewardType"];
        }

        // if !member || !model > error...

        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward_type";
        $res = $wpdb->insert($table_name, $data);
        if (null !== $res) {
            return $this->get_reward_type( array("id" => $wpdb->insert_id ));
        } else {
            return new WP_Error( 'error', __( 'reward type not created' ), array( 'status' => 400 ) );
        }
    }

    public function update_reward_type($request) {
        $id = (int) $request['id'];
        $rewardType = $this->get_reward_type( array("id" => $id), false );

        if ( empty( $id ) || empty( $rewardType["reward_type"]->id ) ) {
            return new WP_Error( 'error', __( 'reward type not found 3 :' . $id ), array( 'status' => 404 ) );
        }

        // why do I have to do this??
        $data = json_decode( $request->get_body(), true );
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward_type";
        $res = $wpdb->update($table_name, $data, array( 'id' => $id));
        if (false !== $res ) {
            return $this->get_reward_type( array("id" => $id) );
        } else {
            return new WP_Error( 'error', __( 'update reward type error ' . $res->last_error), array( 'status' => 404 ) );
        }
    }

    public function delete_reward_type($request) {
        $id = (int) $request['id'];
        $reward_type = $this->get_reward_type( array("id" => $id), false);

        if ( empty( $id ) || empty( $reward_type["reward_type"]->id ) ) {
            return new WP_Error( 'error', __( 'reward_type not found 2 '), array( 'status' => 404 ) );
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "ot_reward_type";
        $res = $wpdb->delete($table_name, array('id' => $id));
        return array();
    }

}

?>
