<?php

return [

    'does_not_exist' => 'Bileşen mevcut değil.',

    'create' => [
        'error' => 'Bileşen oluşturulmadı, lütfen yeniden deneyin.',
        'success' => 'Bileşeni başarıyla oluşturuldu.',
    ],

    'update' => [
        'error' => 'Bileşen güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Bileşen başarıyla güncelleştirildi.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Bu bileşeni silmek istediğinizden emin misiniz?',
        'error' => 'Bileşen silinirken sorun oluştu. Lütfen yeniden deneyin.',
        'success' => 'Bileşen başarıyla silindi.',
        'error_qty' => 'Bu türden bazı bileşenler hala alınmış. Lütfen bunları teslim edin ve tekrar deneyin.',
    ],

    'checkout' => [
        'error' => 'Bileşeni çıkışı yapılamadı, lütfen tekrar deneyin',
        'success' => 'Bileşen çıkışı başarıyla yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'unavailable' => 'Yeterli bileşen kalmadı: : miktar miktar, :talep edilen talep edilen',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Hiçbir alan değiştirilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Bileşen girişi yapılamadı, lütfen tekrar deneyin',
        'success' => 'Bileşen girişi başarıyla yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
    ],

];
