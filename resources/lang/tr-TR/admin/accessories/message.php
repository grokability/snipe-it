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
        'bulk_success' => 'Aksesuar başarıyla silindi.|:count aksesuar başarıyla silindi.',
        'partial_success' => ':count aksesuar başarıyla silindi ancak diğer aksesuarlar silinemedi. Ayrıntılar için aşağıya bakın.|:count aksesuar başarıyla silindi ancak diğer aksesuarlar silinemedi. Ayrıntılar için aşağıya bakın.',
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

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Hiçbir alan değiştirilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
