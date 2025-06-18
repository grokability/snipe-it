<?php

return [

    'update' => [
        'error'                 => 'Güncelleme yapılırken bir hata oluştu. ',
        'success'               => 'Ayarlar güncellendi.',
    ],
    'backup' => [
        'delete_confirm'        => 'Bu yedek dosyayı silmek istediğinizden emin misiniz? Bu eylem geri alınamaz. ',
        'file_deleted'          => 'Yedek dosyası başarıyla silindi.',
        'generated'             => 'Yeni bir yedekleme dosyası başarıyla oluşturuldu.',
        'file_not_found'        => 'Bu yedek dosyası sunucuda bulunamadı.',
        'restore_warning'       => 'Evet, geri yükleyin. Bunun, şu anda veritabanında bulunan mevcut verilerin üzerine yazılacağını kabul ediyorum. Bu aynı zamanda (siz dahil) tüm mevcut kullanıcılarınızın oturumunu kapatacaktır.',
        'restore_confirm'       => 'Veritabanınızı :filename\'den geri yüklemek istediğinizden emin misiniz?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Temizleme sırasında bir hata oluştu. ',
        'validation_failed'     => 'Temizle onay kodu yanlıştır. Lütfen onay kutusuna "DELETE" yazın.',
        'success'               => 'Silinen kayıtları başarıyla temizlendi.',
    ],
    'mail' => [
        'sending' => 'Test maili gönderiliyor...',
        'success' => 'Mail gönder!',
        'error' => 'Mail gönderilemedi.',
        'additional' => 'Ek hata mesajı sağlanmadı. Posta ayarlarınızı ve uygulama günlüğünüzü kontrol edin.'
    ],
    'ldap' => [
        'testing' => 'LDAP bağlantısı deneniyor, bağlanılıyor ve sorgulanıyor ...',
        '500' => '500 Sunucu Hatası. Daha fazla bilgi için lütfen sunucu günlüklerinizi kontrol edin.',
        'error' => 'Bir şeyler yanlış gitti :(',
        'sync_success' => 'Ayarlarınıza göre LDAP sunucusundan döndürülen 10 kullanıcıdan oluşan bir örnek:',
        'testing_authentication' => 'LDAP kimlik doğrulaması deneniyor...',
        'authentication_success' => 'LDAP kullanıcı kimliği başarıyla doğrulandı!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => ':app test mesajı gönderiliyor...',
        'success' => ':webhook_name entegrasyonunuz çalışıyor!',
        'success_pt1' => 'Başarılı! Kontrol edin ',
        'success_pt2' => ' test mesajınız için kanala gidin ve ayarlarınızı kaydetmek için aşağıdaki KAYDET\'i tıklamayı unutmayın.',
        '500' => '500 Sunucu Hatası.',
        'error' => 'Bir şeyler yanlış gitti. :app bu şekilde yanıt verdi: :error_message',
        'error_redirect' => 'HATA: 301/302: bağlantı başka bir yere yönlendiriyor. Güvenlik nedeniyle yönlendirmeleri takip etmiyoruz. Lütfen direk adresi kullanın.',
        'error_misc' => 'Bir şeyler yanlış gitti. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
