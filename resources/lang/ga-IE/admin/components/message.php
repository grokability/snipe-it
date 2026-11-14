<?php

return [

    'does_not_exist' => 'Níl an comhpháirt ann.',

    'create' => [
        'error' => 'Níor cruthaíodh an comhpháirt, déan iarracht arís.',
        'success' => 'Comhpháirt cruthaithe go rathúil.',
    ],

    'update' => [
        'error' => 'Níor nuashonraíodh an comhpháirt, déan iarracht arís',
        'success' => 'Comhpháirt nuashonraithe go rathúil.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'An bhfuil tú cinnte gur mian leat an comhábhar seo a scriosadh?',
        'error' => 'Bhí ceist ann a scriosadh an chomhpháirt. Arís, le d\'thoil.',
        'success' => 'Scriosadh an comhpháirt go rathúil.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Níor sheiceáil amach an comhpháirt, déan iarracht arís',
        'success' => 'Seiceáil amach an comhpháirt go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Níor athraíodh aon réimsí, mar sin níor nuashonraíodh aon rud.',
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
        'error' => 'Níor sheiceáil an comhpháirt isteach, déan iarracht arís',
        'success' => 'Seiceáil ar an gcomhpháirt go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
    ],

];
