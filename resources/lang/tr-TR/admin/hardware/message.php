<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Demirbaş mevcut değil.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Bu varlık mevcut değil veya talep edilebilir değil.',
    'assoc_users'	 	 => 'Bu demirbaş kullanıcıya çıkış yapılmış olaran görülüyor ve silinemez. Lütfen önce demirbaş girişi yapınız, ardından tekrar siliniz. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Demirbaş oluşturulamadı, lütfen tekrar deneyin. ',
        'success' 		=> 'Demirbaş oluşturuldu.',
        'success_linked' => 'Etiketli ürün :etiket oluşturuldu. <strong><a href=":link" style="color: white;">Görmek için tıklayın.</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Demirbaş güncellenemedi, lütfen tekrar deneyin',
        'success' 			=> 'Demirbaş güncellendi.',
        'encrypted_warning' => 'Varlık başarıyla güncellendi, ancak şifreli özel alanlar izin nedeniyle güncellenemedi',
        'nothing_updated'	=>  'Hiçbir alan seçilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'no_assets_selected'  =>  'Hiçbir varlık seçilmedi, bu nedenle hiçbir şey güncellenmedi.',
        'assets_do_not_exist_or_are_invalid' => '',
    ],

    'restore' => [
        'error'   		=> 'Demirbaş geri getirilemedi, lütfen tekrar deneyin',
        'success' 		=> 'Demirbaş geri getirildi.',
        'bulk_success' 		=> 'Varlık başarı ile geri yüklendi.',
        'nothing_updated'   => 'Herhangi bir varlık seçili olmadığı için hiçbirşey geri yüklenmedi.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Varlık denetimi başarıyla günlüğe kaydedildi.',
    ],


    'deletefile' => [
        'error'   => 'Dosya silinemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya silindi.',
    ],

    'upload' => [
        'error'   => 'Dosya(lar) yüklenemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya(lar) yüklendi.',
        'nofiles' => 'Yükleme için herhangi bir dosya seçmediniz veya karşıya yüklemeye çalıştığınız dosya çok büyük',
        'invalidfiles' => 'Bir ya da daha fazla dosya izin verilen boyuttan daha büyük ya da izin verilmeyen bir dosya tipi seçtiniz. Lütfen dosya boyutu ve tipini kontrol ediniz.',
    ],

    'import' => [
        'import_button'         => 'İçeri aktarma işlemi',
        'error'                 => 'Bazı öğeler doğru şekilde içe aktarılamadı.',
        'errorDetail'           => 'Aşağıdaki öğeler hatalar nedeniyle alınamadı.',
        'success'               => 'Dosyanızı içe aktarıldı',
        'file_delete_success'   => 'Dosyanız başarıyla silindi',
        'file_delete_error'      => 'Dosya silenemedi',
        'file_missing' => 'Seçilen dosya bulunamıyor',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Başlık bilgisindeki bir veya daha fazla öznitelik, hatalı UTF-8 karakterleri içeriyor',
        'content_row_has_malformed_characters' => 'Başlıktaki ilk satırda bir veya daha fazla öznitelik, hatalı biçimlendirilmiş UTF-8 karakterleri içeriyor',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Demirbaşı silmek istediğinize emin misiniz?',
        'error'   		=> 'Demirbaş silinirken bir problem oluştu. Lütfen tekrar deneyin.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Herhangi bir varlık seçilmediği için silinemedi.',
        'success' 		=> 'Demirbaş silindi.',
    ],

    'checkout' => [
        'error'   		=> 'Demirbaş çıkışı yapılamadı. Lütfen tekrar deneyin',
        'success' 		=> 'Demirbaş çıkışı yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'not_available' => 'Bu varlık için atama yapılamaz!',
        'no_assets_selected' => 'Listeden en az bir varlık seçmelisiniz',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Demirbaş girişi yapılamadı. Lütfen tekrar deneyin',
        'success' 		=> 'Demirbaş girişi yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'already_checked_in'  => 'Bu varlık zaten atanmış.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'İsteği iptal et',
    ],

];
