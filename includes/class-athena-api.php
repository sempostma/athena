<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Athena_Api
{

	/**
	 * Get current user IP.
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_ip()
	{
		return !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : __('Unknown', 'athena');
	}


	/**
	 * Check wether setting is defined globally in wp-config.php
	 *
	 * @since 1.0
	 * @param  string  $key settings key
	 * @return boolean
	 */
	public static function is_global($key)
	{
		return defined($key);
	}

	public static function path_get($obj, $key, $default = NULL)
	{
		try {
			$path = explode('.', $key);
			foreach ($path as $field) {
				$obj = ((array) $obj)[$field];
			}
			return $obj;
		} catch (Exception $error) {
			return $default;
		}
	}


	/**
	 * Get plugin settings array.
	 *
	 * @since 1.0
	 * @return array
	 */
	public static function get_db_settings()
	{
		$settings = Athena_Cache::cache_get('athena_settings');
		if ($settings) return $settings;
		$settings = get_option('athena_settings');
		Athena_Cache::cache_put('athena_settings', $settings);
		return $settings;
	}

	public static function set_db_setting($key, $value)
	{
		$settings = get_option('athena_settings');
		$settings[$key] = $value;
		update_option('athena_settings', $settings);
		return $settings;
	}

	public static function get_dashboard_url()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('dashboard_url', $settings)) {
			return $settings['dashboard_url'];
		} else return 'https://cms.esstudio.site';
	}

	/**
	 * Get the auth key.
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_key()
	{
		if (defined('ATHENA_SIMPLE_JWT_AUTHENTICATION_SECRET_KEY')) {
			return ATHENA_SIMPLE_JWT_AUTHENTICATION_SECRET_KEY;
		} else {
			$settings = self::get_db_settings();
			if ($settings) {
				return $settings['secret_key'];
			}
		}
		return false;
	}

	public static function http_response_header_as_associative_array($http_response_header)
	{
		$output = array();
		if ('HTTP' === substr($http_response_header[0], 0, 4)) {
			list(, $output['status'], $output['status_text']) = explode(' ', $http_response_header[0]);
			unset($http_response_header[0]);
		}
		foreach ($http_response_header as $v) {
			$h                         = explode(':', $v, 2);
			$output[strtolower($h[0])] = $h[1];
		}
		return $output;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_firebase_cached_public_keys()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('firebase_cached_public_keys', $settings)) {
			$entry = $settings['firebase_cached_public_keys'];
			$expires = $entry['expires'];
			$value = $entry['value'];
			if (is_numeric($expires) && time() < $expires) {
				return $value;
			}
		}
		return null;
	}

	public static function get_server_to_server_secret_key()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('server_to_server_secret_key', $settings)) {
			$value = $settings['server_to_server_secret_key'];
			return $value;
		}
		return null;
	}

	public static function get_jwt_secret_keys()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('jwt_secret_keys', $settings)) {
			$value = $settings['jwt_secret_keys'];
			return $value;
		}
		return array();
	}

	public static function get_app_modules_post_type_force_private()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('app_modules_post_type_force_private', $settings)) {
			return $settings['app_modules_post_type_force_private'];
		}
		return false;
	}

	public static function get_jwt_email_verified_required()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('jwt_email_verified_required', $settings)) {
			return $settings['jwt_email_verified_required'];
		}
		return false;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function set_firebase_cached_public_keys($value, $expires)
	{
		self::set_db_setting('firebase_cached_public_keys', array(
			"value" => $value,
			"expires" => $expires
		));
	}

	public static function get_use_firebase_jwt()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('use_firebase_jwt', $settings)) {
			return $settings['use_firebase_jwt'];
		}
		return null;
	}

	public static function get_show_acf_in_api()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('show_acf_in_api', $settings)) {
			return $settings['show_acf_in_api'];
		}
		return false;
	}

	public static function get_disable_legacy_support()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('disable_legacy_support', $settings)) {
			return $settings['disable_legacy_support'];
		}
		return false;
	}

	/**
	 * Get firebase public keys
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_firebase_app_id()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('firebase_app_id', $settings)) {
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
	public static function get_access_control_allow_origin()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('access_control_allow_origin', $settings)) {
			return $settings['access_control_allow_origin'];
		}
		return false;
	}

	public static function get_webhooks_list()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('webhooks_list', $settings)) {
			return (array) $settings['webhooks_list'];
		}
		return array();
	}

	public static function get_triggers_list()
	{
		$settings = self::get_db_settings();
		if ($settings && array_key_exists('triggers_list', $settings)) {
			return (array) $settings['triggers_list'];
		}
		return array();
	}

	/**
	 * Get the Access Control Allow Origin Setting
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_app_modules_post_type_enabled()
	{
		$settings = self::get_db_settings();
		if ($settings) {
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
	public static function get_cors()
	{
		if (defined('ATHENA_SIMPLE_JWT_AUTHENTICATION_CORS_ENABLE')) {
			return ATHENA_SIMPLE_JWT_AUTHENTICATION_CORS_ENABLE;
		} else {
			$settings = self::get_db_settings();
			if ($settings) {
				return $settings['enable_cors'];
			}
		}
		return false;
	}

	public static function get_levels_with_pages()
	{
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		$levels = wlmapi_get_levels()['levels']['level'];
		$levels_with_pages = array();
		foreach ($levels as $level) {
			$pages = wlmapi_get_level_pages($level['id'])['pages']['page'];
			$levels_with_pages[$level['id']] = $pages;
		}
		return $levels_with_pages;
	}

	public static function get_user_levels($id)
	{
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		return wlmapi_get_member($id)['member'][0]['Levels'];
	}

	public static function get_user_levels_pages($id)
	{
		if (!function_exists('wlmapi_get_levels')) return 'This site is not running wordpress plugin: "Wishlist Member"';
		$levels = wlmapi_get_member($id)['member'][0]['Levels'];
		$return = array();
		foreach ($levels as $level) {
			$pages = wlmapi_get_level_pages($level->Level_ID)['pages']['page'];
			$return[$level->Level_ID] = array();
			$return[$level->Level_ID]['Level'] = $level;
			$return[$level->Level_ID]['Pages'] = $pages;
		}
		return $return;
	}
}
