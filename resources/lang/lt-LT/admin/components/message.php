<?php

return [

    'does_not_exist' => 'Tokio komponento nėra.',

    'create' => [
        'error' => 'Komponentas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Komponentas sėkmingai sukurtas.',
    ],

    'update' => [
        'error' => 'Komponentas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai atnaujintas.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite panaikinti šį komponentą?',
        'error' => 'Bandant panaikinti komponentą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Komponentas sėkmingai panaikintas.',
        'error_qty' => 'Kai kurie šio tipo komponentai vis dar yra išduoti. Paimkite juos ir bandykite dar kartą.',
    ],

    'checkout' => [
        'error' => 'Komponentas nebuvo išduotas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai išduotas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'unavailable' => 'Nepakanka komponentų: yra :remaining, o prašoma :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
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
        'error' => 'Komponentas nebuvo paimtas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai paimtas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
    ],

];
