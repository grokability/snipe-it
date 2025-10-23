<?php

return array(

    'support_url_help' => 'Variabel <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, dan <code>{MODEL_NAME}</code> dapat digunakan dalam URL Anda untuk secara otomatis mengisi nilai-nilai tersebut saat melihat aset - contohnya https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Produsen tidak ada.',
    'assoc_users'	 => 'Produsen ini saat ini dikaitkan dengan setidaknya satu model dan tidak dapat dihapus. Perbarui model Anda yang tidak ada referensi dari produsen ini dan coba lagi. ',

    'create' => array(
        'error'   => 'Produsen gagal di buat, silahkan coba kembali.',
        'success' => 'Produsen sukses di buat.'
    ),

    'update' => array(
        'error'   => 'Produsen gagal di perbarui, silahkan coba kembali',
        'success' => 'Produsen sukses di perbarui.'
    ),

    'restore' => array(
        'error'   => 'Pabrikan tidak dapat dipulihkan, silakan coba kembali',
        'success' => 'Pabrikan berhasil dipulihkan.'
    ),

    'delete' => array(
        'confirm' => 'Apakah Anda yakin untuk menghapus produsen ini?',
        'error'   => 'Terdapat kesalahan pada saat penghapusan produsen. Silahkan coba kembali.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
