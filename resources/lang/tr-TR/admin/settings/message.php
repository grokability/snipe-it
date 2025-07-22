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
        'success'               => 'Sistem yedeğiniz geri yüklendi. Lütfen tekrar giriş yapın.'
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
        'null_template' => 'Etiket şablonu bulunamadı. Lütfen bir şablon seçin.',
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
        'webhook_fail' => 'webhook bildirimi başarısız oldu: URL\'nin hala geçerli olduğundan emin olun.',
        'webhook_channel_not_found' => 'webhook kanalı bulunamadı.',
        'ms_teams_deprecation' => 'Seçilen Microsoft Teams webhook URL\'si 31 Aralık 2025\'te kullanımdan kaldırılacaktır. Lütfen bir iş akışı URL\'si kullanın. Microsoft\'un iş akışı oluşturmayla ilgili belgelerine <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> buradan ulaşabilirsiniz.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Ayarlarınız kaydedilemedi.',
        'mismatch' => 'Konum kapsamını etkinleştirmeden önce veritabanında dikkat etmeniz gereken 1 öğe var.|Konum kapsamını etkinleştirmeden önce veritabanında dikkat etmeniz gereken :count öğe var.',
    ],
];
