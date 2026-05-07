<?php

return [

    'does_not_exist' => 'Konum mevcut değil.',
    'assoc_users' => 'Bu konum şu anda silinemez; çünkü en az bir varlık veya kullanıcı için kayıtlı konumdur, üzerine atanmış varlıklar bulunmaktadır veya başka bir konumun üst konumudur (parent location). Lütfen kayıtlarınızı bu konuma referans vermeyecek şekilde güncelleyin ve tekrar deneyin ',
    'assoc_assets' => 'Bu konum şu anda en az bir varlık ile ilişkili ve silinemez. Lütfen artık bu konumu kullanabilmek için varlık konumlarını güncelleştirin.',
    'assoc_child_loc' => 'Bu konum şu anda en az bir alt konum üstüdür ve silinemez. Lütfen artık bu konuma ait alt konumları güncelleyin. ',
    'assigned_assets' => 'Atanan Varlıklar',
    'current_location' => 'Mevcut konum',
    'deleted_warning' => 'Bu konum silindi. Lütfen herhangi bir değişiklik yapmadan önce konumu geri yükleyin.',

    'create' => [
        'error' => 'Konum oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Konum oluşturuldu.',
    ],

    'update' => [
        'error' => 'Konum güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Konum güncellendi.',
    ],

    'restore' => [
        'error' => 'Konum geri yüklenemedi, lütfen tekrar deneyin',
        'success' => 'Konum başarıyla geri yüklendi.',
    ],

    'delete' => [
        'confirm' => 'Konumu silmek istediğinize emin misiniz?',
        'error' => 'Konum silinirken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Konum silindi.',
    ],

];
