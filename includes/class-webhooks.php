<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Athena_Webhooks
{
    protected static $webhooks_list;

    public static function init()
    {
        self::$webhooks_list = Athena_Api::get_webhooks_list();
    }

    public static function incoming_request($request)
    {
        $key = $request->get_param('key');
        $test = $request->get_query_params()['test'];
        $ignore_errors = $request->get_query_params()['test'];
        $webhook = (array) self::$webhooks_list[$key];
        $template = $webhook['template'];
        $use_php_eval_in_template = $webhook['use_php_eval_in_template'];
        $parsed = null;
        $decoded = null;
        try {
            if ($use_php_eval_in_template) {
                $parsed = $template;
                $decoded = eval($parsed);
            } else {
                $params = $request->get_params();
                $parsed = preg_replace_callback('/{{((?:[^}]|}[^}])+)}}/', function ($match) use ($params) {
                    $value = Athena_Api::path_get($params, $match[1]);
                    return json_encode($value);
                }, $template);

                $decoded = json_decode($parsed, true);
            }
        } catch (Exception $err) {
            if ($ignore_errors) {
                return new WP_REST_Response(array(
                    'success' => true,
                    'exit_silently' => true,
                    'message' => 'error was ignore: (code: ' . $err->get_error_code() . ', message: ' . $err->get_error_message() . ')'
                ), 204);
            } else {
                $data = $err->get_error_data();
                $data['status'] = 401;
                return new WP_error(
                    $err->get_error_code(),
                    $err->get_error_message(),
                    $data
                );
            }


        }

        if ($test == '1') {
            return array(
                'webhook' => $webhook,
                'parsed_template' => $parsed,
                'decoded' => $decoded
            );
        }

        if ($decoded == null) {
            return new WP_REST_Response(array(
                'success' => true,
                'exit_silently' => true,
                'message' => 'Returning null from the json/php template will silently exit the request.'
            ), 204);
        }

        switch ($webhook['type']) {
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
                            $data
                        );
                    }
                    return new WP_REST_Response(array(
                        'user_id' => $userIdOrError,
                        'success' => true
                    ), 201);
                }
            default:
                return new WP_Error('unknown_webhook_type', 'Unknown webhook type', array('status' => 401));
        }
    }

    public static function validate_incoming_request(WP_REST_Request $request)
    {
        $key = $request->get_param('key');
        $secret = $request->get_param('secret');
        if ($key && !empty($key)) {
            $exists = array_key_exists($key, self::$webhooks_list);
            if ($exists) {
                $webhook = (array) self::$webhooks_list[$key];
                return $webhook['secret'] == $secret
                    && $webhook['method'] === strtoupper('POST');
            }
        }
        return false;
    }
}

Athena_Webhooks::init();
