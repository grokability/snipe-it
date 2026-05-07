<?php

return [

    'support_url_help' => '<code>{LOCALE</code>, <code>{SERIAL</code>, <code>{MODEL_NUMBER</code> ve <code>{MODEL_NAME</code> değişkenleri, cihazınızda kullanılabilir. Varlıkları görüntülerken bu değerlerin otomatik olarak doldurulmasını sağlayan URL (örneğin, https://checkcoverage.apple.com/{LOCALE}/{SERIAL}).',
    'does_not_exist' => 'Üretici mevcut değil.',
    'assoc_users' => 'Üretici en az 1 model ile ilişkili durumda ve silinemez. Lütfen önce modelleri güncelleyerek üreticiyi boşa çıkarın ve tekrar deneyin. ',

    'create' => [
        'error' => 'Üretici oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Üretici oluşturuldu.',
    ],

    'update' => [
        'error' => 'Üretici güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Üretici güncellendi.',
    ],

    'restore' => [
        'error' => 'Üretici geri yüklenmedi, lütfen tekrar deneyin',
        'success' => 'Üretici başarıyla geri yüklendi.',
    ],

    'delete' => [
        'confirm' => 'Üreticiyi silmek istediğinize emin misiniz?',
        'error' => 'Üretici silinirken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Üretici başarıyla silindi.',
        'bulk_success' => 'Üreticiler başarıyla silindi.',
        'partial_success' => 'Üretici başarıyla silindi. Aşağıda ek bilgileri görebilirsiniz. | :count üretici başarıyla silindi. Aşağıda ek bilgileri görebilirsiniz.',
    ],

];
