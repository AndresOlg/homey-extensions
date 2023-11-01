<?php



/** HX includes */
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
include_once(HX_TEMPLATES . '/elementor-templates.php');
include_once(HX_PLUGIN_PATH . 'classes/class-hx-user-profile-data.php');
include_once(HX_PLUGIN_PATH . 'classes/controllers/class-hx-loginform.php');
require_once(HX_PLUGIN_PATH . 'classes/class-hx-register.php');

/** HX includes / */

use TemplatesHX\Elementor_Template_Handler as TemplateHandler;
use UserHX\Models\UserProfileData as UserProfile;


use ManageFormsHX\ManageLoginForm as LoginForm;

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
        add_action('wp_ajax_processlogin', array($this, 'processLogin'));
        add_action('wp_ajax_nopriv_processlogin', array($this, 'processLogin'));
        add_action('template_redirect', array($this, 'chooseUserRedirectionLogin'), 99);
        add_action('template_redirect', array($this, 'formRedirectionLogin'), 99);

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('elementor-frontend');
        });
        add_action('wp_enqueue_scripts', array($this, 'loadAjaxLogin'));
    }
    function get_user_role(int $user_id = 0)
    {
        $user = ($user_id) ? get_userdata($user_id) : wp_get_current_user();

        return current($user->roles);
    }
    public function loadAjaxLogin()
    {
        $ajax_data = generate_data_ajax();
        $ajax_scripts = array('ajax_login_form');
        foreach ($ajax_scripts as $script) {
            wp_enqueue_script("{$script}", HX_JS_DIR . $script . '.js', array('jquery'), HX_VERSION, true);
            wp_localize_script("{$script}", 'ajax_object', $ajax_data);
        }
    }

    public function chooseUserRedirectionLogin()
    {
        if (is_user_logged_in() && (is_page('login/') or is_page('register/') or is_page('/'))) {
            $user = wp_get_current_user();
            $userprofile = new UserProfile();
            $redirect_url = '';
            if (!isset($_COOKIE['role_user'])) {
                $user_role = $_COOKIE['role_user'];
                $user_id = $user->ID;

                if (is_null($userprofile->get($user_id))) {
                    $redirect_url = $this->redirect_by_role($user_role);
                } elseif ($userprofile->get($user_id)) {

                    $data = $userprofile->get($user_id)[0];
                    $completed = $data['profile_score'] === 100;

                    if ($completed) $redirect_url = home_url('/profile');
                    else $redirect_url = $this->redirect_by_role($user_role);
                }
            }
            wp_redirect($redirect_url);
            exit;
        } elseif (is_page('login/')) {
            $this->loginFormTemplate();
        }
    }

    public function formRedirectionLogin()
    {
        if (is_user_logged_in() && is_page('/login/travelerform')) {
            UserRegistration::travelerPreferencesFormTemplate();
        } else if (is_user_logged_in() && is_page('/login/hosterform')) {
            UserRegistration::hosterPreferencesFormTemplate();
        } elseif (is_user_logged_in()) {
            wp_redirect(home_url('/profile'));
        }
    }

    private function loginFormTemplate()
    {
        wp_enqueue_script('validate-login-script', HX_PLUGIN_URL . 'assets/js/validate_inputs_login.js', array('jquery'), HX_VERSION, true);
        get_template_part('template-parts/modal-window-forgot-password');

        $template_name = 'login_form';
        $Elementor_Template = TemplateHandler::renderTemplate($template_name);
        echo $Elementor_Template;
    }

    public function processLogin()
    {
        try {
            check_ajax_referer('security_nonce', 'security');
            $data = $_POST;

            if (isset($data)) {
                $data_form = $data;
                $data_validate = LoginForm::manageFormData($data_form);
                if ($data_validate['status'] === 'error') {
                    wp_send_json($data_validate);
                } else {
                    $response = LoginForm::loginUser();
                    if ($response['status'] === 'error') {
                        return  wp_send_json($response);
                    }
                    $user = $response['user'];
                    if (isset($user->ID)) {
                        $response = array('status' => 'success', 'message' => 'The user registered successfully!', 'data' => $user, 'redirect' => $response['redirect']);
                    }
                    wp_send_json($response);
                }
            }
        } catch (\Throwable $th) {
            wp_send_json(array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()));
        }
    }
    private static function redirect_by_role($user_role)
    {
        if ($user_role == 'homey_renter') return home_url('/login/travelerform');
        elseif ($user_role == 'homey_hoster') return home_url('/login/hosterform');
    }
}
