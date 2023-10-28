<?php

/** Thirths parts includes */
include_once(HX_PLUGIN_PATH . 'functions/functions-utils.php');
include_once(HX_TEMPLATES . '/elementor-templates.php');

/** HX includes */
include_once(HX_PLUGIN_PATH . 'functions/functions-email.php');
include_once(HX_PLUGIN_PATH . '/classes/controllers/class-hx-registrationform.php');

use TemplatesHX\Elementor_Template_Handler as TemplateHandler;
use ManageFormsHX\ManageRegistrationForm as RegistrationForm;

class UserRegistration
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
        // Initialize Hooks 
        add_action('wp_ajax_general_register', array($this, 'registerUser'));
        add_action('wp_ajax_nopriv_general_register', array($this, 'registerUser'));

        add_action('wp_ajax_hoster_preferences', array($this, 'registerHoster'));
        add_action('wp_ajax_nopriv_hoster_preferences', array($this, 'registerHoster'));

        add_action('wp_ajax_traveler_preferences', array($this, 'registerTraveler'));
        add_action('wp_ajax_nopriv_traveler_preferences', array($this, 'registerTraveler'));
        add_action('template_redirect', array($this, 'chooseUserRedirection'));

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('elementor-frontend');
        });

        add_action('wp_enqueue_scripts', array($this, 'loadAjaxRegister'));

        add_action('template_redirect', array($this, 'activate_account'));
    }

    public function loadAjaxRegister()
    {
        include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
        $ajax_data = generate_data_ajax();
        $ajax_scripts = array('ajax_register_form');
        foreach ($ajax_scripts as $script) {
            wp_enqueue_script("{$script}", HX_JS_DIR . $script . '.js', array('jquery'), HX_VERSION, true);
            wp_localize_script("{$script}", 'ajax_object', $ajax_data);
        }
    }

    public function chooseUserRedirection()
    {
        if (is_user_logged_in() && is_page('register')) {
            wp_redirect(home_url('/profile'));
            exit;
        } else if (is_page('register')) {
            wp_enqueue_script(
                'validate-register-script',
                HX_PLUGIN_URL . 'assets/js/validate_inputs_register.js',
                array('jquery'),
                HX_VERSION,
                true
            );
            $this->registrationFormTemplate();
        }
    }

    public function registerUser()
    {
        try {
            check_ajax_referer('security_nonce', 'security');
            $data = $_POST;

            if (isset($data)) {
                $data_form = $data;
                $data_form['type'] = 'main';
                $data_validate = RegistrationForm::manageGeneralFormData($data_form);
                if ($data_validate['status'] === 'error') {
                    wp_send_json($data_validate);
                } else {
                    $user = RegistrationForm::saveUserData();
                    $response = $user;
                    if ($response['status'] === 'error') {
                        return  wp_send_json($response);
                    } else if (isset($user['user_id'])) {
                        //[Path avatar image] uploads/homey-extensions/profile_avatars/$user['file_name'];
                        $user_id = $user['user_id'];
                        update_user_meta($user_id, 'profile_image', $user['filename']);

                        $user_data = get_userdata($user_id);
                        $user_data->set_role(get_user_role($data_form['user_role']));
                        $updated = wp_update_user($user_data);

                        if ($updated) {
                            $mail_response = static::sendEmailConfirmation($user_id, $user['pass'], get_user_role($data_form['user_role']));
                            if ($mail_response) {
                                $response = array('status' => 'success', 'data' => $user);
                                return $response;
                            }
                            unset($user['pass']);
                            unset($user['filepath']);
                            unset($user['filename']);
                            $response = array('status' => 'success', 'message' => 'The user registered successfully!', 'data' => $user);
                        }
                    }
                    wp_send_json($response);
                }
            }
        } catch (\Throwable $th) {
            wp_send_json(array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()));
        }
    }

    public function registerHoster()
    {
        check_ajax_referer('security_nonce', 'security');

        $data = json_decode(file_get_contents("php://input"), true);

        $data_form = $data;
        $data_form['type'] = 'hoster';
        $data_validate = RegistrationForm::manageGeneralFormData($data_form);
        if (!$data_validate['errors']) {
            wp_send_json($data_form);
        } else {
            wp_send_json($data_validate);
        }
        exit;
    }

    public function registerTraveler()
    {
        check_ajax_referer('security_nonce', 'security');

        $data = json_decode(file_get_contents("php://input"), true);

        $data_form = $data;
        $data_form['type'] = 'traveler';

        $data_validate = RegistrationForm::manageGeneralFormData($data_form);
        if (!$data_validate['errors']) {
            wp_send_json($data_form);
        } else {
            wp_send_json($data_validate);
        }
        exit;
    }

    /**
     * Templates RegistrationForm
     */

    /* { registration Form } */
    public function registrationFormTemplate()
    {
        $template_name = 'register_form';
        $Elementor_Template = TemplateHandler::renderTemplate($template_name);
        echo $Elementor_Template;
    }

    /* { registration Hoster Form } */
    public function hosterPreferencesFormTemplate()
    {
        $template_name = 'hoster_form';
        $Elementor_Template = TemplateHandler::renderTemplate($template_name);
        echo $Elementor_Template;
    }

    /* { registration Traveler Form } */
    public function travelerPreferencesFormTemplate()
    {
        $template_name = 'traveler_form';
        $Elementor_Template = TemplateHandler::renderTemplate($template_name);
        echo $Elementor_Template;
    }

    public static function sendEmailConfirmation($user_id, $pass, $role)
    {
        ob_start();
        hx_wp_new_user_notification($user_id, $pass, $role);
        $res = ob_get_contents();
        ob_clean();
        return  $res;
    }
}
