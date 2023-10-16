<?php
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
            $data['url']['country'] = "{$url_base}countryInfoJSON?country={{ID}}&maxRows=1";
            $data['url']['states'] = "{$url_base}childrenJSON?geonameId={{ID}}&maxRows=195";
            $data['url']['cities'] = "{$url_base}childrenJSON?geonameId={{ID}}&maxRows=195";
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
    $file_json = HX_PLUGIN_PATH . '/jsons/country_phone_codes.json';
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
function getObjectByProperty($data, $property, $value)
{
    foreach ($data as $countryCode => $country) {
        if ($country->$property === $value) {
            return $country;
        }
    }
    return null;
}
