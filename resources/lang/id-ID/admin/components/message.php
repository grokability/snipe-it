<?php

return [

    'does_not_exist' => 'Tidak ada komponen.',

    'create' => [
        'error' => 'Komponen belum terbuat, silahkan coba kembali.',
        'success' => 'Komponen telah berhasil di buat.',
    ],

    'update' => [
        'error' => 'Komponen belum terperbarui, silahkan coba kembali',
        'success' => 'Komponen telah berhasil di perbarui.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Apakah Anda yakin untuk menghapus komponen ini?',
        'error' => 'Terdapat kesalahan pada saat penghapusan komponen ini. Silahkan coba kembali.',
        'success' => 'Komponen telah berhasil dihapus.',
        'error_qty' => 'Beberapa komponen jenis ini masih diperiksa. Silakan periksa dan coba lagi.',
    ],

    'checkout' => [
        'error' => 'Komponen belum tertanda keluar, silahkan mencoba lagi',
        'success' => 'Komponen telah sukses tertanda keluar.',
        'user_does_not_exist' => 'Pengguna tidak terdaftar. Silahkan coba kembali.',
        'unavailable' => 'Tidak ada sisa komponen: :sisa sisa, :diminta diminta ',
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
        'error' => 'Komponen belum tertanda masuk, silahkan mencoba lagi',
        'success' => 'Komponen telah sukses tertanda masuk.',
        'user_does_not_exist' => 'Pengguna tidak terdaftar. Silahkan coba kembali.',
    ],

];
