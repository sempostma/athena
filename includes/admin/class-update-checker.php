<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Athena_Update_Checker
{

    protected $plugin_name;
    protected $plugin_version;
    
    public function __construct($plugin_name, $plugin_version)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_version = $plugin_version;

        require plugin_dir_path( __FILE__ ) . '../../plugin-update-checker-4.9/plugin-update-checker.php';

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/sempostma/athena',
            __FILE__,
            'athena'
        );

        $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    }
}

new Athena_Update_Checker($plugin_name, $plugin_version);
