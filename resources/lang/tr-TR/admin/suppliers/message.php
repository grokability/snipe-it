<?php

return array(

    'deleted' => 'Silinen tedarikçi',
    'does_not_exist' => 'Tedarikçi mevcut değil.',


    'create' => array(
        'error'   => 'Tedarikçi oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Tedarikçi oluşturuldu.'
    ),

    'update' => array(
        'error'   => 'Tedarikçi güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Tedarikçi güncellendi.'
    ),

    'delete' => array(
        'confirm'   => 'Tedarikçiyi silmek istediğinize emin misiniz?',
        'error'   => 'Tedarikçi silinirken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Tedarikçi silindi.',
        'assoc_assets'	 => 'Bu tedarikçi halihazırda :asset_count asset(s) ilişkili durumda ve silinemez. Lütfen varlıklarınızı bu tedarikçi ile ilişkisi olmayacak şekilde güncelleyin ve yeniden deneyin. ',
        'assoc_licenses'	 => 'Bu tedarikçi halihazırda :licenses_count licences(s) ilişkili durumda ve silinemez. Lütfen lisanslarınızı bu tedarikçi ile ilişkisi olmayacak şekilde güncelleyin ve yeniden deneyin. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
