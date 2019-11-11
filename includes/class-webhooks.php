<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Athena_Webhooks {
    protected static $webhooks_list;

    public static function init() {
        self::$webhooks_list = Athena_Api::get_webhooks_list();
        

    }

    public static function incoming_post_request($request) {
        $key = $request->get_param( 'key' );
        $test = $request->get_param( 'test' );
        $webhook = (array)self::$webhooks_list[$key];
        $template = $webhook['template'];
        try {
            $parsed = preg_replace_callback('/{{((?:[^}]|}[^}])+)}}/', function($match) use ($request) { return json_encode($request->get_param($match[1])); }, $template);
        } catch(Exception $err) {
            return  $data = $err->get_error_data();
            $data['status'] = 401;
            return new WP_error(
                $err->get_error_code(), 
                $err->get_error_message(),
                $data);
        }
        $decoded = json_decode($parsed, true);

        if($test == '1') {
            return array(
                'webhook' => $webhook,
                'parsed_template' => $parsed,
                'decoded' => $decoded
            );
        }
        
        switch($webhook['type']) {
            case 'insert_user': {
                // return $webhook;
                $decoded['user_pass'] = wp_generate_password();
                $userIdOrError = wp_insert_user($decoded);
                if (is_wp_error($userIdOrError)) {
                    $data = $userIdOrError->get_error_data();
                    $data['status'] = 401;
                    return new WP_error(
                        $userIdOrError->get_error_code(), 
                        $userIdOrError->get_error_message(),
                        $data);
                }
                return new WP_REST_Response(array(
                    'user_id' => $userIdOrError,
                    'success' => true
                ), 404);
            }
            default: return new WP_Error( 'unkown_webhook_type', 'Unkown webhook type', array( 'status' => 401 ) );
        } 
    }

    public static function validate_incoming_post_request(WP_REST_Request $request) {
        $key = $request->get_param( 'key' );
        $secret = $request->get_param( 'secret' );
        if ( $key && !empty($key) ) {
            $exists = array_key_exists($key, self::$webhooks_list);
            if ($exists) {
                $webhook = (array)self::$webhooks_list[$key];
                return $webhook['secret'] == $secret;
            }
        }
        return false;
    }
}

Athena_Webhooks::init();
