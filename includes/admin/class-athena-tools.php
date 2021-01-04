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

class Athena_Tools
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
		add_action('admin_init', array($this, 'tools_init'));
	}

	public function tools_init() {
		add_action('admin_enqueue_scripts', array($this, 'tools_page_scripts'));
	}

	/**
	 * Adds the menu page to management.
	 *
	 * @since 1.0
	 */
	public function add_admin_menu()
	{

		add_management_page(
			'Athena',
			'Athena',
			'manage_options',
			'athena',
			array($this, 'athena_management_page')
		);
	}
	
	public function tools_page_scripts($hook_suffix)
	{
		if ($hook_suffix === 'settings_page_athena') {
			wp_register_script('athena_settings_page', plugin_dir_url(__FILE__) . 'views/tools/athena-tools-page.js');
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
	 * tools form callback.
	 *
	 * @since 1.0
	 */
	public function athena_management_page()
	{
		$dashboard_url = Athena_Api::get_dashboard_url();
		
		$path = plugin_dir_path(__FILE__) . 'views/tools/page.php';
		if (file_exists($path)) {
			include_once $path;
		}

	}
}

new Athena_Tools($plugin_name, $plugin_version);
