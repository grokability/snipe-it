<?php

return [

    'invalid_category_type' => 'Kategori, tüketilebilir bir kategori olmalıdır.',
    'does_not_exist' => 'Sarf malzemesi mevcut değil.',

    'create' => [
        'error' => 'Sarf malzemesi oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Sarf malzemesi oluşturuldu.',
    ],

    'update' => [
        'error' => 'Sarf malzemesi oluşturulamadı, lütfen tekrar deneyin',
        'success' => 'Sarf malzemesi güncellendi.',
    ],

    'delete' => [
        'confirm' => 'Bu sarf malzemesini silmek istediğinizden emin misiniz?',
        'error' => 'Sarf malzemesi\'ni silerken hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Sarf malzemesi başarıyla silindi.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Sarf malzemesi çıkışı yapılamadı, lütfen tekrar deneyin',
        'success' => 'Sarf malzemesi çıkışı yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'unavailable' => 'Bu zimmetleme işlemi için yeterli sarf malzeme kalmamış. Lütfen stok miktarını kontrol edin.',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Sarf malzemesi girişi yapılamadı, lütfen tekrar deneyin',
        'success' => 'Sarf malzemesi girişi yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Hiçbir alan değiştirilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
