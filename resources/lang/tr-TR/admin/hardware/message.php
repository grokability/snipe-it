<?php

return [

    'undeployable' 		 => 'Aşağıdaki varlıklar dağıtılamadığı için teslimattan çıkarıldı: :asset_tags.',
    'does_not_exist' 	 => 'Demirbaş mevcut değil.',
    'does_not_exist_var' => ':asset_tag etiketine sahip varlık bulunamadı.',
    'no_tag' 	         => 'Varlık etiketi sağlanmadı.',
    'does_not_exist_or_not_requestable' => 'Bu varlık mevcut değil veya talep edilebilir değil.',
    'assoc_users'	 	 => 'Bu demirbaş kullanıcıya çıkış yapılmış olaran görülüyor ve silinemez. Lütfen önce demirbaş girişi yapınız, ardından tekrar siliniz. ',
    'warning_audit_date_mismatch' 	=> 'Bu varlığın bir sonraki denetim tarihi (:next_audit_date) son denetim tarihinden (:last_audit_date) önce. Lütfen bir sonraki denetim tarihini güncelleyin.',
    'labels_generated'   => 'Etiketler başarıyla oluşturuldu.',
    'error_generating_labels' => 'Etiket oluşturulurken hata oluştu.',
    'no_assets_selected' => 'Seçilmiş varlık yok.',

    'create' => [
        'error'   		=> 'Demirbaş oluşturulamadı, lütfen tekrar deneyin. ',
        'success' 		=> 'Demirbaş oluşturuldu.',
        'success_linked' => 'Etiketli ürün :etiket oluşturuldu. <strong><a href=":link" style="color: white;">Görmek için tıklayın.</a></strong>.',
        'multi_success_linked' => 'Varlık :links etiketi ile başarıyla oluşturuldu.|:count varlıkları başarıyla oluşturuldu. :links.',
        'partial_failure' => 'Bir varlık oluşturulamadı. Sebep: :failures|:count varlıkları oluşturulamadı. Nedenler: :failures',
        'target_not_found' => [
            'user' => 'Atanan kullanıcı bulunamadı.',
            'asset' => 'Atanan varlık bulunamadı.',
            'location' => 'Atanan konum bulunamadı.',
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
        'error'   		=> 'Varlık denetimi başarısız: :error',
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
        'file_already_deleted' => 'Seçilen dosya zaten silinmiş',
        'header_row_has_malformed_characters' => 'Başlık bilgisindeki bir veya daha fazla öznitelik, hatalı UTF-8 karakterleri içeriyor',
        'content_row_has_malformed_characters' => 'Başlıktaki ilk satırda bir veya daha fazla öznitelik, hatalı biçimlendirilmiş UTF-8 karakterleri içeriyor',
        'transliterate_failure' => ':encoding kodlamasından UTF-8\'e dönüştürme, girişteki geçersiz karakterler nedeniyle başarısız oldu.'
    ],


    'delete' => [
        'confirm'   	=> 'Demirbaşı silmek istediğinize emin misiniz?',
        'error'   		=> 'Demirbaş silinirken bir problem oluştu. Lütfen tekrar deneyin.',
        'assigned_to_error' => '{1}Varlık Etiketi: :asset_tag şu anda teslim alınmış durumda. Silmeden önce bu cihazı teslim edin.|[2,*]Varlık Etiketleri: :asset_tag şu anda teslim alınmış durumda. Silmeden önce bu cihazları teslim edin.',
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
        'error'   => 'Varlık teslim edilemedi, lütfen tekrar deneyin|Varlıklar teslim edilemedi, lütfen tekrar deneyin',
        'success' => 'Varlık başarıyla çıkış yapıldı.|Varlıklar başarıyla çıkış yaptı.',
    ],

    'checkin' => [
        'error'   		=> 'Demirbaş girişi yapılamadı. Lütfen tekrar deneyin',
        'success' 		=> 'Demirbaş girişi yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'already_checked_in'  => 'Bu varlık zaten atanmış.',

    ],

    'requests' => [
        'error'   		=> 'Talep başarılı olamadı, lütfen tekrar deneyin',
        'success' 		=> 'Talep başarıyla gönderildi.',
        'canceled'      => 'Talep başarıyla iptal edildi.',
        'cancel'        => 'İsteği iptal et',
    ],

];
