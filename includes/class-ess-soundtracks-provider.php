<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}


class ESSSoundtracksProvider
{
  protected $plugin_name;
  protected $plugin_version;
  
  public function __construct($plugin_name, $plugin_version)
  {
    $this->plugin_name    = $plugin_name;
    $this->plugin_version = $plugin_version;

    wp_oembed_add_provider( 'https://api.esstudio.site/sharing/*', 'https://api.esstudio.site/oembed' );
    wp_oembed_add_provider( 'http://api.esstudio.site/sharing/*', 'https://api.esstudio.site/oembed' );
  }
}

new ESSSoundtracksProvider( $plugin_name, $plugin_version );
