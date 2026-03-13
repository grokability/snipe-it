<?php

return array(

    'does_not_exist' => 'Kategori mevcut değil.',
    'assoc_models'	 => 'Bu kategori en az 1 adet model ile ilişkili ve silinemez. Lütfen Modelleri güncelleyerek bu kategori ile bağını kesin ve tekrar deneyin. ',
    'assoc_items'	 => 'Bu kategori en az 1 adet model ile ilişkili ve silinemez. Lütfen Modelleri güncelleyerek bu kategori ile bağını kesin ve tekrar deneyin. ',

    'create' => array(
        'error'   => 'Kategori oluşturulamadı. Lütfen tekrar deneyin.',
        'success' => 'Kategori oluşturuldu.'
    ),

    'update' => array(
        'error'   => 'Kategori güncellenemedi, Lütfen tekrar deneyin',
        'success' => 'Kategori güncellendi.',
        'cannot_change_category_type'   => 'Kategori tipini oluşturduktan sonra üzerinde değişiklik yapamazsınız',
    ),

    'delete' => array(
        'confirm'                => 'Bu kategoriyi silmek istediğinize emin misiniz?',
        'error'                  => 'Bu kategoriyi silerken bir hata ile karşılaşıldı. Lütfen tekrar deneyin.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
