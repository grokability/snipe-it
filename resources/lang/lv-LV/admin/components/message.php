<?php

return [

    'does_not_exist' => 'Komponents neeksistē.',

    'create' => [
        'error' => 'Komponents nav izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Komponents tika veiksmīgi izveidots.',
    ],

    'update' => [
        'error' => 'Komponents nav atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Komponents ir veiksmīgi atjaunināts.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Vai tiešām vēlaties dzēst šo komponentu?',
        'error' => 'Radās problēma, izdzēšot komponentu. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Komponents tika veiksmīgi dzēsts.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponents netika pārbaudīts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Komponents veiksmīgi pārbaudīts.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Neviens laukums netika mainīts, tāpēc nekas netika atjaunināts.',
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
        'error' => 'Komponents nav reģistrēts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Komponents ir veiksmīgi reģistrēts.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
    ],

];
