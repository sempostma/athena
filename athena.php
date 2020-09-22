<?php

/**
 * Plugin Name: 				Athena
 * Plugin URI:  				https://github.com/sempostma/athena
 * Description: 				Bridges the gap between Wordpress and Athena Apps
 * Requires at least: 	5.1
 * Required WP: 				5.1
 * Tested up to: 				5.2
 * Tested WP:						5.2
 * Requires PHP: 				5.6
 * Version:     				0.4.9
 * Author:      				Sem Postma
 * Author URI:  				https://github.com/sempostma
 * License:     				MIT
 * License URI: 				https://opensource.org/licenses/MIT
 * Domain Path: 				/languages
 * 
 * @since 1.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

load_plugin_textdomain('athena', false, dirname(plugin_basename(__FILE__)) . '/languages/');

// Only include the file if we actually have the WP_REST_Controller class.
if (class_exists('WP_REST_Controller')) {
	require_once('includes/class-rest-api-filter-fields.php');
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}

class Athena
{

	protected $plugin_name;
	protected $plugin_version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 */
	public function __construct()
	{

		$this->plugin_name    = 'athena';
		$this->plugin_version = '0.4.9';

		// Load all dependency files.
		$this->load_dependencies();

		// Activation hook
		register_activation_hook(__FILE__, array($this, 'activate'));

		// Deactivation hook
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		// Localization
		// add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Loads all dependencies in our plugin.
	 *
	 * @since 1.0
	 */
	public function load_dependencies()
	{

		// Load all Composer dependencies
		$this->include_file('vendor/autoload.php');
		$this->include_file('class-athena-cache.php');
		$this->include_file('class-ess-soundtracks-provider.php');
		$this->include_file('class-acf-extension.php');
		$this->include_file('class-athena-api.php');

		// Admin specific includes
		if (is_admin()) {
			$this->include_file('admin/class-athena-settings.php');
			$this->include_file('admin/class-athena-tools.php');
			$this->include_file('admin/class-athena-profile.php');
			$this->include_file('admin/class-app-modules-importer.php');
			$this->include_update_checker();
		}

		$this->include_file('class-athena-rest.php');
		$this->include_file('class-app-module-post-type.php');
		$this->include_file('class-firebase-verify-id-tokens.php');
		$this->include_file('class-webhooks.php');
		$this->include_file('class-triggers.php');
		$this->include_file('json-dump.php');
	}

	private function include_update_checker()
	{
		require plugin_dir_path(__FILE__) . 'plugin-update-checker-4.9/plugin-update-checker.php';

		$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/sempostma/athena',
			__FILE__,
			'athena'
		);

		$myUpdateChecker->getVcsApi()->enableReleaseAssets();
	}

	/**
	 * Includes a single file located inside /includes.
	 *
	 * @param string $path relative path to /includes
	 * @since 1.0
	 */
	private function include_file($path)
	{
		$plugin_name    = $this->plugin_name;
		$plugin_version = $this->plugin_version;

		$includes_dir = trailingslashit(plugin_dir_path(__FILE__) . 'includes');
		if (file_exists($includes_dir . $path)) {
			include_once $includes_dir . $path;
		}
	}

	/**
	 * The code that runs during plugin activation.
	 *
	 * @since    1.0
	 */
	public function activate()
	{
		$htaccess = get_home_path() . ".htaccess";

		$lines = array();
		$lines = "
SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1

# Compression
<IfModule mod_deflate.c>
  # Compress HTML, CSS, JavaScript, Text, XML and fonts
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/rss+xml
  AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
  AddOutputFilterByType DEFLATE application/x-font
  AddOutputFilterByType DEFLATE application/x-font-opentype
  AddOutputFilterByType DEFLATE application/x-font-otf
  AddOutputFilterByType DEFLATE application/x-font-truetype
  AddOutputFilterByType DEFLATE application/x-font-ttf
  AddOutputFilterByType DEFLATE application/x-javascript
  AddOutputFilterByType DEFLATE application/xhtml+xml
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE font/opentype
  AddOutputFilterByType DEFLATE font/otf
  AddOutputFilterByType DEFLATE font/ttf
  AddOutputFilterByType DEFLATE image/svg+xml
  AddOutputFilterByType DEFLATE image/x-icon
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/javascript
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/xml
  AddOutputFilterByType DEFLATE application/json

  # Remove browser bugs (only needed for really old browsers)
  BrowserMatch ^Mozilla/4 gzip-only-text/html
  BrowserMatch ^Mozilla/4\.0[678] no-gzip
  BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
  Header append Vary User-Agent
</IfModule>

<IfModule mod_rewrite.c>

RewriteEngine On
RewriteBase /
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]

# (Apache) Always set these headers.
Header always set Access-Control-Allow-Credentials \"true\"
Header always set Access-Control-Allow-Origin \"*\"
Header merge Vary Origin
Header always set Access-Control-Allow-Methods \"POST, GET, OPTIONS, DELETE, PUT\"
Header always set Access-Control-Max-Age \"1000\"
Header always set Access-Control-Allow-Headers \"X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token\"
Header always set Access-Control-Expose-Headers: \"X-WP-Total, X-WP-TotalPages\"
 
# (Apache) Added a rewrite to respond with a 200 SUCCESS on every OPTIONS request.
RewriteEngine On
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

</IfModule>
";

		insert_with_markers($htaccess, $this->plugin_name, explode(PHP_EOL, $lines));

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$pieces_table = $wpdb->prefix . 'athena_pieces';
		$collections_table = $wpdb->prefix . 'athena_collections';
		$users_table = $wpdb->prefix . 'users';

		$sql = "
		CREATE TABLE IF NOT EXISTS $collections_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
			wp_user_id BIGINT(20) UNSIGNED,
			wp_user_email varchar(100),
			title VARCHAR(64) NOT NULL,
			json_schema MEDIUMTEXT,
			updated_at TIMESTAMP NOT NULL,
			deleted_at TIMESTAMP,
			created_at TIMESTAMP NOT NULL,
			FOREIGN KEY (wp_user_id) REFERENCES $users_table(id),
			CONSTRAINT json_schema_check CHECK (JSON_VALID(json_schema))
		) $charset_collate;";

		dbDelta($sql);

		$sql = "
		CREATE TABLE IF NOT EXISTS $pieces_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
			wp_user_id BIGINT(20) UNSIGNED,
			wp_user_email varchar(100),
			collection_id BIGINT(20) UNSIGNED NOT NULL,
			structure MEDIUMTEXT,
			updated_at TIMESTAMP NOT NULL,
			deleted_at TIMESTAMP,
			created_at TIMESTAMP NOT NULL,
			name VARCHAR(127) NOT NULL DEFAULT '',
			FOREIGN KEY (wp_user_id) REFERENCES $users_table(id),
			FOREIGN KEY (collection_id) REFERENCES $collections_table(id),
			CONSTRAINT structure_check CHECK (JSON_VALID(structure))
		) $charset_collate;";

		dbDelta($sql);
	}

	/**
	 * The code that runs during plugin deactivation.
	 *
	 * @since    1.0
	 */
	public function deactivate()
	{
		$htaccess = get_home_path() . ".htaccess";

		$lines = array();
		$lines[] = "";

		insert_with_markers($htaccess, $this->plugin_name, $lines);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0
	 */
	public function load_textdomain()
	{

		load_plugin_textdomain(
			'athena',
			false,
			basename(dirname(__FILE__)) . '/languages/'
		);
	}
}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0
 */
new Athena();
