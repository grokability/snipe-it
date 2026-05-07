<?php

return [

    'does_not_exist' => '[:id] numaralı aksesuar bulunmuyor.',
    'not_found' => 'O aksesuar bulunamadı.',
    'assoc_users' => 'Bu aksesuardan :count adet kullanıcılara çıkış yapıldı. Lütfen aksesuar girişi yapınız ve tekrar deneyin.',

    'create' => [
        'error' => 'Aksesuar oluşturma başarısız. lütfen tekrar deneyin.',
        'success' => 'Aksesuar başarıyla güncellendi.',
    ],

    'update' => [
        'error' => 'Aksesuar güncellenmedi. Lütfen tekrar deneyin',
        'success' => 'Aksesuar başarı ile güncellendi.',
    ],

    'delete' => [
        'confirm' => 'Bu Aksesuar silmek istediğinizden emin misiniz?',
        'error' => 'Aksesuarı silerken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Aksesuar başarıyla silindi.',
    ],

    'checkout' => [
        'error' => 'Aksesuar çıkışı yapılamadı, lütfen tekrar deneyin',
        'success' => 'Aksesuar çıkışı yapıldı.',
        'unavailable' => 'Bu ürün zimmetlenemez. Ürün sayısını kontrol edin.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'checkout_qty' => [
            'lte' => 'Şu anda bu türden yalnızca bir adet aksesuar mevcut ve siz :checkout_qty adet almaya çalışıyorsunuz. Lütfen alma miktarını veya bu aksesuarın toplam stokunu ayarlayıp tekrar deneyin.

Toplamda :number_currently_remaining adet mevcut aksesuar var ve siz :checkout_qty adet almaya çalışıyorsunuz. Lütfen alma miktarını veya bu aksesuarın toplam stokunu ayarlayıp tekrar deneyin.',
        ],

    ],

    'checkin' => [
        'error' => 'Aksesuar girişi yapılamadı, lütfen tekrar deneyin',
        'success' => 'Aksesuar girişi yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
    ],

];
