<?php
include_once(HX_PLUGIN_PATH . '/functions/functions-default-options.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function init_plugin_hx()
{
    create_tables_plugin_hx();
    $functions_queue = array(
        'insert_traveler_preferences',
        'insert_hoster_preferences',
    );

    foreach ($functions_queue as $function_name) {
        if (function_exists($function_name)) {
            call_user_func($function_name);
        }
    }
}

function create_tables_plugin_hx()
{
    /* Call functions to create initial tables for the plugin homey-extensions */
    $functions = array(
        "create_table_user_profile_data",
        "create_table_preferences",
        "create_table_preferences_history"
        // "create_table_matchs",
        // "create_table_user_history",
        // "create_table_sessions",
    );

    foreach ($functions as $function) {
        if (function_exists($function)) {
            call_user_func($function);
        }
    }
}

/**
 * Created table user_profile_data.
 */
function create_table_user_profile_data()
{
    global $wpdb;
    $table_name = HX_PREFIX . 'user_profile_data';

    $sql =
        "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL UNIQUE,
            user_role ENUM('traveler', 'hoster') NOT NULL,
            data_traveler JSON,
            data_hoster JSON,
            preferences JSON,
            profile_score INT NOT NULL,
            PRIMARY KEY (`id`)
        ) ";
    $wpdb->query($sql);
}

/**
 * Create table IF NOT EXISTS matchs.
 */
function create_table_matchs()
{
    global $wpdb;

    $table_name = HX_PREFIX . 'matchs';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id INT NOT NULL AUTO_INCREMENT,
        traveler_id INT NOT NULL,
        hoster_id INT NOT NULL,
        match_details JSON NOT NULL,
        match_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        match_status ENUM(\"accepted\", \"reject\", \"hold\") DEFAULT \"hold\",
        PRIMARY KEY (id)
    )";
    $wpdb->query($sql);
}

/**
 * Create table IF NOT EXISTS preferences.
 */

function create_table_preferences()
{
    global $wpdb;

    $table_name = HX_PREFIX . 'preferences';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT NOT NULL AUTO_INCREMENT,
              preference_name VARCHAR(255) NOT NULL,
              preferences_type ENUM(\"hoster\",\"traveler\"),
              preferences_question TEXT,
              preferences_category varchar(255) NOT NULL,
              preferences_options JSON NOT NULL,
              preferences_typefield ENUM(\"select\",\"check\",\"text\",\"multiselect\"),
              preferences_ico VARCHAR(255),
              preferences_status ENUM(\"0\",\"1\"),
              PRIMARY KEY (id)
    )";
    $wpdb->query($sql);
}

function create_table_user_history()
{
    global $wpdb;
    $table_name = HX_PREFIX . "user_history";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
        id INT NOT NULL AUTO_INCREMENT,
        profile_info JSON NOT NULL,
        contact_info JSON NOT NULL,
        user_role ENUM(\"hoster\",\"traveler\"),
        user_preferences INT NOT NULL,
        PRIMARY KEY (id)
    )";
    $wpdb->query($sql);
}

function create_table_preferences_history()
{
    global $wpdb;
    $table_name = HX_PREFIX . "preferences_history";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        preferences JSON NOT NULL,
        PRIMARY KEY (id)

    )";
    $wpdb->query($sql);
}

function create_table_sessions()
{
    global $wpdb;
    $table_name = HX_PREFIX . "sessions_register";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
        sessionID INT NOT NULL AUTO_INCREMENT,
        userID INT NOT NULL,
        user_role ENUM(\"hoster\",\"traveler\"),
        date_start DATETIME,
        date_end DATETIME,
        ip_dir VARCHAR(15),
        lastActivity DATETIME,
        PRIMARY KEY (`sessionID`)
    )";
    $wpdb->query($sql);
}
