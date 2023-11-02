<?php

class Homey_Extensions
{
    protected static $instance;
    protected static $version = '1.0.0';

    private function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    /**
     * Return plugin instance.
     *
     * @return homey_extensions
     */
    protected static function getInstance()
    {
        return is_null(static::$instance) ? new Homey_Extensions() : static::$instance;
    }

    public static function run()
    {
        self::hxIncludeFiles();
        static::$instance = static::getInstance();
    }

    public static function pluginStatus()
    {
        return get_option('hx_activation') || false;
    }

    public static function getVersion()
    {
        return self::$version;
    }

    public static function init()
    {
        static::load_scripts();

        UserRegistration::run();
        LoginManager::run();

        include_once HX_PLUGIN_PATH . '/functions/functions-rwrules.php';
        init_notifications_rewrite();

        static::load_widgets();
    }

    private static function load_widgets()
    {
        try {
            include_once(HX_PLUGIN_PATH . '/widgets/widgets-preferences.php');
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \WidgetsHX\Widget_Preferences());
        } catch (\Throwable $th) {
            wp_send_json(array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()));
        }
    }

    private static function load_scripts()
    {
        wp_enqueue_script(
            'hx-toast-script',
            HX_PLUGIN_URL . 'assets/js/hx_toast.js',
            array('jquery'),
            HX_VERSION,
            true
        );
        wp_enqueue_style(
            'hx-toast-style',
            HX_PLUGIN_URL . 'assets/css/hx_toast.css',
            array(),
            HX_VERSION,
            'all'
        );
        wp_enqueue_script(
            'hx-custom-script',
            HX_PLUGIN_URL . 'assets/js/custom.js',
            array('jquery', 'modernizr-custom'),
            HX_VERSION,
            true
        );
        wp_enqueue_style(
            'hx_styles',
            HX_PLUGIN_URL . 'assets/css/style.css',
            array(),
            HX_VERSION,
            'all'
        );
    }

    public static function hxPluginActivation()
    {
        include_once(HX_PLUGIN_PATH . 'functions/functions-activate.php');

        $activation_status = update_option('hx_activation', 'true');
        if ($activation_status == 'true') {
            init_plugin_hx();
        }
    }
    public static function hxPluginDeactivate()
    {
        include_once(HX_PLUGIN_PATH . '/functions/functions-deactivate.php');
        include_once(HX_PLUGIN_PATH . '/functions/functions-rwrules.php');
        remove_action('plugins_loaded', array(__CLASS__, 'load_scripts'), 0);
        remove_action('init', 'init_notifications_rewrite');

        update_option('hx_activation', 'false');
        flush_rewrite_rules();
    }

    private static function hxIncludeFiles()
    {
        $class_files = apply_filters('hxIncludeFiles', array(
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
        ));
        $function_files = apply_filters('hxIncludeFiles', array(
            // 'functions-prepare-backup.php',
            'functions-activate.php',
            'functions-uninstall.php',
        ));
        self::hxLoadFiles($class_files, '/classes/');
        self::hxLoadFiles($function_files, '/functions/');
    }

    private static function hxLoadFiles($files, $dir)
    {
        foreach ($files as $file) {
            $path = HX_PLUGIN_PATH . $dir . $file;
            if (file_exists($path)) {
                if ($dir == '/functions/') {
                    require_once $path;
                } else {
                    include_once $path;
                }
            }
        }
    }
}

// add_menu_page(
//     esc_html__( 'Homey', 'homey-core' ),
//     esc_html__( 'Homey', 'homey-core' ),
//     'manage_options',
//     'homey_dashboard',
//     array( 'homey_Dashboard', 'render' ),
//     '',
//     '4'
// );
