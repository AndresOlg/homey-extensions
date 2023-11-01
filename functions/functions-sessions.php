<?php
function checkActivityUser()
{
    global $wpdb;
    $table_name = HX_PREFIX . "sessions_register";
    $limit_time = 36000;
    $elapsed_time = current_time("timestamp") - $limit_time;

    $inactive_users = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT userID FROM $table_name WHERE date_end IS NULL AND lastActivity < %d",
            $elapsed_time
        )
    );

    if (!empty($inactive_users)) {
        foreach ($inactive_users as $user) {
            $wpdb->update(
                $table_name,
                array('date_end' => current_time('mysql')),
                array('userID' => $user->userID, 'FechaCierre' => '0000-00-00 00:00:00')
            );
        }
    }
}

// add_action('inactivity_check', 'checkActivityUser');
// if (!wp_next_scheduled('inactivity_check')) {
//     wp_schedule_event(time(), 3600, 'inactivity_check');
// }
