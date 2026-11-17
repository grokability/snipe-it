<?php

return [

    'does_not_exist' => 'Komponenttia ei ole olemassa.',

    'create' => [
        'error' => 'Komponenttia ei luotu, yritä uudelleen.',
        'success' => 'Komponentti on luotu onnistuneesti.',
    ],

    'update' => [
        'error' => 'Komponenttia ei ole päivitetty, yritä uudelleen',
        'success' => 'Komponentti on päivitetty onnistuneesti.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Haluatko varmasti poistaa tämän komponentin?',
        'error' => 'Komponentti poisto ei onnistunut. Yritä uudelleen.',
        'success' => 'Komponentti poistettiin.',
        'error_qty' => 'Jotkut tämän tyyppiset komponentit ovat vielä luovutettuna. Palauta ne ja yritä uudelleen.',
    ],

    'checkout' => [
        'error' => 'Komponenttia ei luovutettu, yritä uudelleen',
        'success' => 'Komponentin luovutus onnistui.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
        'unavailable' => 'Ei riittävästi komponentteja: :remaining jäljellä, :requested pyydetty ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Mitään kentistä ei ollut muutettu, joten mitään ei päivitetty.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponenttia ei palautettu, yritä uudelleen',
        'success' => 'Komponentti palautettiin onnistuneesti.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
    ],

];
