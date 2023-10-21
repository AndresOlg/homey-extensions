<?php

include_once(HX_TEMPLATES . '/elementor-templates.php');

use TemplatesHX\Elementor_Template_Handler as TemplateHandler;
use RegistrationFormValidator as FormValidator;

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
        add_action('template_redirect', array($this, 'redirect_to_profile_is_logged'));

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('elementor-frontend');
        });

        add_action('wp_enqueue_scripts', array($this, 'load_ajax_scripts_register'));
    }

    public function load_ajax_scripts_register()
    {
        $ajax_data = array('ajaxurl' => admin_url('admin-ajax.php'));
        $ajax_scripts = array(
            'ajax_register_form', // General register_form
        );

        foreach ($ajax_scripts as $script) {
            wp_enqueue_script("{$script}", HX_JS_DIR . $script . '.js', array('jquery'), HX_VERSION, true);
            wp_localize_script("{$script}", 'ajax_object', $ajax_data);
        }
    }

    public function redirect_to_profile_is_logged()
    {
        if (is_user_logged_in() && is_page('register')) {
            wp_redirect(home_url('/profile'));
            exit;
        } else if (is_page('register')) {
            $this->registrationFormTemplate();
        }
    }

    public function registerUser()
    {
        check_ajax_referer('security_nonce', 'security');
        $data = $_POST;
        $data_form = $data['data'];
        $data_form['type'] = 'main';
        $data_validate = FormValidator::validateFormData($data_form);
        if (!$data_validate['errors']) {
            wp_send_json_success($data_form);
        } else {
            wp_send_json_error($data_validate);
        }
        exit;
    }

    public function registerHoster()
    {
        check_ajax_referer('security_nonce', 'security');

        $data = json_decode(file_get_contents("php://input"), true);

        $data_form = $data;
        $data_form['type'] = 'hoster';
        $data_validate = FormValidator::validateFormData($data_form);
        if (!$data_validate['errors']) {
            wp_send_json_success($data_form);
        } else {
            wp_send_json_error($data_validate);
        }
        exit;
    }

    public function registerTraveler()
    {
        check_ajax_referer('security_nonce', 'security');

        $data = json_decode(file_get_contents("php://input"), true);

        $data_form = $data;
        $data_form['type'] = 'traveler';
        wp_die(json_encode($data_form));
        $data_validate = FormValidator::validateFormData($data_form);
        if (!$data_validate['errors']) {
            wp_send_json_success($data_form);
        } else {
            wp_send_json_error($data_validate);
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
