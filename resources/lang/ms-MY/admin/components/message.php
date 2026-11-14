<?php

return [

    'does_not_exist' => 'Komponen tidak wujud.',

    'create' => [
        'error' => 'Komponen tidak dibuat, sila cuba lagi.',
        'success' => 'Komponen berjaya dihasilkan.',
    ],

    'update' => [
        'error' => 'Komponen tidak dikemas kini, sila cuba lagi',
        'success' => 'Komponen berjaya dikemas kini.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Adakah anda pasti ingin memadamkan komponen ini?',
        'error' => 'Terdapat isu memadamkan komponen. Sila cuba lagi.',
        'success' => 'Komponen telah berjaya dipadam.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponen tidak diperiksa, sila cuba lagi',
        'success' => 'Komponen diperiksa dengan jayanya.',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tiada medan berubah, jadi tiada apa yang dikemas kini.',
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
        'error' => 'Komponen tidak diperiksa, sila cuba lagi',
        'success' => 'Komponen diperiksa dengan jayanya.',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.',
    ],

];
