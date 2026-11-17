<?php

return [

    'does_not_exist' => 'Ingxenye ayikho.',

    'create' => [
        'error' => 'Ingxenye ayizange idalwe, sicela uzame futhi.',
        'success' => 'Ingxenye idalwe ngempumelelo.',
    ],

    'update' => [
        'error' => 'Ingxenye ayizange ibuyekezwe, sicela uzame futhi',
        'success' => 'Ingxenye ibuyekezwe ngempumelelo.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Uqinisekile ukuthi ufisa ukususa le ngxenye?',
        'error' => 'Kube nenkinga yokususa ingxenye. Ngicela uzame futhi.',
        'success' => 'Ingxenye isusiwe ngempumelelo.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Ingxenye ayizange ihlolwe, sicela uzame futhi',
        'success' => 'Ingxenye ihloliwe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Azikho amasimu ashintshiwe, ngakho akukho lutho olubuyekeziwe.',
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
        'error' => 'Ingxenye ayizange ihlolwe, sicela uzame futhi',
        'success' => 'Ingxenye ihlolwe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
    ],

];
