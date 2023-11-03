<?php
function init_notifications_rewrite()
{
    add_rewrite_rule(
        '^notifications/confirmation/([^/]*)/?',
        'index.php?token_activation=$matches[1]',
        'top'
    );
    add_filter('query_vars', 'notifications_vars', 10, 1);
}

function notifications_vars($vars)
{
    $vars[] = 'token_activation';
    return $vars;
}

function process_notifications_params()
{
    $template_path = HX_TEMPLATES . '/pages/notification/';

    if (get_query_var('token_activation')) {
        include_once HX_PLUGIN_PATH . '/templates/utils/hx-loader-timing.phtml';
        include_once($template_path . 'user_activation.phtml');
        process_activation();
        exit;
    }
}

add_action('template_redirect', 'process_notifications_params');