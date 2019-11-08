<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Athena_Api {

	/**
	 * Get current user IP.
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_ip() {
		return ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : __( 'Unknown', 'athena' );
	}


	/**
	 * Check wether setting is defined globally in wp-config.php
	 *
	 * @since 1.0
	 * @param  string  $key settings key
	 * @return boolean
	 */
	public static function is_global( $key ) {
		return defined( $key );

	}


	/**
	 * Get plugin settings array.
	 *
	 * @since 1.0
	 * @return array
	 */
	public static function get_db_settings() {
		return get_option( 'athena_settings' );
	}

	public static function set_db_setting($key, $value) {
		$settings = get_option( 'athena_settings' );
		$settings[$key] = $value;
		update_option('athena_settings', $settings);
		return $settings;
	}

	/**
	 * Get the auth key.
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_key() {
		if ( defined( 'ATHENA_SIMPLE_JWT_AUTHENTICATION_SECRET_KEY' ) ) {
			return ATHENA_SIMPLE_JWT_AUTHENTICATION_SECRET_KEY;
		} else {
			$settings = self::get_db_settings();
			if ( $settings ) {
				return $settings['secret_key'];
			}
		}
		return false;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_firebase_cached_public_keys() {
		$settings = self::get_db_settings();
		if ( $settings ) {
			return $settings['firebase_cached_public_keys'];
		}
		return null;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function set_firebase_cached_public_keys($value) {
		self::set_db_setting('firebase_cached_public_keys', $value);
	}

	public static function get_use_firebase_jwt() {
		$settings = self::get_db_settings();
		if ( $settings ) {
			return $settings['use_firebase_jwt'];
		}
		return null;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_firebase_app_id() {
		$settings = self::get_db_settings();
		if ( $settings ) {
			return $settings['firebase_app_id'];
		}
		return null;
	}

	/**
	 * Get the Access Control Allow Origin Setting
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_access_control_allow_origin() {
		$settings = self::get_db_settings();
		if ( $settings ) {
			return $settings['access_control_allow_origin'];
		}
		return false;
	}

	/**
	 * Get the Access Control Allow Origin Setting
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_app_modules_post_type_enabled() {
		$settings = self::get_db_settings();
		if ( $settings ) {
			return $settings['app_modules_post_type_enabled'];
		}
		return false;
	}

	/**
	 * Get CORS enabled/disabled
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_cors() {
		if ( defined( 'ATHENA_SIMPLE_JWT_AUTHENTICATION_CORS_ENABLE' ) ) {
			return ATHENA_SIMPLE_JWT_AUTHENTICATION_CORS_ENABLE;
		} else {
			$settings = self::get_db_settings();
			if ( $settings ) {
				return $settings['enable_cors'];
			}
		}
		return false;

	}

	public static function get_levels_with_pages() {
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		$levels = wlmapi_get_levels()['levels']['level'];
		$levels_with_pages = array();
		foreach($levels as $level){
			$pages = wlmapi_get_level_pages($level['id'])['pages']['page'];
			$levels_with_pages[$level['id']] = $pages;
		}
     	return $levels_with_pages;
	}

	public static function get_user_levels($id) {
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		return wlmapi_get_member($id)['member'][0]['Levels'];
	}

	public static function get_user_levels_pages($id) {
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		$levels = wlmapi_get_member($id)['member'][0]['Levels'];
		$return = array();
		foreach($levels as $level){
			$pages = wlmapi_get_level_pages($level->Level_ID)['pages']['page'];
			$return[$level->Level_ID] = array();
			$return[$level->Level_ID]['Level'] = $level;
			$return[$level->Level_ID]['Pages'] = $pages;
		}
     	return $return;
	}
}
