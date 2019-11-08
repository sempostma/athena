<?php
/**
 * Plugin Name: Athena
 * Plugin URI:  https://github.com/sempostma
 * Description: Bridges the gap between wordpress and Athena Apps
 * Version:     0.3.8
 * Author:      Sem Postma
 * Author URI:  http://github.com/LesterGallagher
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @since 1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}

// Only include the file if we actually have the WP_REST_Controller class.
if(class_exists( 'WP_REST_Controller' )){
  require_once('includes/class-rest-api-filter-fields.php');
}

class Athena {

	protected $plugin_name;
	protected $plugin_version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->plugin_name    = 'athena';
		$this->plugin_version = '0.3.8';

		// Load all dependency files.
		$this->load_dependencies();

		// Activation hook
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Deactivation hook
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Localization
		// add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Loads all dependencies in our plugin.
	 *
	 * @since 1.0
	 */
	public function load_dependencies() {

		// Load all Composer dependencies
		$this->include_file( 'vendor/autoload.php' );
		$this->include_file( 'class-athena-api.php' );
		
		// Admin specific includes
		if ( is_admin() ) {
			$this->include_file( 'admin/class-athena-settings.php' );
			$this->include_file( 'admin/class-athena-profile.php' );
		}
		
		$this->include_file( 'class-athena-rest.php' );
		$this->include_file( 'class-app-module-post-type.php' );
		$this->include_file( 'class-firebase-verify-id-tokens.php' );
	}

	/**
	 * Includes a single file located inside /includes.
	 *
	 * @param string $path relative path to /includes
	 * @since 1.0
	 */
	private function include_file( $path ) {
		$plugin_name    = $this->plugin_name;
		$plugin_version = $this->plugin_version;

		$includes_dir = trailingslashit( plugin_dir_path( __FILE__ ) . 'includes' );
		if ( file_exists( $includes_dir . $path ) ) {
			include_once $includes_dir . $path;
		}
	}

	/**
	 * The code that runs during plugin activation.
	 *
	 * @since    1.0
	 */
	public function activate() {
		$htaccess = get_home_path().".htaccess";

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
Header always set Access-Control-Allow-Origin \"%{HTTP_ORIGIN}e\" env=HTTP_ORIGIN
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

		insert_with_markers($htaccess, $this->plugin_name, explode(PHP_EOL,$lines));
	}

	/**
	 * The code that runs during plugin deactivation.
	 *
	 * @since    1.0
	 */
	public function deactivate() {
		$htaccess = get_home_path().".htaccess";

		$lines = array();
		$lines[] = "";

		insert_with_markers($htaccess, $this->plugin_name, $lines);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0
	 */
	public function load_textdomain() {

		load_plugin_textdomain(
			'athena',
			false,
			basename( dirname( __FILE__ ) ) . '/languages/'
		);

	}

}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0
 */
new Athena();
