<?php
function destroy_plugin_hx()
{
    drop_tables_plugin_hx();
}
register_uninstall_hook(__FILE__, 'destroy_plugin_hx');

function drop_tables_plugin_hx()
{
    $tables_to_remove = array(
        "cities",
        "states",
        "countries",
        "matchs",
        "preferences",
        "user_profile_data",
    );

    foreach ($tables_to_remove as $table_key) {
        global $wpdb;
        $table_name = HX_PREFIX . $table_key;
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }
}
