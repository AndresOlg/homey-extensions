<?php

namespace ManageFormsHX;

include_once(HX_PLUGIN_PATH . '/classes/errors/class-hx-errors.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');

use ErrorsHX\ErrorHandler as ErrorHandler;

class ManageRegistrationForm
{
    public static $filename_img;
    protected static $instance;
    protected static $errorHandler;
    protected static $data_form;
    public static function manageGeneralFormData($data_form)
    {
        $response = null;
        $type = $data_form['type'];
        static::$data_form = $data_form;
        static::$instance = new ManageRegistrationForm();

        try {
            static::$errorHandler = ErrorHandler::getInstance();

            switch ($type) {
                case 'main':
                    $response = self::validateGeneralForm();
                    break;
                case 'hoster':
                    // $isvalidate = self::validateHosterForm();
                    break;
                case 'traveler':
                    // $isvalidate = self::validateTravelerForm();
                    break;
            }
        } catch (\Throwable $th) {
            $response = array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile());
        }
        return $response;
    }

    private static function validateGeneralForm()
    {
        $allowed_html = array();
        static::$data_form['user_login'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_login'], $allowed_html)));
        static::$data_form['user_email'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_email'], $allowed_html)));
        static::$data_form['user_role'] = trim(sanitize_text_field(wp_kses(static::$data_form['user_role'], $allowed_html)));

        $user = user_exist(static::$data_form);
        $response = '';

        if (!empty($user['user_email'])) {
            $error_data = static::$errorHandler->getError('ERR-U007', 'user');
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
        } elseif (!empty($user['user_login'])) {
            $error_data = static::$errorHandler->getError('ERR-U008', 'user');
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
        } else {
            $user = static::$data_form;
            $response_img = static::$instance->validateProfileImage();
            if ($response_img['filename']) {
                if (get_option('users_can_register') != 1) {
                    $response = array('status' => 'error', 'message' => esc_html__('Access denied.', 'homey-login-register'));
                    return $response;
                }
                if (strlen($user['user_login']) < 3) {
                    $response = array('status' => 'error', 'message' => esc_html__('Invalid user_login <br> Minimum 3 characters required', 'homey-login-register'));
                    return $response;
                }
                if (preg_match("/^[0-9A-Za-z_]+$/", $user['user_login']) == 0) {
                    $response = array('status' => 'error', 'message' => esc_html__('Invalid user_login (do not use special characters or spaces)!', 'homey-login-register'));
                    return $response;
                }

                if (!is_email($user['user_email'])) {
                    $response = array('status' => 'error', 'message' => esc_html__('Invalid email address.', 'homey-login-register'));
                    return $response;
                }
                return array('status' => 'success', 'message' => 'The user is valid');
            } else {
                return  $response_img;
            }
        }
    }

    private static function validateHosterForm()
    {
    }

    private static function validateTravelerForm()
    {
    }

    public function validateProfileImage()
    {
        try {
            $profile_image = static::$data_form['image_base64'];
            $image_type = exif_imagetype($profile_image);
            $allowed_extensions = array(
                IMAGETYPE_GIF => 'gif',
                'IMAGETYPE_JPG' => 'jpg',
                IMAGETYPE_JPEG => 'jpeg',
                IMAGETYPE_PNG => 'png'
            );


            if (isset($allowed_extensions[$image_type])) {
                $file_extension = $allowed_extensions[$image_type];
                $prefix_img = substr(static::$data_form['user_login'], 0, 2) . '-';

                $file_name = 'avatar_' . strtoupper($prefix_img) . time() . '.' . $file_extension;
                $file_path = HX_UPLOAD_DIR['basedir'] . '/homey-extensions/profile_avatars/';

                return array('filename' => $file_name, 'filepath' => $file_path, 'file_ext' => $file_extension);
            } else {
                $error_data = static::$errorHandler->getError('ERR-F001', 'files');
                $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
                return $response;
            }
        } catch (\Throwable $th) {
            array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile());
        }
    }


    public static function saveUserData()
    {
        $profile_image = static::$data_form['image_base64'];

        $data_form = static::$data_form;
        $password = $data_form['user_pass'];
        $password = ascii_string(explode('-', $password));

        $user_data = array(
            'user_login' => $data_form['user_login'],
            'user_pass' => $password,
            'user_email' => $data_form['user_email'],
            'role' => get_user_role($data_form['user_role']),
            'user_status' => 0
        );



        if ($user_id = wp_insert_user($user_data)) {
            if (is_wp_error($user_id)) {
                $error_data = static::$errorHandler->getError('ERR-U009', 'user');
                $error_messages = $user_id->get_error_messages();

                $string_errors = implode("\n", $error_messages);

                $message = "{$error_data['message']}<br>$string_errors";
                $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $message);

                return $response;
            } else {
                static::$data_form['user_id'] = $user_id;
                $image_data = self::saveProfileImage($profile_image);
                if ($image_data['status'] !== 'success') return $image_data;

                static::$data_form['filename'] = $image_data['data']['filename'];
                static::$data_form['filepath'] = $image_data['data']['filepath'];

                return array('status' => 'success', 'user_id' => $user_id, 'filename' => static::$data_form['filename'], 'filepath' => static::$data_form['filepath'], 'pass' => $password);
            }
        } else {
            $error_data = static::$errorHandler->getError('ERR-U009', 'user');
            $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            return $response;
        }
    }

    private static function saveProfileImage($profile_image)
    {
        try {
            $profile_image = static::$data_form['image_base64'];
            $image_type = exif_imagetype($profile_image);

            $allowed_extensions = array(
                IMAGETYPE_GIF => 'gif',
                'IMAGETYPE_JPG' => 'jpg',
                IMAGETYPE_JPEG => 'jpeg',
                IMAGETYPE_PNG => 'png'
            );

            $file_extension = $allowed_extensions[$image_type];
            $prefix_img = substr(static::$data_form['user_login'], 0, 2) . '-';
            $file_name = 'avatar_' . strtoupper($prefix_img) . time() . '.' . $file_extension;
            $file_path = HX_UPLOAD_IMG . 'profile_avatars/';

            $img_data = getDataBinaryImg($profile_image);
            $result_saveImg = saveImage_WP($img_data, $file_path, $file_name, $file_extension);
            $response = $result_saveImg;

            $user_id = static::$data_form['user_id'];
            $attach_id = save_image($img_data, $file_path, $file_name, $file_extension);

            update_user_meta($user_id, 'homey_author_picture_id', $attach_id);

            return $response;
        } catch (\Throwable $th) {
            return array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => __FILE__);
        }
    }
}
