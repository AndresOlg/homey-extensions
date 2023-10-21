<?php

/**
 * Plugin Name: Homey Extensions
 * Plugin URI: https://www.muchomasstudio.com
 * Description: This plugin adds functionality to the Homey theme and modifies some of its existing logic.
 * Version: 1.0.0
 * Author: muchomasstudio
 * Author URI: https://www.muchomasstudio.com
 * Collaborator: AndresOlg <github> https://github.com/AndresOlg </github>
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpplugin
 * Domain Path:  /homey-extensions
 *
 */
// Abbreviation homey-extensions prefix: HX
defined('ABSPATH') or die('¡Loading!');

// Default config plugin
define('HX_PLUGIN_PATH',              plugin_dir_path(__FILE__));
define('HX_PLUGIN_URL',               plugin_dir_url(__FILE__));
define('HX_ADMIN_IMAGES_URL',         HX_PLUGIN_PATH  . '/assets/images/');
define('HX_TEMPLATES',                HX_PLUGIN_PATH . '/templates/');
define('HX_VERSION',                  '1.0.0');
define('HX_JS_DIR', HX_PLUGIN_URL . 'assets/js/');
define('HX_CSS_DIR', HX_PLUGIN_URL . 'assets/css/');
define('HX_JSON_DIR', HX_PLUGIN_PATH . '/jsons/');

global $wpdb;
$prefix_homey_extensions =            $wpdb->prefix . 'hx_';
define('HX_PREFIX',                   $prefix_homey_extensions);

error_reporting(E_ALL);

//Main plugin file
require_once HX_PLUGIN_PATH . '/classes/class-hx-init.php';

register_activation_hook(__FILE__, array('Homey_Extensions', 'hxPluginActivation'));
register_deactivation_hook(__FILE__, array('Homey_Extensions', 'hxPluginDeactivate'));

// Initialize plugin.
use Homey_Extensions as HX;

HX::run();


restore_error_handler();

// function mi_manipulador_de_errores($errno, $errstr, $errfile, $errline)
// {
//     error_log("Error: $errstr en $errfile en la línea $errline" . $errno);
//     print_error("Error: $errstr en $errfile en la línea $errline" . $errno);
// }

// // Establecer el manejador de errores personalizado
// set_error_handler('mi_manipulador_de_errores');