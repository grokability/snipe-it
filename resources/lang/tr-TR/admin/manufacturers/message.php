<?php

return array(

    'support_url_help' => '<code>{LOCALE</code>, <code>{SERIAL</code>, <code>{MODEL_NUMBER</code> ve <code>{MODEL_NAME</code> değişkenleri, cihazınızda kullanılabilir. Varlıkları görüntülerken bu değerlerin otomatik olarak doldurulmasını sağlayan URL (örneğin, https://checkcoverage.apple.com/{LOCALE}/{SERIAL}).',
    'does_not_exist' => 'Üretici mevcut değil.',
    'assoc_users'	 => 'Üretici en az 1 model ile ilişkili durumda ve silinemez. Lütfen önce modelleri güncelleyerek üreticiyi boşa çıkarın ve tekrar deneyin. ',

    'create' => array(
        'error'   => 'Üretici oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Üretici oluşturuldu.'
    ),

    'update' => array(
        'error'   => 'Üretici güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Üretici güncellendi.'
    ),

    'restore' => array(
        'error'   => 'Üretici geri yüklenmedi, lütfen tekrar deneyin',
        'success' => 'Üretici başarıyla geri yüklendi.'
    ),

    'delete' => array(
        'confirm' => 'Üreticiyi silmek istediğinize emin misiniz?',
        'error'   => 'Üretici silinirken bir hata oluştu. Lütfen tekrar deneyin.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
