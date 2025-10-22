<?php

return array(

    'does_not_exist' => 'Kategori tidak ada.',
    'assoc_models'	 => 'Kategori ini saat ini dikaitkan dengan setidaknya satu model dan tidak dapat dihapus. Perbarui model Anda yang tidak ada referensi dari kategori ini dan coba lagi. ',
    'assoc_items'	 => 'Kategori ini saat ini dikaitkan dengan setidaknya satu model dan tidak dapat dihapus. Perbarui model Anda yang tidak ada referensi dari kategori ini dan coba lagi. ',

    'create' => array(
        'error'   => 'Kategori belum terbuat, silahkan coba kembali.',
        'success' => 'Kategori telah berhasil dibuat.'
    ),

    'update' => array(
        'error'   => 'Pembaharuan kategori tidak berhasil, silahkan coba kembali',
        'success' => 'Pembaharuan kategori berhasil.',
        'cannot_change_category_type'   => 'Anda tidak dapat mengubah jenis kategori setelah dibuat',
    ),

    'delete' => array(
        'confirm'                => 'Apakah Anda yakin untuk menghapus kategori ini?',
        'error'                  => 'Terdapat kesalahan pada saat penghapusan kategori ini. Silahkan coba kembali.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
