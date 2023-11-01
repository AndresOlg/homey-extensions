<?php
include_once(HX_PLUGIN_PATH . "functions/functions-sessions.php");
function encryptValue($value, $secret_key)
{
    return base64_encode(openssl_encrypt($value, 'aes-256-cbc', $secret_key, 0, $secret_key));
}
function decryptValue($encrypted_value, $secret_key)
{
    return openssl_decrypt(base64_decode($encrypted_value), 'aes-256-cbc', $secret_key, 0, $secret_key);
}

function decodeCharacters($asciiValue)
{
    $asciiArray = explode(',', $asciiValue);
    $decodedValue = implode(array_map('chr', $asciiArray));
    return $decodedValue;
}

function get_request_auth()
{
    $encrypt_file = HX_PLUGIN_PATH . '/data/.encrypt';
    if (file_exists($encrypt_file)) {
        // Read file content
        $content = file_get_contents($encrypt_file);
        $lines = explode("\n", $content);
        $decoded_value = '';
        if (count($lines) === 3) {
            $encoded_value = $lines[1];
            $chars_encode = explode('|', $encoded_value);
            foreach ($chars_encode as $char_code) {
                $decoded_value .= decodeCharacters(intval($char_code));
            }
            $decoded_value = encryptValue($decoded_value, $lines[0]);
            $data = array(
                "secret_key" => $lines[0],
                "user_name" => $decoded_value,
                "url" => array("base" => $lines[2])
            );

            $url_base = $data['url']['base'];

            $data['url']['countries'] = "{$url_base}searchJSON?fcode=PCLI&maxRows=195";
            $data['url']['country'] = "{$url_base}countryInfoJSON?country={{COUNTRY}}&maxRows=1";
            $data['url']['states'] = "{$url_base}search?fcode=ADM1&country={{COUNTRY}}&style=SHORT&lang=en&maxRows=100&type=json";
            $data['url']['cities'] = "{$url_base}?q={{STATE}}&country={{COUNTRY}}&style=SHORT&lang=en&maxRows=100&type=json";
            return $data;
        } else {
            echo "Incorrect format .encrypt, contact your plugin provider";
            return false;
        }
    } else {
        echo "The file not exist!";
        return false;
    }
}


function get_data_phonecodes()
{
    $file_json = HX_JSON_DIR . 'country_phone_codes.json';
    return get_data_json($file_json);
}

function get_data_json($file_json)
{
    if (file_exists($file_json)) {
        $contentJSON = file_get_contents($file_json);
        $datos = $contentJSON;
        $arrayData = json_decode($datos, true);
        if ($arrayData === null) {
            echo "Error parse JSON...";
        } else {
            return $arrayData;
        }
    } else {
        echo "Error file not exist!";
    }
}

function getDataBinaryImg($image_base64)
{
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image_base64));
    return $image_data;
}

function saveImage_WP($image_data, $file_path, $file_name, $file_extension)
{
    try {
        $new_image_data = createResizeImg($file_extension, $image_data);
        if (wp_mkdir_p($file_path)) {
            if (file_put_contents($file_path . $file_name, $new_image_data)) {
                return array('status' => 'success', 'message' => 'Profile picture saved successfully!', 'data' => array('filename' => $file_name, 'filepath' => $file_path));
            } else {
                return array('status' => 'error', 'message' => 'Error saving profile image!');
            }
        } else {
            return array('status' => 'error', 'message' => 'Error to create path file!');
        }
    } catch (\Throwable $th) {
        throw $th;
    }
}

function generate_data_ajax()
{
    $nonce = wp_create_nonce('security_nonce');
    $ajax_url = admin_url('admin-ajax.php');

    return array(
        'security_nonce' => $nonce,
        'ajax_url' => $ajax_url,
    );
}

function createResizeImg($ext, $image_data)
{
    try {
        $target_image = '';
        $source_image = '';

        $source_image = imagecreatefromstring($image_data);
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        $target_width = $source_width / 2;
        $target_height = $source_height / 2;

        $target_image = imagecreatetruecolor($target_width, $target_height);
        imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
        $resized_image_data = getResizedImageData($target_image, $ext);

        imagedestroy($source_image);
        imagedestroy($target_image);
        return $resized_image_data;
    } catch (\Throwable $th) {
        throw $th;
    }
}

function getResizedImageData($image, $extension)
{
    ob_start();
    if ($extension === 'jpg' or $extension === 'jpeg') {
        imagejpeg($image, null, 85);
    } elseif ($extension === 'png') {
        imagepng($image, null, 8);
    } elseif ($extension === 'gif') {
        imagegif($image, null);
    }
    return ob_get_clean();
}


/**
 * @param $user_data array()
 * @return array
 */
function user_exist($user_data)
{
    $user_email = $user_data['user_email'];
    $username = $user_data['user_login'];
    return array('email' => email_exists($user_email), 'username' => username_exists($username));
}


function get_user_role($role)
{
    if ($role === 'traveler') return 'homey_renter';
    elseif ($role === 'hoster') return 'homey_host';
    else return 'subscriber';
}

function include_templates_emails()
{
    $plugin_dir_smtp = WP_PLUGIN_DIR . '/smtp-mail';
    $theme_dir = get_theme_root() . '/homey/framework';

    include_once $plugin_dir_smtp . '/index.php';
    include_once $theme_dir . '/functions/helper.php';
    include_once $theme_dir . '/options/homey-option.php';
}

function user_id_to_hash($user_id)
{
    return dechex($user_id);
}
function hash_to_user_id($hash_id)
{
    return hexdec($hash_id);
}

function user_is_verified($user)
{
    $user_to_found = new WP_User($user->ID);
    if (!empty($user_to_found->user_activation_key)) return true;
    else return false;
}

function user_is_verified_by_email($user)
{
    if (!empty(get_user_meta($user->ID, 'verification_id', true)) && get_user_meta($user->ID, 'is_email_verified', true) == 0) {
        return false;
    }
    return true;
}

function ascii_string($asciiValues)
{
    $characters = array();
    foreach ($asciiValues as $asciiValue) {
        $character = chr($asciiValue);
        $characters[] = $character;
    }
    return implode("", $characters);
}
