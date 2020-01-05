<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Athena_Triggers
{
    protected static $triggers_list;

    public static function init()
    {
        self::$triggers_list = Athena_Api::get_triggers_list();

        add_action('user_register', self::class . '::user_register', 10, 1);
    }

    public static function do_http_request($data, $trigger, $method, $blocking, $compress, $decompress, $url, $headers, $test)
    {
        global $wp_version;

        if ($test) $data = array(
            'data' => $data,
            'trigger' => $trigger,
            'method' => $method,
            'blocking' => $blocking,
            'compress' => $compress,
            'decompress' => $decompress,
            'url' => $url,
            'headers' => $headers,
            'test' => $test
        );

        if ($data == null) return; // return silently

        $args = array(
            'timeout'     => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
            'blocking'    => $blocking,
            'headers'     => (array) $headers,
            'cookies'     => array(),
            'body'        => $data,
            'compress'    => $compress,
            'decompress'  => $decompress,
            'sslverify'   => true,
            'stream'      => false,
            'filename'    => null
        );

        $response = null;

        if ($method === 'POST') {
            $response = wp_remote_post($url, $args);
        } else {
            $response = wp_remote_get($url, $args);
        }

        if (is_wp_error($response)) {
            $data = $response->get_error_data();
            $data['status'] = 401;
            return new WP_error(
                $response->get_error_code(),
                $response->get_error_message(),
                $data
            );
        }
    }

    public static function user_register($user_id)
    {
        $data = (array) get_userdata($user_id);

        foreach (self::$triggers_list as $key => $trigger) {
            $type = $trigger['type'];
            if ($type !== 'insert_user') continue;
            $template = $trigger['template'];
            $use_php_eval_in_template = array_key_exists('use_php_eval_in_template', $trigger) && $trigger['use_php_eval_in_template'] === 'true';
            $result = null;
            $method = $trigger['method'];
            $blocking = array_key_exists('blocking', $trigger) && $trigger['blocking'] === 'true';
            $compress = array_key_exists('compress', $trigger) && $trigger['compress'] === 'true';
            $decompress = array_key_exists('decompress', $trigger) && $trigger['decompress'] === 'true';
            $url = $trigger['url'];
            // $headers = $trigger['headers'];
            $headers = array();
            $test = array_key_exists('test', $trigger) && $trigger['test'] === 'true';

            try {
                if ($use_php_eval_in_template) {
                    $result = eval($template);
                } else {
                    $params = $data;
                    $parsed = preg_replace_callback('/{{((?:[^}]|}[^}])+)}}/', function ($match) use ($params) {
                        $value = Athena_Api::path_get($params, $match[1]);
                        return json_encode($value);
                    }, $template);

                    $result = json_decode($parsed, true);
                }
            } catch (Exception $err) {
                $err_data = $err->get_error_data();
                $err_data['status'] = 401;
                return new WP_error(
                    $err->get_error_code(),
                    $err->get_error_message(),
                    $err_data
                );
            }

            return self::do_http_request($result, $trigger, $method, $blocking, $compress, $decompress, $url, $headers, $test);
        }
    }
}

Athena_Triggers::init();
