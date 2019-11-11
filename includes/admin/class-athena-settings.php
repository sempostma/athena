<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The user profile specific functionality of the plugin.
 *
 * @since 1.0
 */

class athena_Settings {

	protected $plugin_name;
	protected $plugin_version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		$this->plugin_name    = $plugin_name;
		$this->plugin_version = $plugin_version;

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

	}


	/**
	 * Adds the menu page to options.
	 *
	 * @since 1.0
	 */
	public function add_admin_menu() {
		add_options_page(
			'Athena',
			'Athena',
			'manage_options',
			'athena',
			array( $this, 'athena_options_page' )
		);

	}


	/**
	 * Initialize all settings.
	 *
	 * @since 1.0
	 */
	public function settings_init() {
		register_setting( 'athena', 'athena_settings' );

		add_settings_section(
			'athena_section',
			__( 'Basic configuration', 'athena' ),
			array( $this, 'settings_section_callback' ),
			'athena'
		);

		add_settings_field(
			'secret_key',
			__( 'Secret Key', 'athena' ),
			array( $this, 'settings_secret_callback' ),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'app_modules_post_type_enabled',
			__( 'App modules post type enabled', 'athena' ),
			array( $this, 'settings_app_modules_post_type_enabled_callback' ),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'app_modules_post_type_force_private',
			__( 'Force all App Modules to be private when saved', 'athena' ),
			array( $this, 'settings_app_modules_post_type_force_private_callback' ),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'use_firebase_jwt',
			__( 'Use Firebase JWT', 'athena' ),
			array( $this, 'settings_use_firebase_jwt_callback' ),
			'athena',
			'athena_section'
		);

		add_settings_field(
			'firebase_app_id',
			__( 'Firebase App ID', 'athena' ),
			array( $this, 'settings_firebase_app_id_callback' ),
			'athena',
			'athena_section'
		);


		
	}


	/**
	 * Secret key field callback.
	 *
	 * @since 1.0
	 */
	public function settings_secret_callback() {
		$secret_key = Athena_Api::get_key();
		$is_global  = Athena_Api::is_global( 'ATHENA_SECRET_KEY' );
		include plugin_dir_path( __FILE__ ) . 'views/settings/secret-key.php';

	}


	/**
	 * Enable/disable cors field callback.
	 *
	 * @since 1.0
	 */
	public function settings_cors_callback() {
		$enable_cors = Athena_Api::get_cors();
		$is_global   = Athena_Api::is_global( 'ATHENA_CORS_ENABLE' );
		include plugin_dir_path( __FILE__ ) . 'views/settings/enable-cors.php';
	}

	/**
	 * Access Control Allow Origin field callback.
	 *
	 * @since 1.0
	 */
	public function settings_access_control_allow_origin_callback() {
		$access_control_allow_origin = Athena_Api::get_access_control_allow_origin();
		include plugin_dir_path( __FILE__ ) . 'views/settings/access_control_allow_origin.php';
	}

	public function settings_app_modules_post_type_enabled_callback() {
		$app_modules_post_type_enabled = Athena_Api::get_app_modules_post_type_enabled();
		include plugin_dir_path( __FILE__ ) . 'views/settings/app_modules_post_type_enabled.php';
	}

	public function settings_firebase_app_id_callback() {
		$firebase_app_id = Athena_Api::get_firebase_app_id();
		include plugin_dir_path( __FILE__ ) . 'views/settings/firebase_app_id.php';
	}

	public function settings_use_firebase_jwt_callback() {
		$use_firebase_jwt = Athena_Api::get_use_firebase_jwt();
		include plugin_dir_path( __FILE__ ) . 'views/settings/use_firebase_jwt.php';
	}	

	public function settings_app_modules_post_type_force_private_callback() {
		$app_modules_post_type_force_private = Athena_Api::get_app_modules_post_type_force_private();
		include plugin_dir_path( __FILE__ ) . 'views/settings/app_modules_post_type_force_private.php';
	}

	

	/**
	 * Section callback.
	 *
	 * @since 1.0
	 */
	public function settings_section_callback() {
		echo sprintf( __( 'This is all you need to start using JWT authentication.<br /> You can also specify these in wp-config.php instead using %1$s %2$s', 'athena' ), "<br /><br /><code>define( 'athena_SECRET_KEY', YOURKEY );</code>", "<br /><br /><code>define( 'athena_CORS_ENABLE', true );</code>" ); // phpcs:ignore

	}

	/**
	 * Settings form callback.
	 *
	 * @since 1.0
	 */
	public function athena_options_page() {
		include plugin_dir_path( __FILE__ ) . 'views/settings/page.php';
	}


}

new athena_Settings( $plugin_name, $plugin_version );
