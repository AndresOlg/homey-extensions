<?php
include_once(HX_PLUGIN_PATH . '/classes/errors/class-hx-errors.php');
include_once(HX_PLUGIN_PATH . '/functions/functions-utils.php');
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
            static::$errorHandler = new ErrorHandler();

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
            array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile());
        }
        return $response;
    }

    private static function validateGeneralForm()
    {
        $user = userExist(static::$data_form);
        if ($user) {
            $response = '';
            if (!empty($user['email'])) {
                $error_data = static::$errorHandler->getError('ERR-U007', 'user');
                $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            } elseif ($user['username']) {
                $error_data = static::$errorHandler->getError('ERR-U008', 'user');
                $response = array('status' => 'error', 'code' => $error_data['code'], 'message' => $error_data['message']);
            }
            return $response;
        } else {
            $response_img = static::$instance->validateProfileImage();
            if ($response_img['filename']) {
                $response = array('status' => 'success', 'message' => 'The user is valid');
            } else {
                $response = $response_img;
            }
            return $response;
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


    public static function saveGeneralData()
    {
        $profile_image = static::$data_form['image_base64'];
        $image_data = self::saveProfileImage($profile_image);

        if ($image_data['status'] !== 'success') return $image_data;

        static::$data_form['filename'] = $image_data['data']['filename'];
        static::$data_form['filepath'] = $image_data['data']['filepath'];
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
            $file_path = HX_UPLOAD_DIR['basedir'] . '/homey-extensions/profile_avatars/';

            $img_data = getDataBinaryImg($profile_image);
            $result_saveImg = saveImage_WP($img_data, $file_path, $file_name, $file_extension);
            $response = $result_saveImg;

            return $response;
        } catch (\Throwable $th) {
            array('status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile());
        }
    }
}
