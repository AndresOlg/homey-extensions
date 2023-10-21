<?php
include_once(HX_PLUGIN_PATH . "/functions/functions-utils.php");
class ErrorHandler
{
    public static $data_error = array();
    protected $error_codes = array();
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
    }

    private function setErrors()
    {
        $file_json = HX_JSON_DIR . 'errors_codes.json';
        $this->error_codes = get_data_json($file_json);
    }

    public function handleError()
    {
    }

    public function renderError(){

    }

    
}
