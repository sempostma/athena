<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Athena_App_Module_Post_Type
{
	protected static $plugin_name;
	protected static $plugin_version;
	protected static $enabled;
	protected static $force_private;

	public static function is_enabled()
	{
		return self::$enabled;
	}

	public static function init($plugin_name, $plugin_version)
	{
		self::$plugin_name    = $plugin_name;
		self::$plugin_version = $plugin_version;
		self::$enabled = Athena_Api::get_app_modules_post_type_enabled();
		self::$force_private = Athena_Api::get_app_modules_post_type_force_private();

		if (self::$enabled) {
			add_action('init', [self::class, 'create_posttype']);
			add_action('init', [self::class, 'create_taxonomy']);
			add_action('admin_init', [self::class, 'add_capabilities']);
			if (self::$force_private) {
				add_filter('wp_insert_post_data', [self::class, 'force_type_private']);
			}
			add_action('rest_api_init', [self::class, 'rest_api_init']);
		}
	}

	static function force_type_private($post)
	{
		if ($post['post_type'] == 'app_modules' && $post['post_status'] == 'publish') {
			$post['post_status'] = 'private';
		}
		return $post;
	}

	public static function rest_api_init()
	{
		register_rest_field(
			'categories',
			'fields',
			array('get_callback' => Athena_Rest::class . '::show_term_meta')
		);

		if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
			register_rest_field(
				'categories',
				'acf',
				array('get_callback' => Athena_Rest::class . '::show_taxonomy_fields')
			);
		}

		if (self::should_add_posttype()) {
			register_rest_field(
				'app_modules',
				'fields',
				array('get_callback' => Athena_Rest::class . '::show_post_meta')
			);

			if (class_exists('acf') && Athena_API::get_show_acf_in_api()) {
				register_rest_field(
					'app_modules',
					'acf',
					array('get_callback' => Athena_Rest::class . '::show_post_fields')
				);
			}
		}
	}

	static function add_capabilities()
	{
		$roles = array('administrator', 'editor', 'author');

		foreach ($roles as $role_name) {
			$role = get_role($role_name);
			$role->add_cap('publish_app_modules', true);
			$role->add_cap('edit_app_modules', true);
			$role->add_cap('edit_others_app_modules', true);
			$role->add_cap('read_private_app_modules', true);
			$role->add_cap('edit_app_modules', true);
			$role->add_cap('delete_app_modules', true);
			$role->add_cap('read_app_modules', true);
		}
	}

	private static function should_add_posttype()
	{
		$user_roles = wp_get_current_user()->roles;
		return in_array('administrator', $user_roles)
			|| in_array('author', $user_roles)
			|| in_array('editor', $user_roles)
			|| in_array('app_module_user', $user_roles);
	}

	static function create_taxonomy()
	{
		$labels = array(
			'name' => _x('App Module Groups', 'taxonomy general name'),
			'singular_name' => _x('App Module Group', 'taxonomy singular name'),
			'search_items' =>  __('Search App Module Groups'),
			'all_items' => __('All App Module Groups'),
			'parent_item' => __('Parent App Module Group'),
			'parent_item_colon' => __('Parent App Module Group'),
			'edit_item' => __('Edit App Module Group'),
			'update_item' => __('Update App Module Group'),
			'add_new_item' => __('Add New App Module Group'),
			'new_item_name' => __('New App Module Group'),
			'menu_name' => __('App Module Groups'),
		);

		// Now register the taxonomy

		register_taxonomy('categories', 'app_modules', array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'app-module-groups'),
			'show_in_rest' => true,
			'rest_base' => 'app-module-groups'
		));
	}

	static function create_posttype()
	{
		if (!self::should_add_posttype()) return;

		// Add new taxonomy, make it hierarchical like categories
		//first do the translations part for GUI

		$supports = array(
			'title', // post title
			'editor', // post content
			'author', // post author
			'thumbnail', // featured images
			'excerpt', // post excerpt
			'categories', // post excerpt
			'page-attributes'
		);
		$labels = array(
			'name' => _x('App Modules', 'plural'),
			'singular_name' => _x('App Module', 'singular'),
			'menu_name' => _x('App Modules', 'admin menu'),
			'name_admin_bar' => _x('App Modules', 'admin bar'),
			"all_items" =>  _x('All App Modules', 'admin bar'),
			'add_new' => _x('Add App Module', 'add new'),
			'add_new_item' => __('Add New App Module'),
			'new_item' => __('New App Modules'),
			'edit_item' => __('Edit App Module'),
			'view_item' => __('View App Module'),
			'view' => __('View'),
			'search_items' => __('Search App Modules'),
			"not_found" => __("No App Modules Found"),
			"not_found_in_trash" => __("No App Modules Found in Trash"),
			'all_items' => __('All App Modules'),
			'search_items' => __('Search App Modules'),
			'not_found' => __('No App Modules found.'),
			'parent_item_colon' => __('Parent App Module'),
			"parent" => __('Parent App Module'),
		);
		$args = array(
			'supports' => $supports,
			'labels' => $labels,
			'public' => true,
			'capability_type' => 'app_modules',
			'show_ui' => true,
			"show_in_menu" => true,
			"exclude_from_search" => false,
			'capabilities' => array(
				'publish_posts' => 'publish_app_modules',
				'edit_posts' => 'edit_app_modules',
				'edit_others_posts' => 'edit_others_app_modules',
				'read_private_posts' => 'read_private_app_modules',
				'edit_post' => 'edit_app_modules',
				'delete_post' => 'delete_app_modules',
				'read_post' => 'read_app_modules',
			),
			'query_var' => true,
			"rewrite" => array("slug" => "app-modules", "with_front" => true),
			'has_archive' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-smartphone',
			'description' => 'App Modules to be used from an App.',
			'show_in_rest' => true,
			'can_export' => true,
			'rest_base' => 'app-modules',
			'taxonomies' => array('app-module-groups'),
		);

		register_post_type('app_modules', $args);

		add_role('app_module_user', 'App module user', array(
			'read' => false,
			'edit_posts' => false,
			'delete_posts' => false,
			'read_app_modules' => true,
			'read_private_app_modules' => true
		));

		get_terms('double-ipa', array('hide_empty' => false));
	}
}

Athena_App_Module_Post_Type::init($plugin_name, $plugin_version);
