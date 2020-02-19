<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Most of the core functionality of the plugin happen here.
 *
 * @since 1.0
 */

// Require the JWT library.
use \Firebase\JWT\JWT;

use \Ramsey\Uuid\Uuid;

class Athena_Rest
{
	protected $plugin_name;
	protected $plugin_version;
	protected $namespace;

	/**
	 * Store errors to display if the JWT is wrong
	 *
	 * @var WP_Error
	 */
	private $jwt_error = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 */
	public function __construct($plugin_name, $plugin_version)
	{
		$this->plugin_name    = $plugin_name;
		$this->plugin_version = $plugin_version;
		$this->api_version    = 1;
		$this->namespace      = $plugin_name . '/v' . $this->api_version;

		$this->init();
	}


	/**
	 * Initialize this class with hooks.
	 *
	 * @return void
	 */
	public function init()
	{
		add_action('rest_api_init', array($this, 'add_api_routes'));
		add_filter('rest_pre_dispatch', array($this, 'rest_pre_dispatch'), 10, 2);
		$this->gutenberg_compatibility();
	}

	/**
	 * Fix gutenberg compatiblity.
	 * Make sure the JWT token is only looked for and applied if the user is not already logged in.
	 * TODO: Look into if we really need to use cookies for this...?
	 *
	 * @return void
	 */
	public function gutenberg_compatibility()
	{
		// If logged in cookie exists bail early.
		foreach ($_COOKIE as $name => $value) {
			if (0 === strpos($name, 'wordpress_logged_in_')) {
				return;
			}
		}

		add_filter('determine_current_user', array($this, 'determine_current_user'), 10);
	}

	private function should_add_options_endpoint()
	{
		$user_roles = wp_get_current_user()->roles;
		return in_array('administrator', $user_roles);
	}

	/**
	 * Add the endpoints to the API
	 */
	public function add_api_routes()
	{
		if ($this->should_add_options_endpoint()) {
			register_rest_route($this->namespace, '/options', array(
				'methods'  => 'GET',
				'callback' => array($this, 'get_db_settings'),
			));
		}

		if (!Athena_Api::get_disable_legacy_support()) {
			register_rest_field(
				'page',
				'meta',
				array('get_callback' => self::class . '::show_post_meta')
			);

			register_rest_field(
				'post',
				'meta',
				array('get_callback' => self::class . '::show_post_meta')
			);

			register_rest_field(
				'term',
				'meta',
				array('get_callback' => self::class . '::show_term_meta')
			);
		}

		register_rest_field(
			'page',
			'fields',
			array('get_callback' => self::class . '::show_post_meta')
		);

		register_rest_field(
			'post',
			'fields',
			array('get_callback' => self::class . '::show_post_meta')
		);

		register_rest_field(
			'term',
			'fields',
			array('get_callback' => self::class . '::show_term_meta')
		);

		if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
			register_rest_field(
				'page',
				'acf',
				array('get_callback' => self::class . '::show_post_fields')
			);

			register_rest_field(
				'post',
				'acf',
				array('get_callback' => self::class . '::show_post_fields')
			);

			register_rest_field(
				'term',
				'acf',
				array('get_callback' => self::class . '::show_taxonomy_fields')
			);
		}

		register_rest_route($this->namespace, '/menus', array(
			'methods'  => 'GET',
			'callback' => array($this, 'wp_api_v2_menus_get_all_menus'),
		));

		register_rest_route($this->namespace, '/menus/(?P<id>[a-zA-Z0-9_-]+)', array(
			'methods'  => 'GET',
			'callback' => array($this, 'wp_api_v2_menus_get_menu_data'),
		));

		register_rest_route($this->namespace, '/menu-locations/(?P<id>[a-zA-Z0-9_-]+)', array(
			'methods'  => 'GET',
			'callback' => array($this, 'wp_api_v2_locations_get_menu_data'),
		));

		register_rest_route($this->namespace, '/menu-locations', array(
			'methods'  => 'GET',
			'callback' => array($this, 'wp_api_v2_menu_get_all_locations'),
		));

		if (function_exists('wlmapi_get_levels')) {
			register_rest_route(
				$this->namespace,
				'wlm/levels/pages',
				array(
					'methods'  => 'GET',
					'callback' => array($this, 'get_levels_pages'),
				)
			);

			register_rest_route(
				$this->namespace,
				'wlm/levels/user/(?P<id>[\d]+)',
				array(
					'methods'  => 'GET',
					'callback' => array($this, 'get_user_levels'),
				)
			);

			register_rest_route(
				$this->namespace,
				'wlm/levels/pages/user/(?P<id>[\d]+)',
				array(
					'methods'  => 'GET',
					'callback' => array($this, 'get_user_levels_pages'),
				)
			);
		}

		register_rest_route(
			$this->namespace,
			'token',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'generate_token'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/validate',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'validate_token'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/refresh',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'refresh_token'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/revoke',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'revoke_token'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/resetpassword',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'reset_password'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/firebase/pkeys',
			array(
				'methods'  => 'GET',
				'callback' => array($this, 'get_firebase_pkeys'),
			)
		);

		register_rest_route(
			$this->namespace,
			'token/firebase/verify',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'verify_firebase_id_token'),
			)
		);

		register_rest_route(
			$this->namespace,
			'webhooks/incoming',
			array(
				'methods'  => 'POST',
				'callback' => array(Athena_Webhooks::class, 'incoming_request'),
				'permission_callback' => array(Athena_Webhooks::class, 'validate_incoming_request'),
			)
		);

		register_rest_route(
			$this->namespace,
			'webhooks/incoming',
			array(
				'methods'  => 'GET',
				'callback' => array(Athena_Webhooks::class, 'incoming_request'),
				'permission_callback' => array(Athena_Webhooks::class, 'validate_incoming_request'),
			)
		);
	}

	public function get_db_settings()
	{
		$options = Athena_Api::get_db_settings();
		return $options;
	}

	/**
	 * Get all registered menus
	 * @return array List of menus with slug and description
	 */
	public function wp_api_v2_menus_get_all_menus()
	{
		$menus = get_terms('nav_menu', array('hide_empty' => true));
		foreach ($menus as $key => $menu) {
			// check if there is acf installed
			if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
				$fields =  get_fields($menu);
				if (!empty($fields)) {
					foreach ($fields as $field_key => $item) {
						// add all acf custom fields
						$menus[$key]->$field_key = $item;
					}
				}
			}
		}

		return $menus;
	}

	/**
	 * Get all locations
	 * @return array List of locations
	 **/

	public function wp_api_v2_menu_get_all_locations()
	{
		$nav_menu_locations = get_nav_menu_locations();
		$locations          = new stdClass;
		foreach ($nav_menu_locations as $location_slug => $menu_id) {
			if (get_term($location_slug) !== null) {
				$locations->{$location_slug} = get_term($location_slug);
			} else {
				$locations->{$location_slug} = new stdClass;
			}
			$locations->{$location_slug}->slug = $location_slug;
			$locations->{$location_slug}->menu = get_term($menu_id);
		}

		return $locations;
	}

	/**
	 * Get menu's data from his id
	 *
	 * @param array $data WP REST API data variable
	 *
	 * @return object Menu's data with his items
	 */
	public function wp_api_v2_locations_get_menu_data($data)
	{
		// Create default empty object
		$menu = new stdClass;

		// this could be replaced with `if (has_nav_menu($data['id']))`
		if (($locations = get_nav_menu_locations()) && isset($locations[$data['id']])) {
			// Replace default empty object with the location object
			$menu        = get_term($locations[$data['id']]);
			$menu->items = $this->wp_api_v2_menus_get_menu_items($locations[$data['id']]);
		} else {
			return new WP_Error('not_found', 'No location has been found with this id or slug: `' . $data['id'] . '`. Please ensure you passed an existing location ID or location slug.', array('status' => 404));
		}

		// check if there is acf installed
		if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
			$fields =  get_fields($menu);
			if (!empty($fields)) {
				foreach ($fields as $field_key => $item) {
					// add all acf custom fields
					$menu->$field_key = $item;
				}
			}
		}

		return $menu;
	}

	/**
	 * Check if a menu item is child of one of the menu's element passed as reference
	 *
	 * @param $parents Menu's items
	 * @param $child Menu's item to check
	 *
	 * @return bool True if the parent is found, false otherwise
	 */
	public function wp_api_v2_menus_dna_test(&$parents, $child)
	{
		foreach ($parents as $key => $item) {
			if ($child->menu_item_parent == $item->ID) {
				if (!$item->child_items) {
					$item->child_items = [];
				}
				array_push($item->child_items, $child);
				return true;
			}

			if ($item->child_items) {
				if ($this->wp_api_v2_menus_dna_test($item->child_items, $child)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Retrieve items for a specific menu
	 *
	 * @param $id Menu id
	 *
	 * @return array List of menu items
	 */
	public function wp_api_v2_menus_get_menu_items($id)
	{
		$menu_items = wp_get_nav_menu_items($id);

		// check if there is acf installed
		if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
			foreach ($menu_items as $menu_key => $menu_item) {
				$fields =  get_fields($menu_item->ID);
				if (!empty($fields)) {
					foreach ($fields as $field_key => $item) {
						// add all acf custom fields
						$menu_items[$menu_key]->$field_key = $item;
					}
				}
			}
		}

		// wordpress does not group child menu items with parent menu items
		$child_items = [];
		// pull all child menu items into separate object
		foreach ($menu_items as $key => $item) {
			if ($item->menu_item_parent) {
				array_push($child_items, $item);
				unset($menu_items[$key]);
			}
		}

		// push child items into their parent item in the original object
		do {
			foreach ($child_items as $key => $child_item) {
				if ($this->wp_api_v2_menus_dna_test($menu_items, $child_item)) {
					unset($child_items[$key]);
				}
			}
		} while (count($child_items));

		return array_values($menu_items);
	}

	/**
	 * Get menu's data from his id.
	 *    It ensures compatibility for previous versions when this endpoint
	 *    was allowing locations id in place of menus id)
	 *
	 * @param array $data WP REST API data variable
	 *
	 * @return object Menu's data with his items
	 */
	public function wp_api_v2_menus_get_menu_data($data)
	{
		// This ensure retro compatibility with versions `<= 0.5` when this endpoint
		//   was allowing locations id in place of menus id
		if (has_nav_menu($data['id'])) {
			$menu = $this->wp_api_v2_locations_get_menu_data($data);
		} else if (is_nav_menu($data['id'])) {
			if (is_int($data['id'])) {
				$id = $data['id'];
			} else {
				$id = wp_get_nav_menu_object($data['id']);
			}
			$menu        = get_term($id);
			$menu->items = $this->wp_api_v2_menus_get_menu_items($id);
		} else {
			return new WP_Error('not_found', 'No menu has been found with this id or slug: `' . $data['id'] . '`. Please ensure you passed an existing menu ID, menu slug, location ID or location slug.', array('status' => 404));
		}

		// check if there is acf installed
		if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
			$fields =  get_fields($menu);
			if (!empty($fields)) {
				foreach ($fields as $field_key => $item) {
					// add all acf custom fields
					$menu->$field_key = $item;
				}
			}
		}

		return $menu;
	}

	public function get_pages($request)
	{
		return get_pages($request->get_query_params());
	}

	/**
	 * Gets all paegs indexed by level from the Wishlist member API
	 *
	 * @since 0.3.1
	 * @return String UUID
	 */
	public function get_levels_pages($request)
	{
		return Athena_Api::get_levels_with_pages();
	}

	public function get_user_levels($request)
	{
		return Athena_Api::get_user_levels($request['id']);
	}

	public function get_user_levels_pages($request)
	{
		return Athena_Api::get_user_levels_pages($request['id']);
	}

	/**
	 * Creates a new UUID to track the token. Attempts to generate the UUID using
	 * the WP built-in method first and falls back to ramsey/uuid
	 *
	 * @since 1.2
	 * @return String UUID
	 */
	private function generate_uuid()
	{
		if (function_exists('wp_generate_uuid4')) {
			//Use the built in UUID generator
			return wp_generate_uuid4();
		} else {
			//Old version of WP, use a different UUID generator
			return Uuid::uuid4()->toString();
		}
	}

	/**
	 * Get the user and password in the request body and generate a JWT
	 *
	 * @param object $request a WP REST request object
	 * @since 1.0
	 * @return mixed Either a WP_Error or current user data.
	 */
	public function generate_token($request)
	{
		$secret_key = Athena_Api::get_key();
		$username   = $request->get_param('username');
		$password   = $request->get_param('password');

		header("Access-Control-Allow-Origin: *");

		/** First thing, check the secret key if not exist return a error*/
		if (!$secret_key) {
			return new WP_Error(
				'jwt_auth_bad_config',
				__('JWT is not configurated properly, please contact the admin. The key is missing.', 'athena'),
				array(
					'status' => 403,
				)
			);
		}
		/** Try to authenticate the user with the passed credentials*/
		$user = wp_authenticate($username, $password);

		/** If the authentication fails return a error*/
		if (is_wp_error($user)) {
			$error_code = $user->get_error_code();
			return new WP_Error(
				'[jwt_auth] ' . $error_code,
				$user->get_error_message($error_code),
				array(
					'status' => 403,
				)
			);
		}

		// Valid credentials, the user exists create the according Token.
		$issued_at  = time();
		$not_before = apply_filters('jwt_auth_not_before', $issued_at);
		$expire     = apply_filters('jwt_auth_expire', $issued_at + (DAY_IN_SECONDS * 7), $issued_at, $user);
		$uuid       = $this->generate_uuid();

		$token = array(
			'uuid' => $uuid,
			'iss'  => get_bloginfo('url'),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $user->data->ID,
				),
			),
		);

		// Let the user modify the token data before the sign.
		$token = JWT::encode(apply_filters('jwt_auth_token_before_sign', $token, $user), $secret_key);

		// Setup some user meta data we can use for our UI.
		$jwt_data   = get_user_meta($user->data->ID, 'jwt_data', true) ?: array();
		$user_ip    = Athena_Api::get_ip();
		$jwt_data[] = array(
			'uuid'      => $uuid,
			'issued_at' => $issued_at,
			'expires'   => $expire,
			'ip'        => $user_ip,
			'ua'        => $_SERVER['HTTP_USER_AGENT'],
			'last_used' => time(),
		);
		update_user_meta($user->data->ID, 'jwt_data', apply_filters('simple_jwt_auth_save_user_data', $jwt_data));

		// The token is signed, now create the object with no sensible user data to the client.
		$data = array(
			'token'             => $token,
			'user_id'           => $user->data->ID,
			'user_email'        => $user->data->user_email,
			'user_nicename'     => $user->data->user_nicename,
			'user_display_name' => $user->data->display_name,
			'token_expires'     => $expire,
		);

		// Let the user modify the data before send it back.
		return apply_filters('jwt_auth_token_before_dispatch', $data, $user);
	}

	/**
	 * This is our Middleware to try to authenticate the user according to the
	 * token send.
	 *
	 * @param (int|bool) $user Logged User ID
	 * @since 1.0
	 * @return (int|bool)
	 */
	public function determine_current_user($user)
	{
		/**
		 * This hook only should run on the REST API requests to determine
		 * if the user in the Token (if any) is valid, for any other
		 * normal call ex. wp-admin/.* return the user.
		 *
		 * @since 1.2.3
		 **/
		$rest_api_slug = rest_get_url_prefix();
		$valid_api_uri = strpos($_SERVER['REQUEST_URI'], $rest_api_slug);
		if (!$valid_api_uri) {
			return $user;
		}

		/*
		 * if the request URI is for validate the token don't do anything,
		 * this avoid double calls to the validate_token function.
		 */
		$validate_uri = strpos($_SERVER['REQUEST_URI'], 'token/validate');
		if ($validate_uri > 0) {
			return $user;
		}
		$token = $this->validate_token(false);

		if (is_wp_error($token)) {
			if ($token->get_error_code() !== 'jwt_auth_no_auth_header') {
				// If there is a error, store it to show it after see rest_pre_dispatch
				$this->jwt_error = $token;
				return $user;
			} else {
				return $user;
			}
		}

		if (Athena_App_Module_Post_Type::is_enabled()) {
			$token = (array) $token;

			if (Athena_Api::get_jwt_email_verified_required() && array_key_exists('email_verified', $token) && $token['email_verified'] == false) {
				return $user;
			}
			$user = get_user_by('email', $token['email']);
			return $user->data->ID;
		} else {
			// Everything is ok, return the user ID stored in the token.
			return $token->data->user->id;
		}
	}

	/**
	 * Main validation function, this function try to get the Autentication
	 * headers and decoded.
	 *
	 * @param bool $output
	 * @since 1.0
	 * @return WP_Error | Object
	 */
	public function validate_token($output = true)
	{

		/*
		* Looking for the HTTP_AUTHORIZATION header, if not present just
		* return the user.
		*/

		$header_name = defined('SIMPLE_JWT_AUTHENTICATION_HEADER_NAME') ? SIMPLE_JWT_AUTHENTICATION_HEADER_NAME : 'HTTP_AUTHORIZATION';
		$auth        = isset($_SERVER[$header_name]) ? $_SERVER[$header_name] : false;

		// Double check for different auth header string (server dependent)
		if (!$auth) {
			$auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		if (!$auth) {
			return new WP_Error(
				'jwt_auth_no_auth_header',
				__('Authorization header not found.', 'athena'),
				array(
					'status' => 403,
				)
			);
		}


		/*
			* The HTTP_AUTHORIZATION is present verify the format
			* if the format is wrong return the user.
			*/
		list($token) = sscanf($auth, 'Bearer %s');
		if (!$token) {
			return new WP_Error(
				'jwt_auth_bad_auth_header',
				__('Authorization header malformed.', 'athena'),
				array(
					'status' => 403,
				)
			);
		}

		// Get the Secret Key
		$secret_key = Athena_Api::get_key();
		if (!$secret_key) {
			return new WP_Error(
				'jwt_auth_bad_config',
				__('JWT is not configurated properly, please contact the admin. The key is missing.', 'athena'),
				array(
					'status' => 403,
				)
			);
		}

		// Try to decode the token
		try {
			$use_firebase_jwt = Athena_Api::get_use_firebase_jwt();
			if ($use_firebase_jwt) {
				$secret_key = Athena_Firebase_Verify_Id_Tokens_Api::get_firebase_public_keys();
			}
			$token = JWT::decode($token, $secret_key, ["RS256"]);
			// The Token is decoded now validate the iss
			$iss = $use_firebase_jwt
				? Athena_Firebase_Verify_Id_Tokens_Api::get_iss_token()
				: get_bloginfo('url');

			if ($iss !== $token->iss) {
				// The iss do not match, return error
				return new WP_Error(
					'jwt_auth_bad_iss',
					__('The iss do not match with this server', 'athena'),
					array(
						'status' => 403,
					)
				);
			}

			if (!$use_firebase_jwt) {

				// So far so good, validate the user id in the token.
				if (!isset($token->data->user->id)) {
					return new WP_Error(
						'jwt_auth_bad_request',
						__('User ID not found in the token', 'athena'),
						array(
							'status' => 403,
						)
					);
				}

				// Custom validation against an UUID on user meta data.
				$jwt_data = get_user_meta($token->data->user->id, 'jwt_data', true) ?: false;
				if (false === $jwt_data) {
					return new WP_Error(
						'jwt_auth_token_revoked',
						__('Token has been revoked.', 'athena'),
						array(
							'status' => 403,
						)
					);
				}


				$valid_token = false;
				// Loop through and check wether we have the current token uuid in the users meta.
				foreach ($jwt_data as $key => $token_data) {
					if ($token_data['uuid'] === $token->uuid) {
						$user_ip                       = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : __('Unknown', 'athena');
						$jwt_data[$key]['last_used'] = time();
						$jwt_data[$key]['ua']        = $_SERVER['HTTP_USER_AGENT'];
						$jwt_data[$key]['ip']        = $user_ip;
						$valid_token                   = true;
						break;
					}
				}

				// Found no valid token. Return error.
				if (false === $valid_token) {
					return new WP_Error(
						'jwt_auth_token_revoked',
						__('Token has been revoked.', 'athena'),
						array(
							'status' => 403,
						)
					);
				}
			}

			// Everything looks good return the decoded token if the $output is false
			if (!$output) {
				return $token;
			}
			// If the output is true return an answer to the request to show it.
			return array(
				'code' => 'jwt_auth_valid_token',
				'data' => array(
					'status' => 200,
				),
			);
		} catch (Exception $e) {
			// Something is wrong trying to decode the token, send back the error.
			return new WP_Error(
				'jwt_auth_invalid_token',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}


	/**
	 * Get a JWT in the header and generate a JWT
	 *
	 * @return mixed Either a WP_Error or an object with a JWT token.
	 */
	public function refresh_token()
	{

		header("Access-Control-Allow-Origin: *");

		//Check if the token is valid and get user information
		$token = $this->validate_token(false);

		if (is_wp_error($token)) {
			return $token;
		}

		// Get the Secret Key
		$secret_key = Athena_Api::get_key();
		if (!$secret_key) {
			return new WP_Error(
				'jwt_auth_bad_config',
				__('JWT is not configurated properly, please contact the admin. The key is missing.', 'athena'),
				array(
					'status' => 403,
				)
			);
		}

		$user = new WP_User($token->data->user->id);

		// The user exists create the according Token.
		$issued_at  = time();
		$not_before = apply_filters('jwt_auth_not_before', $issued_at);
		$expire     = apply_filters('jwt_auth_expire', $issued_at + (DAY_IN_SECONDS * 7), $issued_at, $user);
		$uuid       = wp_generate_uuid4();

		$token = array(
			'uuid' => $uuid,
			'iss'  => get_bloginfo('url'),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $user->data->ID,
				),
			),
		);

		// Let the user modify the token data before the sign.
		$token = JWT::encode(apply_filters('jwt_auth_token_before_sign', $token, $user), $secret_key);

		// Setup some user meta data we can use for our UI.
		$jwt_data   = get_user_meta($user->data->ID, 'jwt_data', true) ?: array();
		$user_ip    = Athena_Api::get_ip();
		$jwt_data[] = array(
			'uuid'      => $uuid,
			'issued_at' => $issued_at,
			'expires'   => $expire,
			'ip'        => $user_ip,
			'ua'        => $_SERVER['HTTP_USER_AGENT'],
			'last_used' => time(),
		);
		update_user_meta($user->data->ID, 'jwt_data', apply_filters('simple_jwt_auth_save_user_data', $jwt_data));

		// The token is signed, now create the object with no sensible user data to the client.
		$data = array(
			'token'             => $token,
			'user_id'           => $user->data->ID,
			'user_email'        => $user->data->user_email,
			'user_nicename'     => $user->data->user_nicename,
			'user_display_name' => $user->data->display_name,
			'token_expires'     => $expire,
		);

		// Let the user modify the data before send it back.
		return apply_filters('jwt_auth_token_before_dispatch', $data, $user);
	}


	/**
	 * Check if we should revoke a token.
	 *
	 * @since 1.0
	 */
	public function revoke_token()
	{

		header("Access-Control-Allow-Origin: *");

		$token = $this->validate_token(false);

		if (is_wp_error($token)) {
			if ($token->get_error_code() !== 'jwt_auth_no_auth_header') {
				// If there is a error, store it to show it after see rest_pre_dispatch.
				$this->jwt_error = $token;
				return false;
			} else {
				return false;
			}
		}

		$tokens     = get_user_meta($token->data->user->id, 'jwt_data', true) ?: false;
		$token_uuid = $token->uuid;

		if ($tokens) {
			foreach ($tokens as $key => $token_data) {
				if ($token_data['uuid'] === $token_uuid) {
					unset($tokens[$key]);
					update_user_meta($token->data->user->id, 'jwt_data', $tokens);
					return array(
						'code' => 'jwt_auth_revoked_token',
						'data' => array(
							'status' => 200,
						),
					);
				}
			}
		}

		return array(
			'code' => 'jwt_auth_no_token_to_revoke',
			'data' => array(
				'status' => 403,
			),
		);
	}


	/**
	 * Endpoint for requesting a password reset link.
	 * This is a slightly modified version of what WP core uses.
	 *
	 * @param object $request The request object that come in from WP Rest API.
	 * @since 1.0
	 */
	public function reset_password($request)
	{

		header("Access-Control-Allow-Origin: *");

		$username = $request->get_param('username');
		if (!$username) {
			return array(
				'code'    => 'jwt_auth_invalid_username',
				'message' => __('<strong>Error:</strong> Username or email not specified.', 'athena'),
				'data'    => array(
					'status' => 403,
				),
			);
		} elseif (strpos($username, '@')) {
			$user_data = get_user_by('email', trim($username));
		} else {
			$user_data = get_user_by('login', trim($username));
		}

		global $wpdb, $current_site;

		do_action('lostpassword_post');
		if (!$user_data) {
			return array(
				'code'    => 'jwt_auth_invalid_username',
				'message' => __('<strong>Error:</strong> Invalid username.', 'athena'),
				'data'    => array(
					'status' => 403,
				),
			);
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action('retreive_password', $user_login);  // Misspelled and deprecated
		do_action('retrieve_password', $user_login);

		$allow = apply_filters('allow_password_reset', true, $user_data->ID);

		if (!$allow) {
			return array(
				'code'    => 'jwt_auth_reset_password_not_allowed',
				'message' => __('<strong>Error:</strong> Resetting password is not allowed.', 'athena'),
				'data'    => array(
					'status' => 403,
				),
			);
		} elseif (is_wp_error($allow)) {
			return array(
				'code'    => 'jwt_auth_reset_password_not_allowed',
				'message' => __('<strong>Error:</strong> Resetting password is not allowed.', 'athena'),
				'data'    => array(
					'status' => 403,
				),
			);
		}

		$key = get_password_reset_key($user_data);

		$message  = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		$message .= network_home_url('/') . "\r\n\r\n";
		// translators: %s is the users login name.
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

		if (is_multisite()) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		}
		// translators: %s is the sites name (blogname)
		$title = sprintf(__('[%s] Password Reset'), $blogname);

		$title   = apply_filters('retrieve_password_title', $title);
		$message = apply_filters('retrieve_password_message', $message, $key);

		if ($message && !wp_mail($user_email, $title, $message)) {
			wp_die(__('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...')); // phpcs:ignore
		}

		return array(
			'code'    => 'jwt_auth_password_reset',
			'message' => __('<strong>Success:</strong> an email for selecting a new password has been sent.', 'athena'),
			'data'    => array(
				'status' => 200,
			),
		);
	}

	/**
	 * Filter to hook the rest_pre_dispatch, if the is an error in the request
	 * send it, if there is no error just continue with the current request.
	 *
	 * @param $request
	 * @since 1.0
	 */
	public function rest_pre_dispatch($request)
	{
		if (is_wp_error($this->jwt_error)) {
			return $this->jwt_error;
		}
		return $request;
	}

	public function object_to_array($data)
	{
		if ((!is_array($data)) and (!is_object($data)))
			return 'xxx'; // $data;

		$result = array();

		$data = (array) $data;
		foreach ($data as $key => $value) {
			if (is_object($value))
				$value = (array) $value;
			if (is_array($value))
				$result[$key] = $this->object_to_array($value);
			else
				$result[$key] = $value;
		}
		return $result;
	}

	public function get_firebase_pkeys($request)
	{
		return Athena_Firebase_Verify_Id_Tokens_Api::get_firebase_public_keys();
	}

	public static function show_taxonomy_fields($object, $field_name, $request)
	{
		$fields = self::get_rest_request_fields($request);
		$acf = (object) get_fields($object['taxonomy'] . '_' . $object['id']);
		if (!isset($acf)) return;
		if ($fields && !in_array($field_name, $fields)) return;
		$filtered = array();
		foreach ($acf as $key => $value) {
			$should_show = !$fields || in_array($field_name . '.' . $key, $fields);
			if ($should_show) {
				$filtered[$key] = $value;
			}
		}
		return (object) $filtered;
	}

	public static function show_post_fields($object, $field_name, $request)
	{
		$fields = self::get_rest_request_fields($request);
		if ($fields && !in_array($field_name, $fields)) return;
		$acf = (object) get_fields($object['id']);
		if (!isset($acf)) return;
		$includeAll = !$fields || in_array($field_name, $fields);
		$filtered = array();
		if ($fields && !$includeAll) return;
		foreach ($acf as $key => $value) {
			$should_show = $includeAll || in_array($field_name . '.' . $key, $fields);
			if ($should_show) {
				$filtered[$key] = $value;
			}
		}
		return (object) $filtered;
	}

	public static function show_term_meta($object, $field_name, $request)
	{
		$fields = self::get_rest_request_fields($request);
		$meta = get_term_meta($object['id']);
		if ($fields && !in_array($field_name, $fields)) return;
		$includeAll = !$fields || in_array($field_name, $fields);
		$filtered = array();
		if ($fields && !$includeAll) return;
		foreach ($meta as $key => $value) {
			$should_show = $includeAll || in_array($field_name . '.' . $key, $fields);
			if (strpos($key, '_') !== 0 && $should_show) {
				$filtered[$key] = maybe_unserialize($value[0]);
			}
		}
		return (object) $filtered;
	}

	public static function get_rest_request_fields($request)
	{
		$fields = $request->get_param('fields');
		if ($fields) {
			$fields = str_replace(" ", "", $fields);
			$fields = explode(",", $fields);
		}
		return $fields;
	}

	public static function show_post_meta($object, $field_name, $request)
	{
		$fields = self::get_rest_request_fields($request);
		$meta = get_post_meta($object['id']);
		if ($fields && !in_array($field_name, $fields)) return;
		$includeAll = !$fields || in_array($field_name, $fields);
		$filtered = array();
		if ($fields && !$includeAll) return;
		foreach ($meta as $key => $value) {
			$should_show = $includeAll || in_array($field_name . '.' . $key, $fields);
			if (strpos($key, '_') !== 0 && $should_show) {
				$filtered[$key] = maybe_unserialize($value[0]);
			}
		}
		return (object) $filtered;
	}
}

new Athena_Rest($plugin_name, $plugin_version);
