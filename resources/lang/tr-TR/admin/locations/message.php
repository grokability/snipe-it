<?php

return array(

    'does_not_exist' => 'Konum mevcut değil.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Bu konum şu anda en az bir varlık ile ilişkili ve silinemez. Lütfen artık bu konumu kullanabilmek için varlık konumlarını güncelleştirin.',
    'assoc_child_loc'	 => 'Bu konum şu anda en az bir alt konum üstüdür ve silinemez. Lütfen artık bu konuma ait alt konumları güncelleyin. ',
    'assigned_assets' => 'Atanan Varlıklar',
    'current_location' => 'Mevcut konum',
    'open_map' => ':map_provider_icon Haritalar\'da açın',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Konum oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Konum oluşturuldu.'
    ),

    'update' => array(
        'error'   => 'Konum güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Konum güncellendi.'
    ),

    'restore' => array(
        'error'   => 'Konum geri yüklenemedi, lütfen tekrar deneyin',
        'success' => 'Konum başarıyla geri yüklendi.'
    ),

    'delete' => array(
        'confirm'   	=> 'Konumu silmek istediğinize emin misiniz?',
        'error'   => 'Konum silinirken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Konum silindi.'
    )

);
