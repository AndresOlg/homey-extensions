<?php
include_once(HX_PLUGIN_PATH . '/functions/functions-store-global.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-default-options.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function init_plugin_hx()
{
    create_tables_plugin_hx();
    $auth_data = get_request_auth();
    // fetch_and_store_countries($auth_data);

    $functions_queue = array(
        // 'fetch_and_store_states',
        // 'fetch_and_store_cities',
        'insert_traveler_preferences',
        'insert_hoster_preferences',
    );

    if ($auth_data) {
        foreach ($functions_queue as $function_name) {
            if (function_exists($function_name)) {
                call_user_func($function_name, $auth_data);
            }
        }
    }
}

function create_tables_plugin_hx()
{
    /* Call functions to create initial tables for the plugin homey-extensions */
    $functions = array(
        "create_table_user_profile_data",
        "create_table_countries",
        "create_table_states",
        "create_table_cities",
        "create_table_matchs",
        "create_table_preferences",
        "create_table_user_history",
        "create_table_sessions",
        "create_table_preferences_history"
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
            login_ID VARCHAR(50) UNIQUE,
            date_birth DATE DEFAULT '0000-00-00',
            gender ENUM('M','F') NOT NULL,
            country_residency VARCHAR(2) NOT NULL,
            city_residency VARCHAR(255) NOT NULL,
            country_nationality CHAR(2) NOT NULL,
            mobile_number VARCHAR(10),
            DNI VARCHAR(18) NOT NULL,
            type_DNI VARCHAR(8) NOT NULL DEFAULT 'ID',
            emergency_contact JSON DEFAULT '{ \"name\": \"\", \"telephone\": \"\" }',
            isverified ENUM('YES', 'NO'),
            user_role ENUM('traveler', 'hoster') NOT NULL,
            profile_image VARCHAR(255),
            data_traveler JSON,
            data_hoster JSON,
            profile_score INT NOT NULL,
            PRIMARY KEY (`id`)
        ) ";
    $wpdb->query($sql);
}

/**
 * Created table countries.
 */
function create_table_countries()
{
    global $wpdb;

    $table_name = HX_PREFIX . 'countries';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT NOT NULL AUTO_INCREMENT,
              geonameID    VARCHAR (255) NOT NULL,
              country_name VARCHAR(500) NOT NULL,
              country_short_name CHAR(2) NOT NULL,
              country_phone_code INT,
              PRIMARY KEY (id)
        )";
    $wpdb->query($sql);
}

/**
 * Created table states.
 */
function create_table_states()
{
    global $wpdb;

    $table_name = HX_PREFIX . 'states';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT NOT NULL AUTO_INCREMENT,
              geonameID    VARCHAR (255) NOT NULL,
              country_id INT NOT NULL,
              country_code CHAR(2) NOT NULL,
              state_name VARCHAR(500) NOT NULL,
              PRIMARY KEY (`id`)
            )";
    $wpdb->query($sql);
}

/**
 * Create table IF NOT EXISTS cities.
 */
function create_table_cities()
{
    global $wpdb;

    $table_name = HX_PREFIX . 'cities';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT NOT NULL AUTO_INCREMENT,
              geonameID  VARCHAR (255) NOT NULL,
              state_id INT NOT NULL,
              country_code CHAR(2),
              city_name  VARCHAR(500) NOT NULL,
              PRIMARY KEY (`id`)
            )";
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
