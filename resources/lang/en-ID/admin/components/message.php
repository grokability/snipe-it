<?php

return [

    'does_not_exist' => 'Komponen tidak ada.',

    'create' => [
        'error' => 'Komponen belum dibuat, silahkan coba lagi.',
        'success' => 'Komponen berhasil dibuat.',
    ],

    'update' => [
        'error' => 'Komponen gagal diubah, silahkan coba lagi',
        'success' => 'Komponen berhasil diubah.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Anda yakin akan mengahpus komponen ini?',
        'error' => 'Ada masalah untuk hapus komponen, silahkan coba kembali.',
        'success' => 'Komponen berhasil dihapus.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponen tidak bisa dikeluarkan, silahkan coba kembali',
        'success' => 'Komponen berhasil dikeluarkan.',
        'user_does_not_exist' => 'Pengguna tidak cocok. Silahkan coba lagi.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
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
        'error' => 'Komponen tidak bisa dimasukan, silahkan coba lagi',
        'success' => 'Komponen berhasil dimasukan.',
        'user_does_not_exist' => 'Pengguna tidak cocok. Silahkan coba lagi.',
    ],

];
