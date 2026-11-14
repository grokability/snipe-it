<?php

return [

    'does_not_exist' => 'Komponenti pole olemas.',

    'create' => [
        'error' => 'Komponenti ei loodud, proovi uuesti.',
        'success' => 'Komponendi loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Komponenti ei värskendatud, proovige uuesti',
        'success' => 'Komponendi muutmine õnnestus.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle komponendi kustutada?',
        'error' => 'Komponendi kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Komponendi kustutamine õnnestus.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponenti ei kontrollitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt välja võetud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
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
        'error' => 'Komponent ei olnud märgitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt registreeritud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
    ],

];
