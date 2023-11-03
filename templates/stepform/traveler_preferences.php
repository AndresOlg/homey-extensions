<?php
include_once(HX_PLUGIN_PATH . "/classes/class-hx-preferences.php");
$steps = array();
// $category = 'transportation'; // Cambia esto por la categoría que desees filtrar
// $filteredPreferences = filterPreferencesByCategory($preferences, $category);
// $htmlStructure = generateHtmlStructure($preferences[0]);
function generateHtmlStructure($preference)
{
    $html = '';

    $options = json_decode($preference['preferences_options'], true);

    switch ($preference['preferences_typefield']) {
        case 'check':
            $html = '<input type="checkbox" id="' . $preference['id'] . '" name="' . $preference['preference_name'] . '" value="YES">';
            break;
        case 'select':
            $html = '<select id="' . $preference['id'] . '" name="' . $preference['preference_name'] . '">';
            $limit = isset($options['limit']) ? $options['limit'] : 1;
            $count = 0;
            foreach ($options['data'] as $option) {
                if ($count < $limit) {
                    $html .= '<option value="' . $option['value'] . '">' . $option['description'] . '</option>';
                    $count++;
                }
            }
            $html .= '</select>';
            break;
        case 'multiselect':
            $html = '<select multiple id="' . $preference['id'] . '" name="' . $preference['preference_name'] . '[]">';
            $limit = isset($options['limit']) ? $options['limit'] : count($options['data']);
            $count = 0;
            foreach ($options['data'] as $option) {
                if ($count < $limit) {
                    $html .= '<option value="' . $option['value'] . '">' . $option['description'] . '</option>';
                    $count++;
                }
            }
            $html .= '</select>';
            break;
    }

    return $html;
}

function filterPreferencesByCategory($preferences, $category)
{
    $filteredPreferences = array();
    foreach ($preferences as $preference) {
        if ($preference['preferences_category'] == $category) {
            $filteredPreferences[] = $preference;
        }
    }
    return $filteredPreferences;
}
function generateBootstrapForm($preferences, $category)
{
    $html = '<div class="container mt-5">';
    $html .= '<h2 class="text-center">Formulario de Preferencias</h2>';
    $html .= '<div class="row">';

    // Filtra las preferencias por categoría
    $filteredPreferences = filterPreferencesByCategory($preferences, $category);

    foreach ($filteredPreferences as $preference) {
        $html .= '<div class="col-md-6">';
        $html .= '<div class="card mb-4">';
        $html .= '<div class="card-body">';
        $html .= '<h5 class="card-title">' . $preference['preferences_question'] . '</h5>';
        $html .= generateHtmlStructure($preference);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<button type="submit" class="btn btn-primary">Save</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function initSteps_traveler()
{
    $preferences_instance = new \PreferencesData();
    $preferences = $preferences_instance->getByAttr("preferences_type", "traveler");
    global $steps;

    // Inicializa $steps como un array vacío para cada categoría de preferencias
    $steps = array();

    foreach ($preferences as $preference) {
        $category = $preference['preferences_category'];

        // Verifica si ya existe una entrada para la categoría en $steps
        if (!isset($steps[$category])) {
            $steps[$category] = array(); // Inicializa un array vacío para esta categoría
        }

        // Agrega el formulario al array de la categoría
        $steps[$category][] = generateBootstrapForm($preferences, $category);
    }

    // A continuación, puedes construir la estructura HTML para cada categoría
    foreach ($steps as $category => $formArray) {
        $html = "<div class='container'>
        <form name='traveler_{$category}'>";

        foreach ($formArray as $dataHTML) {
            $html .= $dataHTML;
        }

        $html .= "</form></div>";
        $steps[$category] = $html;
    }
}

initSteps_traveler(); // Asegúrate de llamar a la función para llenar el array $steps.

// Crear y registrar los shortcodes en WordPress
function create_and_register_shortcodes()
{
    global $steps;
    foreach ($steps as $category => $html) {
        add_shortcode($category, function () use ($html) {
            return $html;
        });
    }
}
create_and_register_shortcodes();
function generate_and_save_shortcodes_json()
{
    global $steps;
    $json_data = json_encode($steps);
    file_put_contents('shortcodes.json', $json_data);
}

generate_and_save_shortcodes_json();
function get_shortcode_data($shortcode_name)
{
    $json_data = file_get_contents('shortcodes.json');
    $shortcode_data = json_decode($json_data, true);

    if (isset($shortcode_data[$shortcode_name])) {
        return $shortcode_data[$shortcode_name];
    } else {
        return 'Shortcode no found';
    }
}
