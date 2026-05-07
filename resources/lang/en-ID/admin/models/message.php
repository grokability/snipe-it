<?php

return [

    'deleted' => 'Hapus model asset',
    'does_not_exist' => 'Model tidak ada.',
    'no_association' => '!',
    'no_association_fix' => '.',
    'assoc_users' => 'Model ini saat ini dikaitkan dengan satu atau lebih aset dan tidak dapat dihapus. Harap hapus asetnya, lalu coba hapus lagi. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => [
        'error' => 'Model tidak dibuat, silahkan dicoba lagi.',
        'success' => 'Model berhasil dibuat.',
        'duplicate_set' => 'Model aset dengan nama, nama produsen dan nomor model yang sudah ada.',
    ],

    'update' => [
        'error' => 'Model tidak diperbarui, silahkan dicoba lagi',
        'success' => 'Model berhasil diperbarui.',
    ],

    'delete' => [
        'confirm' => 'Yakin ingin menghapus model aset ini?',
        'error' => 'Terjadi masalah saat menghapus model. Silahkan coba lagi.',
        'success' => 'Model berhasil dihapus.',
    ],

    'restore' => [
        'error' => 'Aset tidak dikembalikan, coba lagi',
        'success' => 'Model berhasil dikembalikan.',
    ],

    'bulkedit' => [
        'error' => 'Tidak ada bidang yang berubah, jadi tidak ada yang diperbarui.',
        'success' => 'Model successfully updated. |:model_count models successfully updated.',
        'warn' => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ],

    'bulkdelete' => [
        'error' => 'Tidak ada model yang dipilih, jadi tidak ada yang dihapus.',
        'success' => 'Model deleted!|:success_count models deleted!',
        'success_partial' => ':success_count model(s) telah dihapus, namun: fail_count tidak dapat dihapus karena mereka masih memiliki aset yang terkait dengannya.',
    ],

];
