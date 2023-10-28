<?php

namespace ErrorsHX;

include_once(HX_PLUGIN_PATH . "/functions/functions-utils.php");
class ErrorHandler
{
    public static $data_error = array();
    protected  $error_codes = array();
    protected static $instance;

    public static $error_handler;
    public function __construct()
    {
        $this->init();
    }

    public static function getInstance()
    {
        return is_null(static::$instance) ? new ErrorHandler() : static::$instance;
    }


    protected function init()
    {
        $this->setErrors();
    }

    private function setErrors()
    {
        $file_json = HX_JSON_DIR . 'errors_codes.json';
        $this->error_codes = get_data_json($file_json)['errors'];
    }

    private function filterErrors($typeToFilter, $codeToFilter)
    {
        $errors = $this->error_codes;
        $filteredErrors = [];
        foreach ($errors as $error) {
            if ($error["type"] === $typeToFilter && $error["code"] === $codeToFilter) {
                $filteredErrors[] = $error;
            }
        }
        return $filteredErrors;
    }

    public function getError($code, $ambient)
    {
        $error_data =  $this->filterErrors($ambient, $code)[0];
        return array('code' => $error_data['code'], 'message' => $error_data['message']);
    }
}
