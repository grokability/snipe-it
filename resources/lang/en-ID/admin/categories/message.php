<?php

return array(

    'does_not_exist' => 'Kategori tidak ada.',
    'assoc_models'	 => 'Kategori ini saat ini terkait dengan setidaknya satu model dan tidak dapat dihapus. Silahkan update model Anda untuk tidak lagi tereferensi dengan kategori ini dan coba lagi. ',
    'assoc_items'	 => 'Kategori ini saat ini terkait dengan setidaknya satu: jenis aset dan tidak dapat dihapus. Silahkan perbarui : Asset_TYPE untuk tidak lagi referensi kategori ini dan coba lagi. ',

    'create' => array(
        'error'   => 'Kategori gagal dibuat, silahkan coba lagi.',
        'success' => 'Kategori Berhasil dibuat.'
    ),

    'update' => array(
        'error'   => 'Kategori gagal diupdate, silahkan coba lagi',
        'success' => 'Kategori berhasil diupdate.',
        'cannot_change_category_type'   => 'Anda tidak bisa mengubah tipe kategori jika sudah dibuat',
    ),

    'delete' => array(
        'confirm'                => 'Apakah Anda yakin ingin menghapus kategori ini?',
        'error'                  => 'Ada masalah saat menghapus kategori. Silakan coba lagi.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
