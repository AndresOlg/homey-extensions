<?php

namespace ManageFormsHX;

include_once(HX_PLUGIN_PATH . '/classes/errors/class-hx-errors.php');
include_once(HX_PLUGIN_PATH . '/classes/class-hx-user-profile-data.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');

use ErrorsHX\ErrorHandler as ErrorHandler;
use UserHX\Models\UserProfileData as UserProfile;

class ManageLoginForm
{
    protected static $instance;
    protected static $errorHandler;
    protected static $data_form;
    public static function manageFormData($data_form)
    {
        $response = null;
        static::$data_form = $data_form;
        static::$instance = new ManageLoginForm();
        try {
            static::$errorHandler = ErrorHandler::getInstance();
            $response = self::validateLoginForm();
        } catch (\Throwable $th) {
            $response = array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile());
        }
        return $response;
    }

    private static function validateLoginForm()
    {
        $allowed_html = array();
        static::$data_form['user_login'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_login'], $allowed_html)));
        static::$data_form['user_email'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_email'], $allowed_html)));
        static::$data_form['user_role'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_role'], $allowed_html)));
        $remember = trim(sanitize_text_field(wp_kses(static::$data_form['rember_user'], $allowed_html)));

        $data_user = static::$data_form;
        $user = user_exist(static::$data_form);
        $username = static::$data_form['user_login'];
        $pass = static::$data_form['user_pass'];
        $remember = ($remember == 'on') ? true : false;

        $response = '';
        if (empty($pass)) {
            $error_data = static::$errorHandler->getError('ERR-U010', 'user');
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            return  $response;
        } elseif (!$user['username'] && !$user['email']) {
            $error_data = static::$errorHandler->getError('ERR-U001', 'user');
            $error_data['message'] = str_replace('{Username}', $username, $error_data['message']);
            $error_data['message'] = str_replace('{email}', $user['user_email'], $error_data['message']);
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            return $response;
        }

        wp_clear_auth_cookie();

        if (is_email($data_user['user_email'])) {
            $user = get_user_by('email', $user['user_email']);
        } else {
            $user = get_user_by('login', $data_user['user_login']);
        }

        if (user_is_verified_by_email($user)) {
            if (isset($_COOKIE['role_user'])) unset($_COOKIE['role_user']);
            setcookie('role_user', get_user_role(static::$data_form['user_role']), time() + (86400 * 30), "/");
            $response = array('status' => 'success', 'message' => 'user is valid and user is verified', 'user' => $user);
            return $response;
        } else {
            $error_data = static::$errorHandler->getError('ERR-U006', 'user');
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            return $response;
        }
    }

    public static function loginUser()
    {
        $data_form = static::$data_form;
        $password = $data_form['user_pass'];
        $remember = ($data_form['remember_user'] == 'on') ? true : false;
        $user = '';

        $creds = array();
        $creds['user_login'] = $data_form['user_login'];
        $creds['user_password'] = ascii_string(explode('-', $password));
        $creds['remember'] = $remember;

        if (is_email($data_form['user_email'])) {
            $user = get_user_by('email', $data_form['user_email']);
        } else {
            $user = get_user_by('login', $data_form['user_login']);
        }
        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            $error_data = static::$errorHandler->getError('ERR-U011', 'user');
            $message = "The password you entered for the username <strong>{$creds['user_login']}</strong> is incorrect.<br>";
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $message);
            return $response;
        } else {
            static::$data_form['user_id'] = $user->ID;
            do_action('set_current_user');
            wp_set_auth_cookie($user->ID, $creds['remember']);
            $response = array('status' => 'success', 'message' => esc_html__('Login successful, redirecting...', 'homey-login-register'), 'user' => $user, 'redirect' => static::$instance->get_user_redirect());
            return $response;
        }
    }

    protected function register_session()
    {
    }

    protected function get_user_redirect()
    {
        $redirect_url = '';
        $data_form = static::$data_form;
        $user_role = get_user_role($data_form['user_role']);
        $user_id = $data_form['user_id'];

        $userprofile = new UserProfile();
        if (is_null($userprofile->get($user_id))) {
            $redirect_url = $this->redirect_by_role($user_role);
        } elseif ($userprofile->get($user_id)) {

            $data = $userprofile->get($user_id);
            var_dump($data);
            $completed = $data['profile_score'] === 100;

            if ($completed) $redirect_url = home_url('/profile');
            else $redirect_url = $this->redirect_by_role($user_role);
        }

        return $redirect_url;
    }

    private static function redirect_by_role($user_role)
    {
        if ($user_role == 'homey_renter') return home_url('travelerform');
        elseif ($user_role == 'homey_hoster') return home_url('hosterform');
    }
}
