<?php

return array(

    'deleted' => 'Silinen varlık modeli',
    'does_not_exist' => 'Model mevcut değil.',
    'no_association' => 'UYARI! Bu öğeye ilişkin varlık modeli geçersiz veya eksik!',
    'no_association_fix' => 'Bu değişiklik bazı şeylerin garip ve tuhaf bir şekilde bozulmasına yol açabilir. Bu varlığı bir modelle ilişkilendirmek için düzeltin.',
    'assoc_users'	 => 'Model bir ya da daha çok demirbaş ile ilişkili ve silinemez. Lütfen demirbaşları silin ve tekrar deneyin. ',
    'invalid_category_type' => 'Kategori, bir varlık kategorisi olmak zorunda.',

    'create' => array(
        'error'   => 'Klasör oluşturulmadı, lütfen tekrar deneyin.',
        'success' => 'Model oluşturuldu.',
        'duplicate_set' => 'Bu üretici ve model numarası ile bir varlık ve model zaten var.',
    ),

    'update' => array(
        'error'   => 'Model güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Model güncellendi.',
    ),

    'delete' => array(
        'confirm'   => 'Bu demirbaş modelini silmek istediğinize emin misiniz?',
        'error'   => 'Demirbaş silinirken bir problem oluştu. Lütfen tekrar deneyin.',
        'success' => 'Model silindi.'
    ),

    'restore' => array(
        'error'   		=> 'Model geri getirilemedi, lütfen tekrar deneyin',
        'success' 		=> 'Model geri getirildi.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Hiçbir alan değiştirilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'success' 		=> 'Model başarıyla güncellendi. |:model_count modelleri başarıyla güncellendi.',
        'warn'          => 'Aşağıdaki modelin özelliklerini güncellemek üzeresiniz: |Aşağıdaki :model_count modellerinin özelliklerini düzenlemek üzeresiniz:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Hiçbir model seçilmedi, bu nedenle hiçbir şey silinmedi.',
        'success' 		    => 'Model silindi!|:success_count modelleri silindi!',
        'success_partial' 	=> ':success_count adet model(ler) silindi, ancak :fail_count adet için silme işlemini tamamlayamadık, çünkü bunlar halâ varlıklarla ilişkilendirilmiş durumda.'
    ),

);
