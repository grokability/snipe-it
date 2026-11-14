<?php

return [

    'invalid_category_type' => 'Kategori harus merupakan kategori barang habis pakai.',
    'does_not_exist' => 'Barang Habis Pakai tidak terdaftar.',

    'create' => [
        'error' => 'Barang Habis Pakai tidak berhasil dibuat, silahkan mencoba lagi.',
        'success' => 'Barang Habis Pakai telah berhasil dibuat.',
    ],

    'update' => [
        'error' => 'Barang Habis Pakai tidak berhasil diperbarui, silahkan mencoba lagi',
        'success' => 'Barang Habis Pakai telah berhasil diperbarui.',
    ],

    'delete' => [
        'confirm' => 'Apakah Anda yakin untuk menghapus Barang Habis Pakai ini?',
        'error' => 'Terdapat kesalahan pada saat penghapusan Barang Habis Pakai ini. Silahkan coba kembali.',
        'success' => 'Barang Habis Pakai telah berhasil dihapus.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Barang Habis Pakai belum tertanda keluar, silahkan mencoba lagi',
        'success' => 'Barang Habis Pakai telah sukses tertanda keluar.',
        'user_does_not_exist' => 'Pengguna tidak terdaftar. Silahkan coba kembali.',
        'unavailable' => 'Tidak cukup barang habis pakai untuk checkout ini. Harap periksa jumlah yang tersisa. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Barang Habis Pakai belum tertanda masuk, silahkan mencoba lagi',
        'success' => 'Barang Habis Pakai telah sukses tertanda masuk.',
        'user_does_not_exist' => 'Pengguna tidak terdaftar. Silahkan coba kembali.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
