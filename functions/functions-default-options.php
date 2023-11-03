<?php

/**
 * @insert_traveler_preferences
 * TRAVELER  rol user
 */
function insert_traveler_preferences()
{
    global $wpdb;
    $table_name = HX_PREFIX . 'preferences';
    $json_data = array();

    /*
    array(
        description_preference, 'traveler: only preferences by rol',
        'Question or description?',
        'category preference',
        'type input in the form',
        'data options json format',
        'icon',
        '1 for default' status 1 for active 0 for inactive
    )*/

    $json_data['check'] = array(
        'data' => array(
            array('description' => 'check', 'value' => 'YES')
        ), 'limit' => 1
    );

    $json_data['experiences'] = array(
        'limit' => 0,
        'data' => array(
            array('id' => 0, 'description' => 'History', 'value' => 'NO'),
            array('id' => 1, 'description' => 'Culture', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Art', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Gastronomy tours', 'value' => 'NO'),
            array('id' => 4, 'description' => 'Cooking', 'value' => 'NO'),
            array('id' => 5, 'description' => 'Dance Classes', 'value' => 'NO'),
            array('id' => 6, 'description' => 'Others', 'value' => 'NO'),
        )
    );

    $json_data['favorite_vacations'] = array(
        'limit' => 4,
        'data' => array(
            array('id' => 1, 'description' => 'Visit only tourist sites', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Urban adventurer', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Nature lover', 'value' => 'NO'),
            array('id' => 4, 'description' => 'Foodie', 'value' => 'NO'),
            array(
                'id' => 5,
                'description' => 'I love living new experiences such as gastronomy tours, culture tours or walks',
                'value' => 'NO'
            )
        )
    );

    $json_data['favorite_vacations'] = array(
        'limit' => 4,
        'data' => array(
            array('id' => 1, 'description' => 'Visit only tourist sites', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Urban adventurer', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Nature lover', 'value' => 'NO'),
            array('id' => 4, 'description' => 'Foodie', 'value' => 'NO'),
            array(
                'id' => 5,
                'description' => 'I love living new experiences such as gastronomy tours, culture tours or walks',
                'value' => 'NO'
            )
        )
    );

    $json_data['kind_accommodation'] = array(
        'limit' => 1,
        'data' => array(
            array('id' => 1, 'description' => 'Where the action happens', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Quieter places', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Nature', 'value' => 'NO'),
            array('id' => 4, 'description' => 'All of the above', 'value' => 'NO'),
        )
    );

    $json_data['stay_city'] = array(
        'limit' => 2,
        'data' => array(
            array('id' => 1, 'description' => 'I like a good hotel / airbnb with a good price', 'value' => 'NO'),
            array('id' => 2, 'description' => 'I like a luxurious hotel / airbnb', 'value' => 'NO'),
        )
    );

    $json_data['traveler_style'] = array(
        'limit' => 1,
        'data' => array(
            array('id' => 1, 'description' => 'Luxury', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Standard', 'value' => 'NO'),
            array('id' => 3, 'description' => 'I seek to save where I can', 'value' => 'NO'),
        )
    );

    $data_to_insert =
        array(
            /*{ Preferences sightseeing category}*/
            array(
                'sightseeing',
                'Do you like sightseeing?',
                'sightseeing',
                'check',
                json_encode($json_data['check']),
                'default.png',
                '1'
            ),

            /*{-------------------------------------------------------------}*/

            /*{ Preferences accommodation category}*/
            array(
                'hotel-airbnbs',
                'Do you Need hotel/Airbnb recommendations?',
                'Accommodation',
                'check',
                json_encode($json_data['check']), 'default.png'
            ),

            array(
                'kind-accommodation',
                'What kind of accommodations do you prefer?',
                'accommodation',
                'select',
                json_encode($json_data['kind_accommodation']),
                'default.png'
            ),

            /*{ Preferences traveler style}*/
            array(
                'traveler-style',
                'Which is your traveler style?',
                'style',
                'check',
                json_encode($json_data['traveler_style']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences traveler transportation}*/
            array(
                'hotelairpot-transportation',
                'Do you need transfers from the airport to the Hotel?',
                'transportation',
                'check',
                json_encode($json_data['check']), 'default.png'
            ),
            array(
                'rent-car',
                'Do you need to rent a car?',
                'transportation',
                'check',
                json_encode($json_data['check']), 'default.png'
            ),
            array(
                'special-transportation',
                'Do you need any kind of special transportation?',
                'transportation',
                'check',
                json_encode($json_data['check']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences traveler experiences}*/
            array(
                'favorite-experiences',
                'Choose your favorite experiences: &nbsp;&nbsp;',
                'experiences',
                'multiselect',
                json_encode($json_data['experiences']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences traveler vacations}*/
            array(
                'favorite-vacations',
                'Choose the 4 options that make the ideal vacation for you:&nbsp;&nbsp;',
                'vancation',
                'multiselect',
                json_encode($json_data['favorite_vacations']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences stay city}*/
            array(
                'stay-city',
                'In which part of the city do you prefer to stay?',
                'stay',
                'multiselect',
                json_encode($json_data['stay_city']),
                'default.png'
            )

            /*{-------------------------------------------------------------}*/
        );

    $check_query = "SELECT COUNT(*) FROM $table_name where preferences_type='traveler'";
    $table_row_count = $wpdb->get_var($check_query);

    if ($table_row_count == 0) {
        foreach ($data_to_insert as $idx => $data) {
            $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE preference_name = %s", $data[0]));
            if (!$existing_record) {
                $wpdb->insert($table_name, array(
                    'preference_name' => $data[0],
                    'preferences_type' => 'traveler',
                    'preferences_question' => $data[1],
                    'preferences_category' => $data[2],
                    'preferences_typefield' => $data[3],
                    'preferences_options' => $data[4],
                    'preferences_ico' => $data[5],
                    'preferences_status' => 1
                ));
            }
        }
    }
}

/**
 * @insert_hoster_preferences
 * HOSTER  rol user
 */
function insert_hoster_preferences()
{
    global $wpdb;
    $table_name = HX_PREFIX . 'preferences';
    $json_data = array();

    /*
    array(
        description_preference, 'hoster: only preferences by rol',
        'Question or description?',
        'category preference',
        'type input in the form',
        'data options json format',
        'icon',
        '1 for default' status 1 for active 0 for inactive
    )*/

    $json_data['check'] = array(
        'data' => array(
            array('description' => 'check', 'value' => 'YES')
        ), 'limit' => 1
    );

    $json_data['cities_region'] = array(
        'limit' => 0,
        'data' => array()
    );

    $json_data['expertise_area'] = array(
        'limit' => 5,
        'data' => array(
            array(
                'id' => 0,
                'description' => 'History: Knowledge of the history of the country/city and its emblematic',
                'value' => 'NO'
            ),
            array('id' => 1, 'description' => 'Art', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Drinks: Beer / Cocktails / Drink', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Party: You know your way around clubs and bars', 'value' => 'NO'),
            array('id' => 4, 'description' => 'Rest: Breaches / Lakes / Islands', 'value' => 'NO'),
            array('id' => 5, 'description' => 'Local Culture', 'value' => 'NO',),
            array('id' => 6, 'description' => 'City Market Lover', 'value' => 'NO',),
            array('id' => 7, 'description' => 'Nature and Adventure enthusiast', 'value' => 'NO',),
            array('id' => 8, 'description' => 'Chef wanting to teach', 'value' => 'NO',),
            array('id' => 9, 'description' => 'Photography', 'value' => 'NO',),
            array('id' => 10, 'description' => 'Other', 'value' => 'NO',),
        )
    );

    $json_data['local_culture'] = array(
        'limit' => 4,
        'data' => array(
            array('id' => 0, 'description' => 'Local History', 'value' => 'NO'),
            array('id' => 1, 'description' => 'City Secrets', 'value' => 'NO'),
            array('id' => 2, 'description' => 'Religion', 'value' => 'NO'),
            array('id' => 3, 'description' => 'Music / Dance', 'value' => 'NO'),
            array('id' => 4, 'description' => 'Myths, Legends, Ghost related stories', 'value' => 'NO'),
            array('id' => 5, 'description' => 'Architecture', 'value' => 'NO'),
            array('id' => 6, 'description' => 'Fashion', 'value' => 'NO'),
            array('id' => 7, 'description' => 'Street Art', 'value' => 'NO'),
            array('id' => 8, 'description' => 'Other', 'value' => 'NO'),
        )
    );

    $json_data['favorite_traveler'] = array(
        'limit' => 1,
        'data' => array(
            array(
                'id' => 0,
                'description' => 'Standard traveler: Stay at hotels the cost less than $300 US  x night',
                'value' => 'NO'
            ),
            array(
                'id' => 1,
                'description' => 'Medium Luxury traveler: Stay at hotels the cost between $301 - $500US x night',
                'value' => 'NO'
            ),
            array(
                'id' => 2,
                'description' => 'Premium Luxury Traveler: Stay at hotels the cost more than $501US x night',
                'value' => 'NO'
            ),

        )
    );


    $data_to_insert =
        array(
            /*{ Preferences expertise category}*/
            array(
                'expertise_area',
                'Choose your area of expertise. (Maximum 5):&nbsp;&nbsp;',
                'expertise',
                'multiselect',
                json_encode($json_data['expertise_area']),
                'default.png',
            ),

            array(
                'expertise_area',
                'Local Culture (Choose 4 Maximum)',
                'expertise',
                'multiselect',
                json_encode($json_data['local_culture']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences type of travel}*/
            array(
                'favorite_travel',
                'With which type of travel do you feel more comfortable while hosting?',
                'travel_type',
                'check',
                json_encode($json_data['favorite_traveler']), 'default.png'
            ),
            /*{-------------------------------------------------------------}*/

            /*{ Preferences region to work}*/
            array(
                'region_preference',
                'Which cities are you going to work on at Wanderloop to create trips or experiences?',
                'region_work',
                'multiselect',
                json_encode($json_data['cities_region']), 'default.png'
            )
            /*{-------------------------------------------------------------}*/
        );

    // Insert default data
    $check_query = "SELECT COUNT(*) FROM $table_name  where preferences_type='hoster'";
    $table_row_count = $wpdb->get_var($check_query);

    if ($table_row_count == 0) {
        foreach ($data_to_insert as $idx => $data) {
            $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE preference_name = %s", $data[0]));
            if (!$existing_record) {
                $wpdb->insert($table_name, array(
                    'preference_name' => $data[0],
                    'preferences_type' => 'hoster',
                    'preferences_question' => $data[1],
                    'preferences_category' => $data[2],
                    'preferences_typefield' => $data[3],
                    'preferences_options' => $data[4],
                    'preferences_ico' => $data[5],
                    'preferences_status' => 1
                ));
            }
        }
    }
}
