<?php
class RegistrationFormValidator
{
    protected static $instance;
    
    public static function run()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function validateFormData($data_form)
    {
        $isvalidate = null;
        $type = $data_form->type;
        switch ($type) {
            case 'main':
                $isvalidate = self::validateGeneralForm();
                break;
            case 'hoster':
                $isvalidate = self::validateHosterForm();
                break;
            case 'traveler':
                $isvalidate = self::validateTravelerForm();
                break;
        }

        return $isvalidate == true ? $data_form : $isvalidate;
    }

    private static function validateGeneralForm()
    {
        $required_fields = array(
            'name', 'lastname', 'gender', 'country_residence',
        );

        if (isset($_POST['general_register'])) {
            $errors = array();

            if (empty($_POST['name'])) {
                $errors['username'] = 'El nombre de usuario es obligatorio.';
            }

            if (empty($_POST['email'])) {
                $errors['email'] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Ingrese una dirección de correo electrónico válida.';
            }

            if (empty($_POST['password'])) {
                $errors['password'] = 'La contraseña es obligatoria.';
            }
            if (!empty($errors)) return array('errors' => $errors);
            else return array('status' => 'OK', 'message' => 'success');
        }
    }

    private static function validateHosterForm()
    {
        if (isset($_POST['hoster_form'])) {
            $errors = array();

            // Validar campos requeridos
            if (empty($_POST['username'])) {
                $errors['username'] = 'El nombre de usuario es obligatorio.';
            }

            if (empty($_POST['email'])) {
                $errors['email'] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Ingrese una dirección de correo electrónico válida.';
            }

            if (empty($_POST['password'])) {
                $errors['password'] = 'La contraseña es obligatoria.';
            }
            if (!empty($errors)) return ($errors);
            else return true;
        }
    }

    private static function validateTravelerForm()
    {
        if (isset($_POST['traveler_form'])) {
            $errors = array();

            // Validar campos requeridos
            if (empty($_POST['username'])) {
                $errors['username'] = 'El nombre de usuario es obligatorio.';
            }

            if (empty($_POST['email'])) {
                $errors['email'] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Ingrese una dirección de correo electrónico válida.';
            }

            if (empty($_POST['password'])) {
                $errors['password'] = 'La contraseña es obligatoria.';
            }
            if (!empty($errors)) return ($errors);
            else return true;
        }
    }
}
