<?php

return [

    'does_not_exist' => 'Aksesori [:id] tidak wujud.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Aksesori ini pada masa ini mempunyai: mengira item yang diperiksa kepada pengguna. Sila semak aksesori dan cuba lagi.',

    'create' => [
        'error' => 'Aksesori tidak dicipta, sila cuba lagi.',
        'success' => 'Aksesori telah berjaya dicipta.',
    ],

    'update' => [
        'error' => 'Aksesori gagal dikemaskini, sila cuba lagi',
        'success' => 'Aksesori berjaya dikemaskini.',
    ],

    'delete' => [
        'confirm' => 'Anda pasti anda mahu membuang aksesori ini?',
        'error' => 'Ralat berlaku semasa membuang aksesori. Sila cuba lagi.',
        'success' => 'Aksesori berjaya dibuang.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Aksesori tidak diperiksa, sila cuba lagi',
        'success' => 'Aksesori diperiksa dengan jayanya.',
        'unavailable' => 'Tiada aksesori untuk dikeluarkan. Sila periksa kuantiti sedia ada',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Aksesori tidak diperiksa, sila cuba lagi',
        'success' => 'Aksesori diperiksa dengan jayanya.',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tiada medan berubah, jadi tiada apa yang dikemas kini.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
