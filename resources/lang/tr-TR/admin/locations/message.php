<?php

return array(

    'does_not_exist' => 'Konum mevcut değil.',
    'assoc_users'    => 'Bu konum şu anda silinemez çünkü en az bir varlık veya kullanıcı için kayıt konumudur, kendisine atanmış varlıkları vardır veya başka bir konumun üst konumudur. Lütfen kayıtlarınızı artık bu konuma referans vermeyecek şekilde güncelleyin ve tekrar deneyin.',
    'assoc_assets'	 => 'Bu konum şu anda en az bir varlık ile ilişkili ve silinemez. Lütfen artık bu konumu kullanabilmek için varlık konumlarını güncelleştirin.',
    'assoc_child_loc'	 => 'Bu konum şu anda en az bir alt konum üstüdür ve silinemez. Lütfen artık bu konuma ait alt konumları güncelleyin. ',
    'assigned_assets' => 'Atanan Varlıklar',
    'current_location' => 'Mevcut konum',
    'open_map' => ':map_provider_icon Haritalar\'da açın',


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
