<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Produsen tidak ada.',
    'assoc_users'	 => 'Produsen ini saat ini terkait dengan setidaknya satu model dan tidak dapat dihapus. Perbarui model Anda agar tidak lagi mereferensikan produsen ini dan coba lagi. ',

    'create' => array(
        'error'   => 'Produsen tidak dibuat, silahkan dicoba lagi.',
        'success' => 'Produsen berhasil dibuat.'
    ),

    'update' => array(
        'error'   => 'Produsen tidak diperbarui, silahkan coba lagi',
        'success' => 'Produsen berhasil diperbarui.'
    ),

    'restore' => array(
        'error'   => 'Pabrikan tidak dapat dipulihkan, silakan coba kembali',
        'success' => 'Pabrikan berhasil dipulihkan.'
    ),

    'delete' => array(
        'confirm' => 'Anda yakin ingin menghapus produsen ini?',
        'error'   => 'Terjadi masalah saat menghapus produsen. Silahkan coba lagi.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
