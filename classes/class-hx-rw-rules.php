<?php
class FilterRewriteRule
{
    public function init()
    {
        // Agregar las reglas de reescritura personalizadas
        $this->add_custom_rewrite_rules();

        // Agregar la variable de consulta 'token_activation'
        add_filter('query_vars', [$this, 'add_query_for_token_activation']);

        // Agregar la variable de consulta 'user_reactivation'
        add_filter('query_vars', [$this, 'add_query_for_user_reactivation']);

        // Hook para seleccionar la plantilla adecuada
        add_filter('template_include', [$this, 'filter_template'], 99);
    }

    public function add_custom_rewrite_rules()
    {
        add_rewrite_rule('^activation/email/confirmation/([^/]+)/?', 'index.php?token_activation=$matches[1]', 'top');
        add_rewrite_rule('^resending/user/token/([^/]+)/?', 'index.php?user_reactivation=$matches[1]', 'top');
    }

    public function add_query_for_token_activation($vars)
    {
        $vars[] = 'token_activation';
        return $vars;
    }

    public function add_query_for_user_reactivation($vars)
    {
        $vars[] = 'user_reactivation';
        return $vars;
    }

    public function filter_template($template)
    {
        $template_path = HX_TEMPLATES . '/pages/notification/';
        if (get_query_var('token_activation')) {
        } elseif (get_query_var('user_reactivation')) {
            include_once($template_path.'user_activation.phtml')
        }
        return $template;
    }
}

// Llamar a la función init usando add_action en 'init'
add_action('init', function () {
    $filter_rewrite = new FilterRewriteRule();
    $filter_rewrite->init();
});
