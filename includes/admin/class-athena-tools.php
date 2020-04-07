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

	/**
	 * tools form callback.
	 *
	 * @since 1.0
	 */
	public function athena_management_page()
	{
    $dashboard_url = Athena_Api::get_dashboard_url();
	}
}

new Athena_Tools($plugin_name, $plugin_version);
