<?php

return [

    'undeployable' => 'Aşağıdaki varlıklar dağıtılamadığı için teslimattan çıkarıldı: :asset_tags.',
    'does_not_exist' => 'Demirbaş mevcut değil.',
    'does_not_exist_var' => ':asset_tag etiketine sahip varlık bulunamadı.',
    'no_tag' => 'Varlık etiketi sağlanmadı.',
    'does_not_exist_or_not_requestable' => 'Bu varlık mevcut değil veya talep edilebilir değil.',
    'assoc_users' => 'Bu demirbaş kullanıcıya çıkış yapılmış olaran görülüyor ve silinemez. Lütfen önce demirbaş girişi yapınız, ardından tekrar siliniz. ',
    'warning_audit_date_mismatch' => 'Bu varlığın bir sonraki denetim tarihi (:next_audit_date) son denetim tarihinden (:last_audit_date) önce. Lütfen bir sonraki denetim tarihini güncelleyin.',
    'labels_generated' => 'Etiketler başarıyla oluşturuldu.',
    'error_generating_labels' => 'Etiket oluşturulurken hata oluştu.',
    'no_assets_selected' => 'Seçilmiş varlık yok.',

    'create' => [
        'error' => 'Demirbaş oluşturulamadı, lütfen tekrar deneyin. ',
        'success' => 'Demirbaş oluşturuldu.',
        'success_no_checkout' => 'Varlık başarıyla oluşturuldu, ancak zimmet alma izniniz olmadığından size zimmetlenmedi.',
        'checkout_skipped_no_permission' => 'Varlık oluşturuldu, ancak zimmet alma izniniz olmadığından istenen hedefe zimmetlenmedi.',
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
        'error' => 'Demirbaş güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Demirbaş güncellendi.',
        'encrypted_warning' => 'Varlık başarıyla güncellendi, ancak şifreli özel alanlar izin nedeniyle güncellenemedi',
        'nothing_updated' => 'Hiçbir alan seçilmedi, dolayısıyla hiç bir alan güncellenmedi.',
        'no_assets_selected' => 'Hiçbir varlık seçilmedi, bu nedenle hiçbir şey güncellenmedi.',
        'assets_do_not_exist_or_are_invalid' => '',
    ],

    'bulk_update' => [
        'success' => 'Varlık başarıyla güncellendi.|:count varlık başarıyla güncellendi.',
        'partial' => ':success varlık başarıyla güncellendi, :failed varlık güncellenemedi. Ayrıntılar için sonuçlar dizisine bakın.',
        'error' => 'Hiçbir varlık güncellenmedi. Ayrıntılar için sonuçlar dizisine bakın.',
    ],

    'restore' => [
        'error' => 'Demirbaş geri getirilemedi, lütfen tekrar deneyin',
        'success' => 'Demirbaş geri getirildi.',
        'bulk_success' => 'Varlık başarı ile geri yüklendi.',
        'nothing_updated' => 'Herhangi bir varlık seçili olmadığı için hiçbirşey geri yüklenmedi.',
    ],

    'audit' => [
        'error' => 'Varlık denetimi başarısız: :error',
        'success' => 'Varlık denetimi başarıyla günlüğe kaydedildi.',
    ],

    'deletefile' => [
        'error' => 'Dosya silinemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya silindi.',
    ],

    'upload' => [
        'error' => 'Dosya(lar) yüklenemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya(lar) yüklendi.',
        'nofiles' => 'Yükleme için herhangi bir dosya seçmediniz veya karşıya yüklemeye çalıştığınız dosya çok büyük',
        'invalidfiles' => 'Bir ya da daha fazla dosya izin verilen boyuttan daha büyük ya da izin verilmeyen bir dosya tipi seçtiniz. Lütfen dosya boyutu ve tipini kontrol ediniz.',
    ],

    'import' => [
        'import_button' => 'İçeri aktarma işlemi',
        'error' => 'Bazı öğeler doğru şekilde içe aktarılamadı.',
        'errorDetail' => 'Aşağıdaki öğeler hatalar nedeniyle alınamadı.',
        'success' => 'Dosyanızı içe aktarıldı',
        'file_delete_success' => 'Dosyanız başarıyla silindi',
        'file_delete_error' => 'Dosya silenemedi',
        'file_missing' => 'Seçilen dosya bulunamıyor',
        'file_already_deleted' => 'Seçilen dosya zaten silinmiş',
        'file_missing_on_disk' => 'Bu içe aktarma dosyası artık diskte yok. Snipe-IT dışında silinmiş olabilir. Bu girdiyi silip dosyayı yeniden yükleyerek tekrar deneyin.',
        'file_empty' => 'Bu dosyada veri satırı yok. Dosyadan içe aktarılacak hiçbir şey bulunmuyor.',
        'already_processing' => 'Bu içe aktarma şu anda başka bir kullanıcı tarafından işleniyor. Tekrar denemeden önce işlemin tamamlanmasını bekleyin.',
        'header_row_missing' => 'Bu dosyada tanınan bir başlık satırı yok. Bu girdiyi silip dosyayı yeniden yükleyerek tekrar deneyin.',
        'header_row_has_malformed_characters' => 'Başlık bilgisindeki bir veya daha fazla öznitelik, hatalı UTF-8 karakterleri içeriyor',
        'content_row_has_malformed_characters' => 'Başlıktaki ilk satırda bir veya daha fazla öznitelik, hatalı biçimlendirilmiş UTF-8 karakterleri içeriyor',
        'transliterate_failure' => ':encoding kodlamasından UTF-8\'e dönüştürme, girişteki geçersiz karakterler nedeniyle başarısız oldu.',
        'bulk_delete' => [
            'button' => 'Seçilenleri Sil (:count)',
            'confirm_title' => 'Seçilen içe aktarma dosyaları silinsin mi?',
            'confirm_body' => ':count içe aktarma dosyasını kalıcı olarak silmek üzeresiniz. Bu işlem geri alınamaz.',
            'confirm_button' => 'Sil',
            'success' => 'İçe aktarma dosyası başarıyla silindi.|:count içe aktarma dosyası başarıyla silindi.',
            'skipped' => ':count dosya silme izniniz olmadığı için atlandı.',
            'select_all' => 'Bu sayfadaki tüm dosyaları seç',
            'select_row' => 'Toplu silme için :file dosyasını seç',
        ],
        'row_count' => '{0} Bu dosyada veri satırı yok|{1} İçe aktarılacak :count veri satırı|[2,*] İçe aktarılacak :count veri satırı',
        'summary' => [
            'created' => 'Oluşturuldu',
            'updated' => 'Güncellendi',
            'skipped' => 'Yinelenen oldukları için atlandı',
            'errored' => 'Hata oluştu',
            'no_changes' => 'İçe aktarma tamamlandı ancak hiçbir kayıt oluşturulmadı veya güncellenmedi. Genellikle ilgili kayıtlar zaten mevcut olduğu için tüm satırlar atlandı. Beklediğiniz sonuç bu değilse aşağıdaki sayıları kontrol edip CSV dosyasını veya içe aktarma türünü değiştirin.',
        ],
        'type_required' => 'Devam etmeden önce bir içe aktarma türü seçin.',
        'processing' => 'İçe aktarma işleniyor. Lütfen sayfayı kapatmadan önce tamamlanmasını bekleyin.',
        'backup_running' => 'İçe aktarma öncesi yedek alınıyor. Büyük dosyalarda bu işlem biraz sürebilir. Lütfen bekleyin.',
        'backup_label' => 'İçe aktarma öncesi yedek',
        'backup_complete' => 'Yedekleme tamamlandı',
        'import_label' => 'İçeri aktar',
        'required_fields_missing' => 'Aşağıdaki zorunlu alanlar eşleştirilmedi: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(eksik varlık etiketi)',
            'missing_asset_tag_message' => 'Satır atlandı: demirbaş etiketi belirtilmedi.',
            'asset_not_found_message' => 'Bu etikete sahip demirbaş bulunamadı. Önce demirbaşları içe aktarın, ardından geçmiş içe aktarımını yeniden çalıştırın.',
            'target_not_matched_message' => ':target_type için ":name" eşleşmesi bulunamadı. Kullanıcılar için 1. adımdaki eşleştirme seçeneklerini değiştirin veya önce kullanıcıyı oluşturun. Konumlar için CSV konum adının mevcut bir konumla tamamen aynı olduğundan emin olun.',
            'invalid_target_type_message' => 'Hedef türü ":value" tanınmıyor. "user" veya "location" kullanın ya da varsayılan olarak user seçilmesi için sütunu boş bırakın.',
        ],
        'wizard' => [
            'step_type' => 'Tür seç',
            'step_map' => 'Alanları eşleştir',
            'step_preview' => 'Ön İzle',
            'back' => 'Geri',
            'next' => 'Sonraki',
            'preview_button' => 'Ön İzle',
            'process' => 'İçe aktarımı işle',
            'preview_intro' => 'Eşlemenizi uyguladıktan sonra ilk :count satırın önizlemesi gösteriliyor. İçe aktarmadan önce eşlenen öznitelikleri düzenlemeniz gerekiyorsa Geri düğmesini kullanın.',
        ],
    ],

    'delete' => [
        'confirm' => 'Demirbaşı silmek istediğinize emin misiniz?',
        'error' => 'Demirbaş silinirken bir problem oluştu. Lütfen tekrar deneyin.',
        'assigned_to_error' => '{1}Varlık Etiketi: :asset_tag şu anda teslim alınmış durumda. Silmeden önce bu cihazı teslim edin.|[2,*]Varlık Etiketleri: :asset_tag şu anda teslim alınmış durumda. Silmeden önce bu cihazları teslim edin.',
        'nothing_updated' => 'Herhangi bir varlık seçilmediği için silinemedi.',
        'success' => 'Demirbaş silindi.',
    ],

    'checkout' => [
        'error' => 'Demirbaş çıkışı yapılamadı. Lütfen tekrar deneyin',
        'success' => 'Demirbaş çıkışı yapıldı.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'not_available' => 'Bu varlık için atama yapılamaz!',
        'no_assets_selected' => 'Listeden en az bir varlık seçmelisiniz',
    ],

    'multi-checkout' => [
        'error' => 'Varlık teslim edilemedi, lütfen tekrar deneyin|Varlıklar teslim edilemedi, lütfen tekrar deneyin',
        'success' => 'Varlık başarıyla çıkış yapıldı.|Varlıklar başarıyla çıkış yaptı.',
    ],

    'multi-checkin' => [
        'error' => 'Varlık zimmetten düşülemedi, lütfen tekrar deneyin|Varlıklar zimmetten düşülemedi, lütfen tekrar deneyin',
        'success' => 'Varlık başarıyla zimmetten düşüldü.|Varlıklar başarıyla zimmetten düşüldü.',
        'no_assets_selected' => 'Listeden en az bir varlık seçmelisiniz',
    ],

    'multi-audit' => [
        'success' => ':count demirbaş başarıyla denetlendi.|:count demirbaş başarıyla denetlendi.',
        'partial_error' => ':success demirbaş denetlendi, :failed başarısız oldu. Aşağıdaki hataları kontrol edip tekrar deneyin.|:success demirbaş denetlendi, :failed başarısız oldu. Aşağıdaki hataları kontrol edip tekrar deneyin.',
        'no_assets_selected' => 'Listeden en az bir varlık seçmelisiniz',
    ],

    'checkin' => [
        'error' => 'Demirbaş girişi yapılamadı. Lütfen tekrar deneyin',
        'success' => 'Demirbaş girişi yapıldı.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Bu kullanıcı geçersiz. Lütfen tekrar deneyin.',
        'already_checked_in' => 'Bu varlık zaten atanmış.',
        'force_checkin_orphaned_success' => 'Geçersiz atama başarıyla temizlendi.',
        'force_checkin_not_orphaned' => 'Öğe geçersiz bir atama durumunda değil.',
        'force_checkin_error' => 'Geçersiz atama temizlenemedi.',

    ],

    'requests' => [
        'error' => 'Talep başarılı olamadı, lütfen tekrar deneyin',
        'success' => 'Talep başarıyla gönderildi.',
        'canceled' => 'Talep başarıyla iptal edildi.',
        'cancel' => 'İsteği iptal et',
        'duplicate' => 'Bu öğe için zaten etkin bir talebiniz var.',
        'no_active' => 'Bu öğe için iptal edebileceğiniz etkin bir talebiniz yok.',
        'insufficient_stock' => 'Bu talebi karşılamak için stok yeterli değil. Önce stoğu yenileyin.',
        'confirm_cancel_by_admin' => ":user kullanıcısının :item talebini iptal et?",
        'no_selection' => 'Karşılanacak hiçbir satır seçilmedi.',
        'row_stale' => '#:id numaralı talep artık beklemede değil ve atlandı.',
        'row_qty_invalid' => '#:id numaralı talepte geçersiz miktar var; talep atlandı.',
        'row_user_missing' => '#:id numaralı talep için geçerli bir hedef kullanıcı bulunamadı; talep atlandı.',
        'row_asset_missing' => '#:id numaralı talep için geçerli bir hedef varlık bulunamadı; talep atlandı.',
        'row_asset_not_requesters' => '#:id numaralı talep atlandı: seçilen varlık talep edene ait değil.',
        'row_asset_not_available' => '#:id numaralı talep atlandı: seçilen varlık, bu modelin kullanılabilir bir birimi değil.',
        'row_asset_taken' => '#:id numaralı talep atlandı: seçilen varlık gönderimden önce başka birine atandı.',
        'row_company_mismatch' => '#:id numaralı talep atlandı: :user bu şirketten varlık alamaz.',
        'row_over_allocated' => '#:id numaralı talep atlandı: gönderim sırasında yeterli stok yok.',
        'no_target_assets_for_user' => ':user kullanıcısına atanabilecek varlık yok.',
        'no_available_units' => 'Bu modelin dağıtılabilecek kullanılabilir birimi yok.',
        'bulk_summary' => ':total talepten :fulfilled tanesi karşılandı.',
        'bulk_fulfill_notification_intro' => 'İşaretlenen her talep karşılandığında aşağıdakiler uygulanacak:',
    ],

];
