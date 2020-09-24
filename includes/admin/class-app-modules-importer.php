<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!defined('WP_LOAD_IMPORTERS'))
	return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if (!class_exists('WP_Importer')) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if (file_exists($class_wp_importer))
		require_once $class_wp_importer;
}

/**
 * RSS App Modules Importer
 *
 * Will process a RSS feed for importing app modules into WordPress. This is a very
 * limited importer and should only be used as the last resort, when no other
 * importer is available.
 *
 * @since unknown
 */
if (class_exists('WP_Importer')) {
	class RSS_App_Modules_Import extends WP_Importer
	{

		var $posts = array();
		var $file;
		var $attachment_cache = array();
		var $attachment_replace = array();

		function header()
		{
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>' . __('Import App Modules RSS', 'athena') . '</h2>';
		}

		function footer()
		{
			echo '</div>';
		}

		function greet()
		{
			echo '<div class="narrow">';
			echo '<p>' . __('Howdy! This importer allows you to extract app module posts from an RSS 2.0 file into your WordPress site. This is useful if you want to import your posts from a system that is not handled by a custom import tool. Pick an RSS file to upload and click Import. You can even import media if you exported using "Export media with selected content".', 'athena') . '</p>';
			wp_import_upload_form("admin.php?import=rss_app_modules&amp;step=1");
			echo '</div>';
		}

		function _normalize_tag($matches)
		{
			return '<' . strtolower($matches[1]);
		}

		function uri_without_extension($path)
		{
			return implode(
				'.',
				array_slice(
					explode(
						'.',
						$path
					),
					0,
					-1
				)
			);
		}

		function import_attachments()
		{
			echo '<p>Starting to import attachments...</p>';
			echo '<ol>';

			foreach ($this->posts as $post) {
				if ($post['post_type'] !== 'attachment') continue;
				echo "<li>" . __('Importing attachment...', 'athena');

				$image_url = $post['post_image'];
				$upload_dir = wp_upload_dir();
				$filename = basename($image_url);
				if (wp_mkdir_p($upload_dir['path'])) {
					$file = $upload_dir['path'] . '/' . $filename;
					$url = $this->uri_without_extension($upload_dir['url'] . '/' . $filename);
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
					$url = $this->uri_without_extension($upload_dir['baseurl'] . '/' . $filename);
				}
				$file_already_existed = file_exists($file);
				$sanitized_filename = sanitize_file_name($filename);
				if (!$file_already_existed) {
					$wp_filetype = wp_check_filetype($filename, null);
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => $sanitized_filename,
						'post_content' => '',
						'post_status' => 'inherit',
						'file' => $file
					);
					$image_data = file_get_contents($image_url);
					file_put_contents($file, $image_data);
					$attach_id = wp_insert_attachment($attachment, $file);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata($attach_id, $file);
					wp_update_attachment_metadata($attach_id, $attach_data);
				}
				else {
					$attachment = get_page_by_title($sanitized_filename, OBJECT, 'attachment');
					$attach_id = $attachment->ID;
				}
				
				$this->attachment_cache[$image_url] = $attach_id;
				$this->attachment_cache[$post['post_id']] = $attach_id;
				$without_extension = $this->uri_without_extension($image_url);
				$this->attachment_replace[$without_extension] = $url;

				_e('Done!', 'athena');
				echo '</li>';
			}

			echo '</ol>';
			echo '<p>Finished importing attachments...</p>';
		}


		function get_posts()
		{
			global $wpdb;

			if (function_exists('set_magic_quotes_runtime')) {
				// PHP7: removes this. Retain compatibility.
				set_magic_quotes_runtime(0);
			}

			$rss = simplexml_load_file($this->file)->channel;
			$this->posts = array();
			foreach ($rss->item as $item) {
				$post = array();
				$namespaces = $item->getNameSpaces(true);
				$dc = false;
				if (!empty($namespaces['dc'])) {
					$dc = $item->children($namespaces['dc']);
				}
				$wp = false;
				if (!empty($namespaces['wp'])) {
					$wp = $item->children($namespaces['wp']);
				}

				$post['post_title'] = (string)$item->title;
				if ($wp->{'post_id'}) {
					$post['post_id'] = (string)$wp->{'post_id'};
					$post['ID'] = (string)$wp->{'post_id'};
				}
				if ($wp->{'attachment_url'}) {
					$post['post_image'] = (string)$wp->{'attachment_url'};
				}
				$post['post_parent'] = $wp->{'post_parent'};
				if ($post['post_parent']) $post['post_parent'] = (string)$post['post_parent'];
				$post['menu_order'] = $wp->{'menu_order'};
				if ($post['menu_order']) $post['menu_order'] = (string)$post['menu_order'];
				$post['post_password'] = $wp->{'post_password'};
				if ($post['post_password']) $post['post_password'] = (string)$post['post_password'];
				$post['is_sticky'] = $wp->{'is_sticky'};
				if ($post['is_sticky']) $post['is_sticky'] = (string)$post['is_sticky'];
				$post['ping_status'] = $wp->{'ping_status'};
				if ($post['ping_status']) $post['ping_status'] = (string)$post['ping_status'];
				$post['comment_status'] = $wp->{'comment_status'};
				if ($post['comment_status']) $post['comment_status'] = (string)$post['comment_status'];
				$post['post_type'] = $wp->{'post_type'};
				if ($post['post_type']) $post['post_type'] = (string)$post['post_type'];
				$post['meta_input'] = array();
				if ($wp->{'postmeta'}) {
					foreach ($wp->{'postmeta'} as $meta_input) {
						$meta_input = $meta_input->children($namespaces['wp']);
						$post['meta_input'][(string)$meta_input->{'meta_key'}] = (string)$meta_input->{'meta_value'};
					}
				}
				$post['post_date_gmt'] = $item->pubDate;
				if ($post['post_date_gmt']) {
					$post['post_date_gmt'] = strtotime($post['post_date_gmt']);
				} else if ($dc) {
					// if we don't already have something from pubDate
					$post['post_date_gmt'] = $dc->date;
					$post['post_date_gmt'] = preg_replace('|([-+])([0-9]+):([0-9]+)$|', '\1\2\3', $post['post_date_gmt']);
					$post['post_date_gmt'] = str_replace('T', ' ', $post['post_date_gmt']);
					$post['post_date_gmt'] = strtotime($post['post_date_gmt']);
				}
				$post['post_date_gmt'] = gmdate('Y-m-d H:i:s', $post['post_date_gmt']);
				$post['post_date'] = get_date_from_gmt($post['post_date_gmt']);

				$post['categories'] = array();
				if ($item->category) {
					foreach ($item->category as $category) {
						$post['categories'][] = (string)$category['nicename'];
					}
				} else if ($dc) {
					foreach ($dc->subject as $category) {
						$post['categories'][] = (string)$category;
					}
				}

				foreach ($post['categories'] as $cat_index => $category) {
					$post['categories'][$cat_index] = $wpdb->escape(html_entity_decode($category));
				}

				$post['guid'] = '';
				if ($item->guid) {
					$post['guid'] = $wpdb->escape(trim($item->guid));
				}

				$post['post_content'] = false;
				if (!empty($namespaces['content'])) {
					$content = $item->children($namespaces['content']);
					if ($content->encoded) {
						$post['post_content'] = $wpdb->escape(trim($content->encoded));
					}
				}
				if (!$post['post_content']) {
					// This is for feeds that put content in description
					$post['post_content'] = $wpdb->escape(html_entity_decode(trim($item->description)));
				}

				// Clean up content
				$post['post_content'] = preg_replace_callback('|<(/?[A-Z]+)|', array(&$this, '_normalize_tag'), $post['post_content']);
				$post['post_content'] = str_replace('<br>', '<br />', $post['post_content']);
				$post['post_content'] = str_replace('<hr>', '<hr />', $post['post_content']);

				$post['post_author'] = 1;
				$post['post_status'] = $wp->{'status'};
				if ($post['post_status']) $post['post_status'] = (string)$post['post_status'];
				$this->posts[] = $post;
			}
		}

		function starts_with($string, $start_string)
		{
			$len = strlen($start_string);
			return (substr($string, 0, $len) === $start_string);
		}

		function import_posts()
		{
			echo '<p>Start to import posts...</p>';
			echo '<ol>';

			$attachment_replace_before = array_keys($this->attachment_replace);

			foreach ($this->posts as $post) {
				if ($post['post_type'] !== 'app_modules') continue;
				echo "<li>" . __('Importing post...', 'athena');

				extract($post);

				if ($post['post_content']) {
					$post['post_content'] = str_replace(
						$attachment_replace_before,
						$this->attachment_replace,
						$post['post_content']
					);
				}

				if (get_post($post_id)) {
					// do nothing
				} else if ($post_id = post_exists($post_title, $post_content, $post_date)) {
					$post['post_id'] = $post_id;
					$post['ID'] = $post_id;
				} else {
					unset($post['post_id']);
					unset($post['ID']);
				}

				$post_id = wp_insert_post($post);
				wp_set_object_terms($post_id, $categories, 'categories');

				if (array_key_exists('_thumbnail_id', $post['meta_input'])) {
					$thumb_id = $post['meta_input']['_thumbnail_id'];
					if (array_key_exists($thumb_id, $this->attachment_cache)) {
						$attach_id = $this->attachment_cache[$thumb_id];
						$attachment = wp_update_post( array(
							'ID'            => $attach_id,
							'post_parent'   => $post_id,
						), true );
						if( is_wp_error( $attachment ) ) {
							error_log( print_r( $attachment, 1 ) );
						}
						set_post_thumbnail($post_id, $attach_id);
					}
				}

				if (is_wp_error($post_id))
					return $post_id;
				if (!$post_id) {
					_e('Couldn&#8217;t get post ID', 'athena');
					return;
				}

				if (0 != count($categories))
					wp_create_categories($categories, $post_id);
				_e('Done!', 'athena');
				echo '</li>';
			}

			echo '</ol>';
			echo '<p>Finished importing posts...</p>';
		}

		function import()
		{
			$file = wp_import_handle_upload();
			if (isset($file['error'])) {
				echo $file['error'];
				return;
			}

			$this->file = $file['file'];
			$this->get_posts();
			$this->import_attachments();
			$result = $this->import_posts();
			if (is_wp_error($result))
				return $result;
			wp_import_cleanup($file['id']);
			do_action('import_done', 'rss_app_modules');

			echo '<h3>';
			printf(__('All done. <a href="%s">Have fun!</a>', 'athena'), get_option('home'));
			echo '</h3>';
		}

		function dispatch()
		{
			if (empty($_GET['step']))
				$step = 0;
			else
				$step = (int) $_GET['step'];

			$this->header();

			switch ($step) {
				case 0:
					$this->greet();
					break;
				case 1:
					check_admin_referer('import-upload');
					$result = $this->import();
					if (is_wp_error($result))
						echo $result->get_error_message();
					break;
			}

			$this->footer();
		}

		function __construct()
		{
			// Nothing.
		}
	}

	$rss_import = new RSS_App_Modules_Import();

	register_importer('rss_app_modules', __('RSS (App Modules)', 'athena'), __('Import app module posts from an RSS feed.', 'athena'), array($rss_import, 'dispatch'));
} // class_exists( 'WP_Importer' )

function rss_app_modules_importer_init()
{
	load_plugin_textdomain('athena', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'rss_app_modules_importer_init');
