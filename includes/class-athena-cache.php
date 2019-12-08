<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Athena_Cache {

  private static $cache = array();

  public static function cache_get($key) {
    $_key = strval($key);
    return array_key_exists($_key, self::$cache) ? self::$cache[$_key] : null;
  }

  public static function cache_put($key, $value) {
    $_key = strval($key);
    self::$cache[$_key] = $value;
  }
}
