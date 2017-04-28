<?php

class Orgtool_API_LFG
{
    protected $namespace = 'orgtool';
    private $base = 'lfgs';
    //     private $base_type = 'ship_types';

    //     public function __construct() {
    //     }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $base = $this->base;
        register_rest_route($this->namespace, '/' . $base, array(
/*
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_lfgs' ),
                'permission_callback' => array( $this, 'get_lfg_permissions_check' ),
            ),
 */
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_lfg' ),
                'permission_callback' => array( $this, 'get_lfg_permissions_check' ),
            ),

        ) );
/*
        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_lfg' ),
                'permission_callback' => array( $this, 'get_lfg_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_member' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_member' ),
                'permission_callback' => array( $this, 'get_members_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );
 */
    }

    public function get_lfg_permissions_check( $request ) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            //         if (!$user_id || !user_can($user_id, 'administrator')) {
            return new WP_Error( 'error', __( 'permission denied' ), array( 'status' => 550 ) );
        }
        return true;
    }


    public function create_lfg($request) {
        $user_id = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
        if (!$user_id) {
            return new WP_Error( 'error', __( 'not logged in' ), array( 'status' => 401 ) );
        } else {

            //	$data = array("username" => "Orgtool", "text" => "got to bed <@83746305490288640>", "icon_url" => "https://discordapp.com/assets/f78426a064bc9dd24847519259bc42af.png", "attachments" => array(array("author_name" => "Orgtool", "author_icon" => "https://discordapp.com/assets/f78426a064bc9dd24847519259bc42af.png", "color" => "#1a80b6", "title" => "some title", "text" => "some text", "fields" => array(array("title" => "game", "value" => "star citizen" )), "footer_icon" => "https://discordapp.com/assets/f78426a064bc9dd24847519259bc42af.png", "footer", "footer text"  )));

            //     $data = array("text"=>"content", "username" => "somename");

            $data = array(
                "mentions" => "@Skoma",
                "attachments" => array(
                    array(
                        "fallback" => "Required plain-text summary of the attachment.",
                        "color" => "#36a64f",
                        "pretext" => "Info about skoma",
                        "author_name" => "Skomas profile",
                        "author_link" => "https://www.oddysee.org/orgtool/#/members/27",
                        "author_icon" => "https://robertsspaceindustries.com/media/1dlobblaw1wsbr/avatar/Space_pinguin.png",
                        "title" => "Member",
                        "title_link" => "https://api.slack.com/",
                        "text" => "[put generic member info here]",
                        "fields" => array(
                            array(
                                "title" => "Rank :crown:",
                                "value" => "[rank]",
                                "short" => true
                            ),
                            array(
                                "title" => "Role :crown:",
                                "value" => "[role]",
                                "short" => true
                            ),
                            array(
                                "title" => "Points :crown:",
                                "value" => "[points]",
                                "short" => true
                            ),

                            array(
                                "title" => "Some other field :crown:",
                                "value" => "[fieldx]",
                                "short" => true
                            ),
                            array(
                                "title" => "Yet another field :crown:",
                                "value" => "[value]",
                                "short" => false
                            ),

                            array(
                                "title" => "Stats field :crown:",
                                "value" => "[value]",
                                "short" => true
                            ),
                            array(
                                "title" => "More Stats :crown:",
                                "value" => "[value]",
                                "short" => true
                            )
                        ),
                        "image_url" => "https://robertsspaceindustries.com/media/1dlobblaw1wsbr/avatar/Space_pinguin.png",
                        "thumb_url" => "https://robertsspaceindustries.com/media/1dlobblaw1wsbr/avatar/Space_pinguin.png",
                        "footer" => "",
                        "footer_icon" => "https://discordapp.com/assets/f78426a064bc9dd24847519259bc42af.png",
                        "ts" => time()
                    )
                )
            );

            $res = $this->sendWenhook($data);
            $response = new WP_REST_Response( array("errors" => array( array("attribute" => "lfg", "message" => "Sorry but your username is ambiguous, please contect one of our admins")), "data" => $data, "res" => $res ));
            $response->set_status( 422 );
            return $response;
        }
    }


    private function sendWenhook($data) {
        //    $data = array("content" => $message, "username" => "Webhooks");
        $curl = curl_init("https://canary.discordapp.com/api/webhooks/269787651719168000/ekizLUh-s7H3nVVa3udULvmOUAyvX9DDGj54_mw3D_7ReD7_h05GgnGfFLtfSmIDA6t1/slack");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if( ! $result = curl_exec($curl))
        {
            return curl_error($curl);
        } 
        return true;
    }

}

?>
