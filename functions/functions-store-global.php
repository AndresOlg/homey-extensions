<?php

include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');

$states_data = array();
$cities_data = array();
$phonecodes_data = array();

/**
 * Make url request.
 * @return mixed
 */

function get_url_geonames($type, $auth_data, $search_param = '')
{
    $search_param = strval($search_param);
    $url = $auth_data['url'][$type];
    $user_param = decryptValue($auth_data['user_name'], $auth_data['secret_key']);
    if ($type != 'countries') {
        $url = str_replace('{{ID}}', $search_param, $url);
    }
    $url_ = "{$url}&username={$user_param}";
    return $url_;
}

/**
 * Fetch and store phonecodes.
 */
function get_phonecode($iso_2)
{
    global $phonecodes_data;
    if (empty($phonecodes_data)) $phonecodes_data = get_data_phonecodes();
    $search_code = $iso_2;
    $found_phonecode = $phonecodes_data[$search_code];
    return $found_phonecode['dial_code'];
}


/**
 * Function to insert data into the database table.
 */
function insert_data($table_name, $insert_columns, $insert_values)
{
    global $wpdb;

    if (!empty($insert_values)) {
        $placeholders = array();
        for ($i = 0; $i < count($insert_values); $i++) {
            $placeholders[] = '(' . implode(', ', array_fill(0, count($insert_columns), '%s')) . ')';
        }

        // Flatten the $insert_values array
        $flat_values = array();
        foreach ($insert_values as $value_set) {
            $flat_values = array_merge($flat_values, $value_set);
        }

        $query = $wpdb->prepare(
            "INSERT INTO $table_name (" . implode(', ', $insert_columns) . ") VALUES " . implode(', ', $placeholders),
            $flat_values  // Use the flattened array
        );

        $wpdb->query($query);
        if ($wpdb->last_error) {
            echo 'Error al insertar los registros: ' . $wpdb->last_error;
        }
    }
}


/**
 * Fetch and store data from the API.
 */
function fetch_and_store_data($api_url)
{
    // Make the API request.
    $timeout = 1000;
    try {
        $response = wp_safe_remote_request($api_url, array('method' => 'GET', 'timeout' => $timeout));

        // Check for WP_Error and response code.
        if (is_wp_error($response)) {
            throw new Exception('Error request API. ' . $api_url);
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 200) {
            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);
            if (isset($data['status']['message'])) {
                return false;
            } elseif (isset($data['geonames'])) return $data['geonames'];
            else {
                wp_die(json_encode($data));
            }
        } else {
            $response_body = wp_remote_retrieve_body($response);
            throw new Exception('Error response API. ' . $api_url);
        }
    } catch (Exception $e) {
        // Exception Control.
        error_log('url:' . $api_url . 'Error en fetch_and_store_data: ' . $e->getMessage());
        return false;
    }
}

/**
 * Fetch and store countries.
 */
function fetch_and_store_countries($auth_data)
{
    global $wpdb;
    $api_url = get_url_geonames('countries', $auth_data);
    $table_name = HX_PREFIX . 'countries';
    $insert_columns = array('geonameID', 'country_name', 'country_short_name', 'country_phone_code');
    $data = fetch_and_store_data($api_url);
    if (!empty($data)) {
        $check_query = "SELECT COUNT(*) FROM $table_name";
        $table_row_count = $wpdb->get_var($check_query);

        if ($table_row_count == 0) {
            $insert_values = array();
            foreach ($data as $item) {
                $values = array(
                    $item['geonameId'],
                    $item['countryName'],
                    $item['countryCode'],
                    get_phonecode($item['countryCode'])
                );
                $insert_values[] = $values;
            }
            insert_data($table_name, $insert_columns, $insert_values);
        }
    }
}

/**
 * Fetch and store states.
 */ function fetch_and_store_states($auth_data)
{
    global $wpdb, $states_data;
    $table_countries = HX_PREFIX . 'countries';
    $table_states = HX_PREFIX . 'states';
    $check_query = "SELECT COUNT(*) FROM $table_states";
    $table_row_count = $wpdb->get_var($check_query);

    if ($table_row_count == 0) {
        $countries = $wpdb->get_results("SELECT * FROM {$table_countries}", ARRAY_A);
        if (empty($states_data)) {
            foreach ($countries as $country) {
                $api_url = get_url_geonames('states', $auth_data, $country['geonameID']);
                $data = fetch_and_store_data($api_url);
                if ($data != false or !empty($data)) $states_data[$country['id']] = $data;
            }
        }
        if (!empty($states_data)) {
            foreach ($states_data as $country => $states) {
                foreach ($states as $state) {
                    $values = array(
                        $state['geonameId'],
                        $country,
                        $state['countryCode'],
                        $state['name']
                    );
                    $insert_values[] = $values;
                }
            }

            if (!empty($insert_values)) {
                $table_name = $table_states;
                $insert_columns = array('geonameID', 'country_id', 'country_code', 'state_name');
                insert_data($table_name, $insert_columns, $insert_values);
            }
        }
    }
}


/**
 * Fetch and store cities.
 */
function fetch_and_store_cities($auth_data)
{
    global $wpdb, $cities_data;
    $table_states = HX_PREFIX . 'states';
    $table_cities = HX_PREFIX . 'cities';
    $check_query = "SELECT COUNT(*) FROM $table_cities";
    $table_row_count = $wpdb->get_var($check_query);
    if ($table_row_count == 0) {
        $states = $wpdb->get_results("SELECT * FROM {$table_states}", ARRAY_A);
        if (!empty($states)) {
            foreach ($states as $state) {
                $state_name = rawurlencode(strtolower($state['state_name']));
                $state_name = str_replace("%27", "'", $state_name);
                $api_url = get_url_geonames('cities', $auth_data, $state['geonameID']);
                $data = fetch_and_store_data($api_url);
                if ($data != false || !empty($data)) $cities_data[$state['id']] = $data;
            }
        }
        if (!empty($cities_data)) {
            foreach ($cities_data as $state => $cities) {
                foreach ($cities as $city) {
                    $values = array(
                        $city['geonameID'],
                        $state,
                        $state['country_code'],
                        $city['name']
                    );
                    $insert_values[] = $values;
                }
            }
            if (!empty($insert_values)) {
                $table_name = $table_states;
                $insert_columns = array('geonameID', 'state_id', 'country_code', 'city_name');
                insert_data($table_name, $insert_columns, $insert_values);
            }
        }
    }
}
