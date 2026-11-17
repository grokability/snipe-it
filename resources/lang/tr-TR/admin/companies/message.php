<?php

return [
    'does_not_exist' => 'Firma yok.',
    'deleted' => 'Firmayı Sil',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Firma oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Firma başarıyla oluşturuldu.',
    ],
    'update' => [
        'error' => 'Firma güncelleştirilmedi, lütfen tekrar deneyin',
        'success' => 'Firma Başarıyla Güncellendi.',
    ],
    'delete' => [
        'confirm' => 'Bu firmayı silmek istediğinize emin misiniz?',
        'error' => 'Firmayı silerken bir sorun oluştu. Lütfen yeniden deneyin.',
        'success' => 'Firma başarıyla silindi.',
        'bulk_success' => 'Şirket başarıyla silindi.|:count şirket başarıyla silindi.',
        'partial_success' => 'Şirket başarıyla silindi. Aşağıda ek bilgiler verilmiştir.|:count şirket başarıyla silindi. Aşağıda ek bilgiler verilmiştir.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
