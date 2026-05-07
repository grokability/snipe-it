<?php

return [

    'does_not_exist' => 'Kategori mevcut değil.',
    'assoc_models' => 'Bu kategori en az 1 adet model ile ilişkili ve silinemez. Lütfen Modelleri güncelleyerek bu kategori ile bağını kesin ve tekrar deneyin. ',
    'assoc_items' => 'Bu kategori en az 1 adet model ile ilişkili ve silinemez. Lütfen Modelleri güncelleyerek bu kategori ile bağını kesin ve tekrar deneyin. ',

    'create' => [
        'error' => 'Kategori oluşturulamadı. Lütfen tekrar deneyin.',
        'success' => 'Kategori oluşturuldu.',
    ],

    'update' => [
        'error' => 'Kategori güncellenemedi, Lütfen tekrar deneyin',
        'success' => 'Kategori güncellendi.',
        'cannot_change_category_type' => 'Kategori tipini oluşturduktan sonra üzerinde değişiklik yapamazsınız',
    ],

    'delete' => [
        'confirm' => 'Bu kategoriyi silmek istediğinize emin misiniz?',
        'error' => 'Bu kategoriyi silerken bir hata ile karşılaşıldı. Lütfen tekrar deneyin.',
        'success' => 'Kategori başarıyla silindi.',
        'bulk_success' => 'Kategoriler başarıyla silindi.',
        'partial_success' => 'Kategori başarıyla silindi. Aşağıda ek bilgileri görebilirsiniz. | :count kategori başarıyla silindi. Aşağıda ek bilgileri görebilirsiniz.',
    ],

];
