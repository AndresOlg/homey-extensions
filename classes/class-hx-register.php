<?php

include_once(HX_TEMPLATES . '/elementor-templates.php');
include_once(HX_PLUGIN_PATH . '/classes/validation/class-hx-registrationform.php');

use TemplatesHX\Elementor_Template_Handler as TemplateHandler;
use ManageRegistrationForm as RegistrationForm;

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
            }
            if (!$data_validate['status'] === 'error') {
                wp_send_json($data_validate);
            } else {
                $response = RegistrationForm::saveGeneralData();
                wp_send_json($response);
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
        $data_validate = FormValidator::manageGeneralFormData($data_form);
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

        $data_validate = FormValidator::manageGeneralFormData($data_form);
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
}
