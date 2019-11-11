<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Athena_Firebase_Verify_Id_Tokens_Api {

  protected static $plugin_name;
	protected static $plugin_version;
	protected static $firebase_app_id;
	protected static $use_firebase_jwt;

	public static function init($plugin_name, $plugin_version)
  {
		self::$plugin_name    = $plugin_name;
		self::$plugin_version = $plugin_version;
		self::$firebase_app_id = Athena_Api::get_firebase_app_id();
    self::$use_firebase_jwt = Athena_Api::get_use_firebase_jwt();
  }

  public static function get_firebase_public_keys() {
    $pkeys = Athena_Api::get_firebase_cached_public_keys();
    if ($pkeys != null) {
      return $pkeys;
    } else {
      $pkeys_raw = file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
      $pkeys = json_decode($pkeys_raw, true);
      Athena_Api::set_firebase_cached_public_keys($pkeys);
      return $pkeys;
    }
  }

  public static function get_iss_token() {
    return "https://securetoken.google.com/" . self::$firebase_app_id;
  }
}

Athena_Firebase_Verify_Id_Tokens_Api::init($plugin_name, $plugin_version);
