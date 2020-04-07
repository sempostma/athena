<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * The user profile specific functionality of the plugin.
 *
 * @since 1.0
 */

class Athena_Settings
{

	protected $plugin_name;
	protected $plugin_version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 */
	public function __construct($plugin_name, $plugin_version)
	{
		$this->plugin_name    = $plugin_name;
		$this->plugin_version = $plugin_version;

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'settings_init'));

		add_action('wp_ajax_athena_openssl_pkey_new', array($this, 'athena_openssl_pkey_new'));
	}


	/**
	 * Adds the menu page to options.
	 *
	 * @since 1.0
	 */
	public function add_admin_menu()
	{
		add_options_page(
			'Athena',
			'Athena',
			'manage_options',
			'athena',
			array($this, 'athena_options_page')
		);
	}

	public function athena_openssl_pkey_new()
	{
		$alg = $_REQUEST['alg'];

		if (substr($alg, 0, 2) === 'RS') {
			$config = array(
				"digest_alg" => "sha" . substr($alg, 2, 5),
				"private_key_bits" => 4096,
				"private_key_type" => OPENSSL_KEYTYPE_RSA
			);

			// Create the private and public key
			$res = openssl_pkey_new($config);

			if ($res === false && openssl_error_string()) return wp_send_json_error(array('errors' => array(0 => array('title' => openssl_error_string(), 'openssl_conf' => getenv('OPENSSL_CONF'),  'cwd' => getcwd(), 'meta' => $config))));

			$privKey = null;

			// Extract the private key from $res to $privKey
			$success = openssl_pkey_export($res, $privKey);
			if ($success === false && openssl_error_string()) return wp_send_json_error(array('errors' => array(0 => array('title' => openssl_error_string(), 'openssl_conf' => getenv('OPENSSL_CONF'),  'cwd' => getcwd(), 'meta' => $config))));

			// Extract the public key from $res to $pubKey
			$pubKey = openssl_pkey_get_details($res);

			if ($pubKey === false && openssl_error_string()) return wp_send_json_error(array('errors' => array(0 => array('title' => openssl_error_string(), 'openssl_conf' => getenv('OPENSSL_CONF'),  'cwd' => getcwd(), 'meta' => $config))));

			$pubKey = $pubKey["key"];

			wp_send_json_success(array(
				'public' => $pubKey,
				'secret' => $privKey,
				'config' => $config,
			));
		} else if (substr($alg, 0, 2) === 'HS') {
			wp_send_json_error(array('errors' => array(0 => array('title' => 'Unsupported'))));
		} else {
			wp_send_json_error(array('errors' => array(0 => array('title' => 'Unsupported'))));
		}
	}

	public function settings_page_scripts($hook_suffix)
	{
		if ($hook_suffix === 'settings_page_athena') {
			wp_register_script('athena_settings_page', plugin_dir_url(__FILE__) . 'athena-settings-page.js');
			// Localize the script with new data
			$translation_array = array(
				'a_value' => '10',
				'hook_suffix' => $hook_suffix,
				'ajaxurl' => admin_url('admin-ajax.php'),
				'delete' => __('Delete', 'athena'),
				'kid_label' => __('kid', 'athena')
			);
			wp_localize_script('athena_settings_page', 'athena_messages', $translation_array);
			wp_enqueue_script('athena_settings_page');
		}
	}

	/**
	 * Initialize all settings.
	 *
	 * @since 1.0
	 */
	public function settings_init()
	{
		register_setting('athena', 'athena_settings');

		add_action('admin_enqueue_scripts', array($this, 'settings_page_scripts'));

		add_settings_section(
			'athena_section',
			__('Basic configuration', 'athena'),
			array($this, 'settings_section_callback'),
			'athena'
		);

		add_settings_field(
			'dashboard_url',
			__('Dashboard url', 'athena'),
			array($this, 'dashboard_url_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'server_to_server_secret_key',
			__('JWT Server to Server authentication secret key', 'athena'),
			array($this, 'settings_server_to_server_secret_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'secret_key',
			__('JWT User authentication secret key', 'athena'),
			array($this, 'settings_secret_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'secret_key',
			__('JWT User authentication secret key', 'athena'),
			array($this, 'settings_secret_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'jwt_secret_keys',
			__('JWT authentication secret keys list', 'athena'),
			array($this, 'jwt_secret_keys_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'app_modules_post_type_enabled',
			__('App modules post type enabled', 'athena'),
			array($this, 'settings_app_modules_post_type_enabled_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'app_modules_post_type_force_private',
			__('Force all App Modules to be private when saved', 'athena'),
			array($this, 'settings_app_modules_post_type_force_private_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'jwt_email_verified_required',
			__('Require the user\'s email to be verified for Firebase JWT?', 'athena'),
			array($this, 'jwt_email_verified_required'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'use_firebase_jwt',
			__('Use Firebase JWT', 'athena'),
			array($this, 'settings_use_firebase_jwt_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'firebase_app_id',
			__('Firebase App ID', 'athena'),
			array($this, 'settings_firebase_app_id_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'webhooks_list',
			__('Webhooks', 'athena'),
			array($this, 'settings_webhooks_list_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'triggers_list',
			__('Triggers', 'athena'),
			array($this, 'settings_triggers_list_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'disable_legacy_support',
			__('Disable legacy support', 'athena'),
			array($this, 'settings_disable_legacy_support_callback'),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'show_acf_in_api',
			__('Show ACF in rest api', 'athena'),
			array($this, 'settings_show_acf_in_api_callback'),
			'athena',
			'athena_section'
		);
	}

	public function settings_show_acf_in_api_callback()
	{
		$show_acf_in_api = Athena_Api::get_show_acf_in_api();
		include plugin_dir_path(__FILE__) . 'views/settings/show_acf_in_api.php';
	}

	public function settings_disable_legacy_support_callback()
	{
		$disable_legacy_support = Athena_Api::get_disable_legacy_support();
		include plugin_dir_path(__FILE__) . 'views/settings/disable_legacy_support.php';
	}

	public function settings_webhooks_list_callback()
	{
		$webhooks_list = Athena_Api::get_webhooks_list();
		include plugin_dir_path(__FILE__) . 'views/settings/webhooks_list.php';
	}

	public function settings_triggers_list_callback()
	{
		$triggers_list = Athena_Api::get_triggers_list();
		include plugin_dir_path(__FILE__) . 'views/settings/triggers_list.php';
	}

	public function jwt_email_verified_required()
	{
		$jwt_email_verified_required = Athena_Api::get_jwt_email_verified_required();
		include plugin_dir_path(__FILE__) . 'views/settings/jwt_email_verified_required.php';
	}

	public function jwt_secret_keys_callback()
	{
		$jwt_secret_keys = Athena_Api::get_jwt_secret_keys();
		include plugin_dir_path(__FILE__) . 'views/settings/jwt_secret_keys.php';
	}

	/**
	 * Secret key field callback.
	 *
	 * @since 1.0
	 */
	public function settings_secret_callback()
	{
		$secret_key = Athena_Api::get_key();
		include plugin_dir_path(__FILE__) . 'views/settings/secret-key.php';
	}

	public function settings_server_to_server_secret_callback()
	{
		$server_to_server_secret_key = Athena_Api::get_server_to_server_secret_key();
		include plugin_dir_path(__FILE__) . 'views/settings/server_to_server_secret_key.php';
	}

	public function dashboard_url_callback()
	{
		$dashboard_url = Athena_Api::get_dashboard_url();
		include plugin_dir_path(__FILE__) . 'views/settings/dashboard_url.php';
	}


	/**
	 * Enable/disable cors field callback.
	 *
	 * @since 1.0
	 */
	public function settings_cors_callback()
	{
		$enable_cors = Athena_Api::get_cors();
		include plugin_dir_path(__FILE__) . 'views/settings/enable-cors.php';
	}

	/**
	 * Access Control Allow Origin field callback.
	 *
	 * @since 1.0
	 */
	public function settings_access_control_allow_origin_callback()
	{
		$access_control_allow_origin = Athena_Api::get_access_control_allow_origin();
		include plugin_dir_path(__FILE__) . 'views/settings/access_control_allow_origin.php';
	}

	public function settings_app_modules_post_type_enabled_callback()
	{
		$app_modules_post_type_enabled = Athena_Api::get_app_modules_post_type_enabled();
		include plugin_dir_path(__FILE__) . 'views/settings/app_modules_post_type_enabled.php';
	}

	public function settings_firebase_app_id_callback()
	{
		$firebase_app_id = Athena_Api::get_firebase_app_id();
		include plugin_dir_path(__FILE__) . 'views/settings/firebase_app_id.php';
	}

	public function settings_use_firebase_jwt_callback()
	{
		$use_firebase_jwt = Athena_Api::get_use_firebase_jwt();
		include plugin_dir_path(__FILE__) . 'views/settings/use_firebase_jwt.php';
	}

	public function settings_app_modules_post_type_force_private_callback()
	{
		$app_modules_post_type_force_private = Athena_Api::get_app_modules_post_type_force_private();
		include plugin_dir_path(__FILE__) . 'views/settings/app_modules_post_type_force_private.php';
	}



	/**
	 * Section callback.
	 *
	 * @since 1.0
	 */
	public function settings_section_callback()
	{
		// echo sprintf( __( 'This is all you need to start using JWT authentication.<br /> You can also specify these in wp-config.php instead using %1$s %2$s', 'athena' ), "<br /><br /><code>define( 'athena_SECRET_KEY', YOURKEY );</code>", "<br /><br /><code>define( 'athena_CORS_ENABLE', true );</code>" ); // phpcs:ignore

		$youHaveMadeAdjustmentsAreYouSureYouWantToQuit = __('You have made adjustments. Are you sure you want to quit', 'athena');

		echo __('<script>
			function enableTab (e) {
				if (e.keyCode === 9) { // tab was pressed
					e.preventDefault();

					// get caret position/selection
					var val = e.target.value,
							start = e.target.selectionStart,
							end = e.target.selectionEnd;

					// set textarea value to: text before caret + tab + text after caret
					e.target.value = val.substring(0, start) + \'\t\' + val.substring(end);

					// put caret at right position again
					e.target.selectionStart = e.target.selectionEnd = start + 1;

					// prevent the focus lose
					return false;
				}
			};
			(function() {
				var dirty = false;
				window.addEventListener("input", function(event) {
					dirty = true;
				});

				window.onbeforeunload = function(e) {
					console.log("dirty", dirty)
					if (dirty) {
						var dialogText = "' . $youHaveMadeAdjustmentsAreYouSureYouWantToQuit . '?";
						e.returnValue = dialogText;
						return dialogText;
					}
				};

				window.document.body.addEventListener("submit", function(event) {
					dirty = false;
				});
			})();
		</script>');

		echo '<pre><code>';
		JSON_Dump::dump(Athena_Api::get_db_settings());
		echo '</code></pre>';
	}

	/**
	 * Settings form callback.
	 *
	 * @since 1.0
	 */
	public function athena_options_page()
	{
		include plugin_dir_path(__FILE__) . 'views/settings/page.php';
	}
}

new Athena_Settings($plugin_name, $plugin_version);
