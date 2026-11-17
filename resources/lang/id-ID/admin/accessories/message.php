<?php

return [

    'does_not_exist' => 'Aksesori [:id] tidak ada.',
    'not_found' => 'Aksesori tersebut tidak ditemukan.',
    'assoc_users' => 'Aksesori ini saat ini memiliki: count item yang dikeluarkan ke pengguna. Silakan periksa di aksesoris dan dan coba lagi. . Silakan periksa di aksesoris dan dan coba lagi. ',

    'create' => [
        'error' => 'Aksesori gagal di buat, mohon ulangi kembali.',
        'success' => 'Aksesori sukses di buat.',
    ],

    'update' => [
        'error' => 'Aksesori gagal terbaharui, mohon ulangi kembali',
        'success' => 'Aksesori sukses terbaharui.',
    ],

    'delete' => [
        'confirm' => 'Apakah anda yakin menghapus aksesori ini?',
        'error' => 'Terdapat kesalahan pada saat penghapusan aksesori ini. Silahkan coba kembali.',
        'success' => 'Aksesori sukses terhapus.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Aksesori ini belum dikeluarkan, silahkan coba kembali',
        'success' => 'Aksesori telah berhasil dikeluarkan.',
        'unavailable' => 'Aksesori tidak tersedia untuk dipinjam atau diambil. Periksa jumlah yang tersedia',
        'user_does_not_exist' => 'Terdapat kesalahan pada user ini. Silahkan coba kembali.',
        'checkout_qty' => [
            'lte' => 'Saat ini hanya ada satu aksesori jenis ini yang tersedia, dan Anda mencoba meminjam atau mengambil :checkout_qty. Harap sesuaikan jumlah pinjaman-pengambilan atau total stok aksesori ini dan coba lagi.|Terdapat :number_currently_remaining total aksesori yang tersedia, dan Anda mencoba meminjam atau mengambil :checkout_qty. Harap sesuaikan jumlah pinjaman-pengambilan atau total stok aksesori ini dan coba lagi.',
        ],

    ],

    'checkin' => [
        'error' => 'Aksesoris belum masuk, silahkan coba kembali',
        'success' => 'Aksesoris telah berhasil dimasukkan.',
        'user_does_not_exist' => 'Terdapat kesalahan pada user ini. Silahkan coba kembali.',
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
