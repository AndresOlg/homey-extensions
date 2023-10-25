<?php

class LoginManager
{
    protected static $instance;

    public static function run()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('wp_ajax_login', array($this, 'processLogin'));
        add_action('wp_ajax_nopriv_login', array($this, 'processLogin'));
    }

    public function init()
    {
        // Initialize Hooks 
        add_action('template_redirect', 'chooseUserRedirection');
    }

    public function chooseUserRedirection()
    {
        if (is_user_logged_in() && is_page('login')) {
            wp_redirect(home_url('/profile'));
            exit;
        }
    }
    
    public function processLogin()
    {
        check_ajax_referer('security_nonce', 'security');

        $data = json_decode(stripslashes($_POST['data']), true);

        if ($data) {
            $username = sanitize_text_field($data['username']);
            $password = sanitize_text_field($data['password']);

            // Realizar la autenticación en WordPress
            $user = wp_signon(array(
                'user_login' => $username,
                'user_password' => $password,
                'remember' => true,
            ));

            if (is_wp_error($user)) {
                wp_send_json_error(array('message' => 'El inicio de sesión falló.'));
            } else {
                wp_send_json_success(array('message' => 'Inicio de sesión exitoso.'));
            }
        } else {
            wp_send_json_error(array('message' => 'Solicitud incorrecta.'));
        }
    }

    private static function getUserRole($username)
    {
        $profile_data_user = UserProfileData::getByAttr('login_ID', $username)[0];
        return $profile_data_user['user_role'];
    }
}
