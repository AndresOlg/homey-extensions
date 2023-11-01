<?php
function unload_scripts()
{
    wp_dequeue_script('hx-toast-script');
    wp_dequeue_style('hx-toast-style');
    wp_dequeue_style('hx_styles');
    wp_clear_scheduled_hook('inactividad_check');
    remove_action('inactivity_check', 'checkActivityUser');
}
