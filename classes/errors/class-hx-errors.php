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
        $this->setErrors();
    }

    private function setErrors()
    {
        $file_json = HX_JSON_DIR . 'errors_codes.json';
        $this->error_codes = get_data_json($file_json)['errors'];
    }

    private function filterErrors($typeToFilter, $codeToFilter)
    {
        $errors = static::$error_codes;
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
        $this->renderErrorModal($error_data['message']);
        return array('code' => $error_data['code'], 'message' => $error_data['message']);
    }

    private function renderErrorModal($errorMessage)
    {
        echo '<div id="errorModal">';
        echo '<div class="modal-content notice notice-error settings-error is-dismissible">';
        echo
        "<p>
        <strong>
            <span style=\"display: block; margin: 0.5em 0.5em 0 0; clear: both;\">This theme recommends the following
                plugin: <em>
                    {$errorMessage}
                </em>.
            </span>
        </strong>
        </p>
        <button type=\"button\" class=\"notice-dismiss\">
        <span class=\"screen-reader-text\">Dismiss this
            notice.</span>
        </button>
        ";
        echo '</div>';
        echo '</div>';

        echo '<style>';
        echo '.modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9999;
        }';

        echo '.modal-content {
            background-color: #fff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            text-align: center;
        }';

        echo '.modal-icon {
            font-size: 48px;
            color: red;
        }';

        echo '.close {
            position: absolute;
            top: 0;
            right: 0;
            padding: 10px;
            cursor: pointer;
        }';
        echo '</style>';

        echo '<script>';
        echo 'function showModal() {
            document.getElementById("errorModal").style.display = "block";
        }';

        echo 'function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }';
        echo '</script>';
    }
}
