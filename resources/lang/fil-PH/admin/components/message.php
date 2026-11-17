<?php

return [

    'does_not_exist' => 'Ang komponent na ito ay hindi umiiral.',

    'create' => [
        'error' => 'Ang komponent ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Ang komponent ay matagumpay na naisagawa.',
    ],

    'update' => [
        'error' => 'Ang komponent ay hindi nai-update, manyaring subukang muli',
        'success' => 'Ang komponent ay matagumpay na naisagawa.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Sigurado kaba na gusto mong i-delete ang komponent na ito?',
        'error' => 'Mayroong isyu sa pag-delete ng komponent. Mangayring subukang muli.',
        'success' => 'Ang komponent ay matagumpay na naisagawa.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Ang komponent ay hindi nai-check out, mangyaring subukang muli',
        'success' => 'Ang komponent ay matagukpay nang nai-check out.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
        'unavailable' => 'Kulang ang bilang ng natitirang komponent: :remaining ang natitira, :requested ang kinukuha ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Walang nabagong mga field, kaya walang nai-update.',
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
        'error' => 'Ang kompnent ay hindi nai-check in, mangyaring subukang muli',
        'success' => 'Ang komponent ay matagumpay na nai-check in.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
    ],

];
