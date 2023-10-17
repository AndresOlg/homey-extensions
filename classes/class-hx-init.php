<?php

class Homey_Extensions
{
    protected static $instance;
    protected static $version = '1.0.0';

    public static function run()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->hx_include_files();
        $this->init();
    }
    public static function plugin_status()
    {
        return get_option('hx_activation') || false;
    }

    public static function getVersion()
    {
        return self::$version;
    }

    private function init()
    {
        include_once(HX_PLUGIN_PATH . '/functions/functions-activate.php');

        if ($this->plugin_status() == 'false') {
            register_activation_hook(__FILE__, array('Homey_Extensions', 'init_plugin_hx'));
            register_deactivation_hook(__FILE__, array('Homey_Extensions', 'deactive_plugin_hx'));
        }
        init_plugin_hx();
    }

    private function hx_include_files()
    {
        $activation_status = get_option('hx_activation');
        $class_files = array(
            'class-hx-installer.php',
            'class-hx-login.php',
            'class-hx-cities.php',
            'class-hx-states.php',
            'class-hx-countries.php',
            'class-hx-preferences.php',
            'class-hx-user-profile-data.php',
            'class-hx-register.php',
            // 'class-hx-user-profiling.php',
            // 'class-hx-match.php',
            // 'class-hx-statistics.php',
        );
        $function_files = array(
            // 'functions-prepare-backup.php',
            'functions-activate.php',
            'functions-uninstall.php',
        );
        if ($activation_status) {
            $this->hx_load_files($class_files, '/classes/');
            $this->hx_load_files($function_files, '/functions/');
        }
    }

    private function hx_load_files($files, $dir)
    {
        foreach ($files as $file) {
            $path = HX_PLUGIN_PATH . $dir . $file;
            if (file_exists($path)) {
                include_once $path;
            }
        }
    }
}


function deactive_plugin_hx()
{
    update_option('hx_activation', 'false');
}
