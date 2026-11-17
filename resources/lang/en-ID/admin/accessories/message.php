<?php

return [

    'does_not_exist' => 'Aksesori itu tidak ada.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Aksesori saat ini memiliki :count item untuk pengguna. Silahkan cek di aksesoris dan dan coba lagi. ',

    'create' => [
        'error' => 'Aksesori tidak dapat dibuat, silahkan coba kembali.',
        'success' => 'Aksesori berhasil dibuat.',
    ],

    'update' => [
        'error' => 'Aksesori tidak dapat diperbaharui, silahkan coba kembali',
        'success' => 'Aksesori berhasil diperbaharui.',
    ],

    'delete' => [
        'confirm' => 'Anda yakin ingin menghapus aksesoris ini?',
        'error' => 'Ada masalah untuk mengahpus aksesoris ini. Silahkan coba lagi.',
        'success' => 'Aksessoris ini berhasil dihapus.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Aksesori belum diperiksa, silakan coba lagi',
        'success' => 'Aksesori berhasil diperiksa.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Pengguna yang tidak valid. Silakan coba lagi.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Aksesori belum diperiksa, silakan coba lagi',
        'success' => 'Aksesori berhasil diperiksa.',
        'user_does_not_exist' => 'Pengguna yang tidak valid. Silakan coba lagi.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
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
