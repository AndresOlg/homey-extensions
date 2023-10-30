<?php

include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
include_templates_emails();

function hx_wp_new_user_notification($user_id, $randonpassword, $role)
{
    $user = new WP_User($user_id);

    $user_login = stripslashes($user->user_login);
    $user_email = stripslashes($user->user_email);
    $user_role = stripslashes($role);

    // Send notification to admin
    $args = array(
        'user_login_register' => $user_login,
        'user_profile' => site_url("/dashboard/?dpage=users&user-id=$user_id"),
        'user_password'   => $randonpassword,
        'user_email_register' => $user_email,
        'user_role' => $user_role
    );
    $email_admin = hx_send_mail(get_option('admin_email'), 'admin_new_user_register', $args);

    if (is_wp_error($email_admin)) {
        return $email_admin;
    }

    // Return if password in empty
    if (empty($randonpassword)) {
        return;
    }

    // Send notification to registered user
    $vId = md5($user_id);
    update_user_meta($user_id, 'verification_id', $vId);
    update_user_meta($user_id, 'is_email_verified', 0);
    update_user_meta($user_id, 'activation_token_expiration', date('Y-m-d H:i:s', strtotime('+24 hours')));

    $site_url = get_option('siteurl');

    $activation_token_expiration = get_user_meta($user_id, 'activation_token_expiration', true);
    $args = array(
        'user_login_register'  =>  $user_login,
        'user_email_register'  =>  $user_email,
        'user_password'   => $randonpassword,
        'activaction_url'   => "{$site_url}/notifications/confirmation/{$vId}",
        'user_role' => $user_role,
        'token_expiration' => $activation_token_expiration
    );

    $email_user = hx_send_mail($user_email, 'new_user_register', $args);

    if (!is_wp_error($email_user)) {
        return (array(
            'status' => 'success',
            'message' => esc_html__("Email Sent Successfully!", 'homey-core')
        ));
    } else {
        return array(
            'status' => 'error',
            'message' => esc_html__("Server Error: Make sure Email function working on your server!", 'homey-core')
        );
    }
}

function hx_send_mail($email, $email_type, $args)
{
    $value_message = homey_option('homey_' . $email_type);
    $value_subject = homey_option('homey_subject_' . $email_type);

    do_action('wpml_register_single_string', 'homey', 'homey_email_' . $value_message, $value_message);
    do_action('wpml_register_single_string', 'homey', 'homey_email_subject_' . $value_subject, $value_subject);

    $filters = hx_emails_filter_replace($email, $value_message, $value_subject, $args, $email_type);
    return $filters;
}

function hx_emails_filter_replace($email, $message, $subject, $args, $email_type)
{
    $args['site_url'] = get_option('siteurl');
    $args['site_url'] = $args['site_url'] == 'localhost' ? 'https://wanderloop.com.co' : $args['site_url'];
    $args['site_title'] = get_option('blogname');
    $args['user_email'] = $email;
    $user = get_user_by('email', $email);
    $args['user_login'] = isset($user->user_login) ? $user->user_login : '';

    foreach ($args as $key => $val) {
        $subject = str_replace('{' . $key . '}', $val, $subject);
        $message = str_replace('{' . $key . '}', $val, $message);
    }

    $message = stripslashes($message);
    $hx_send_emails = hx_send_emails($email, $subject, $message, $email_type, $args);
    return $hx_send_emails;
}

function hx_send_emails($user_email, $subject, $message, $type, $args)
{
    $headers = array();
    $subject = esc_html__($subject, 'homey');
    $no_reply_email_address = homey_option('no_reply_email_address');

    if (empty(trim($no_reply_email_address))) {
        $no_reply_email_address = isset($_SERVER['HTTP_HOST']) ? 'noreply@' . str_replace('www.', '', sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']))) : 'noreply@support.com';
    }

    $enable_html_emails = homey_option('enable_html_emails');
    $enable_email_footer = homey_option('enable_email_footer');

    $email_footer_content = esc_html(homey_option('email_footer_content'));

    // $email_head_bg_color = homey_option('email_head_bg_color');
    // $email_foot_bg_color = homey_option('email_foot_bg_color');

    if ($enable_html_emails != 0) {
        $headers[] = "Content-Type: text/html; charset=UTF-8";
    }
    if ($enable_email_footer === 0) {
        $email_footer_content = '';
    }
    $home_link_url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'javascript:void(0);';

    $enable_html_emails = homey_option('enable_html_emails');
    $socials = esc_html(get_social_row());

    $email_content = get_template_email($type, $message, $socials, $email_footer_content);

    $email_content = str_replace('{subject}', $subject, $email_content);
    $email_content = str_replace('{site_url}', $home_link_url, $email_content);

    $email_content = str_replace('{username_id}', trim($args['user_login_register']), $email_content);
    $email_content = str_replace('{email_user}', trim($args['user_email_register']), $email_content);
    $email_content = str_replace('{site_name}', get_bloginfo('name'), $email_content);
    $email_content = str_replace('{user_name}', trim($args['user_login_register']), $email_content);
    $email_content = str_replace('{role_user}', trim($args['user_role']), $email_content);
    $email_content = str_replace('{role_user}', trim($args['user_role']), $email_content);

    if (isset($args['activaction_url'])) {
        $email_content = str_replace('{user_verification_link}', trim($args['activaction_url']), $email_content);
        $token_expiration = $args['token_expiration'];
        $email_content = str_replace('{token_expiration}', trim($token_expiration), $email_content);
    }

    if (isset($args['user_profile'])) {
        $email_content = str_replace('{user_profile}', $args['user_profile'], $email_content);
    }

    homey_write_log("sending email: {$subject} to {$user_email}");
    $info_email = array(
        'email' => $user_email,
        'subject' => $subject,
        'message' => html_entity_decode($email_content),
        'name' => 'wanderloop.com',
        'headers' => $headers
    );
    $email_sent = smtpmail_sendmail($info_email);

    if ($email_sent) {
        error_log("email sent {$subject} to {$user_email}");
        return true;
    } else {
        error_log("email not sent {$subject} to {$user_email}");
    }
    return false;
};

function get_template_email($type, $message, $socials, $email_footer)
{
    $content_mail = '';
    switch ($type) {
        default:
        case 'new_user_register':
            $template_path = HX_TEMPLATES . "/emails";
            ob_start();
            include_once($template_path . '/new_user.phtml');
            $content_mail = ob_get_clean();
            ob_clean();
            break;
        case 'admin_new_user_register':
            $template_path = HX_TEMPLATES . "/emails/admin";
            ob_start();
            include_once($template_path . '/new_user.phtml');
            $content_mail = ob_get_clean();
            ob_clean();
            break;
    }
    return $content_mail;
}

function get_social_row()
{
    $socials = '';
    $social_1_icon = homey_option('social_1_icon', false, 'url');
    $social_1_link = homey_option('social_1_link');
    $social_2_icon = homey_option('social_2_icon', false, 'url');
    $social_2_link = homey_option('social_2_link');
    $social_3_icon = homey_option('social_3_icon', false, 'url');
    $social_3_link = homey_option('social_3_link');
    $social_4_icon = homey_option('social_4_icon', false, 'url');
    $social_4_link = homey_option('social_4_link');

    if (!empty($social_1_icon) || !empty($social_2_icon) || !empty($social_3_icon) || !empty($social_4_icon)) {
        $socials = '<p style="margin:0;margin-bottom: 10px; text-align: center; font-size: 14px; color:#777777;">' . esc_html__('Follow us on', 'homey-core') . '</p>';

        if (!empty($social_1_icon)) {
            $socials .= '<a href="' . $social_1_link . '" style="margin-right: 5px"><img src="' . $social_1_icon . '"> </a>';
        }
        if (!empty($social_2_icon)) {
            $socials .= '<a href="' . $social_2_link . '" style="margin-right: 5px"><img src="' . $social_2_icon . '"> </a>';
        }
        if (!empty($social_3_icon)) {
            $socials .= '<a href="' . $social_3_link . '" style="margin-right: 5px"><img src="' . $social_3_icon . '"> </a>';
        }
        if (!empty($social_4_icon)) {
            $socials .= '<a href="' . $social_4_link . '" style="margin-right: 5px"><img src="' . $social_4_icon . '"> </a>';
        }
    }
    return $socials;
}
