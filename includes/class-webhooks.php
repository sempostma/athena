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
}

Athena_Webhooks::init();
