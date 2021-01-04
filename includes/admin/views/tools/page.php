<?php
$button_name = __('Export app module groups');
$url = plugins_url( 'class-app-module-groups-exporter.php', __FILE__ );
$path = plugin_dir_path(__FILE__);

echo "Hello world";

if (array_key_exists('export', $_GET)) {
  include_once __DIR__ . '/../../class-app-module-groups-exporter.php';
  $exporter = new AppModuleGroupsExporter();
  $exporter->get_app_module_groups();
  $exporter->download();
}
?>
<a href="?export=1&page=athena" target="_blank" class="button button-primary"><?php echo esc_html($button_name) ?></button>

