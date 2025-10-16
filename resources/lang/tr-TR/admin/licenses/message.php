<?php

return array(

    'does_not_exist' => 'Lisans mevcut değil veya görüntüleme izniniz yok.',
    'user_does_not_exist' => 'Kullanıcı mevcut değil veya bunları görüntüleme izniniz yok.',
    'asset_does_not_exist' 	=> 'Lisans ile ilişkilendirmek istediğiniz demirbaş mevcut değil.',
    'owner_doesnt_match_asset' => 'Lisans ile ilişkilendirmek istediğiniz demirbaş ilişkilendirmek istediğiniz kişiden başkasına atanmış durumda.',
    'assoc_users'	 => 'Bu demirbaş kullanıcıya çıkış yapılmış olaran görülüyor ve silinemez. Lütfen önce demirbaş girişi yapınız, ardından tekrar siliniz. ',
    'select_asset_or_person' => 'Bir varlık veya kullanıcı seçmelisiniz, ancak her ikisini birden değil.',
    'not_found' => 'Lisans bulunamadı',
    'seats_available' => ':seat_count atama yapılabilir',


    'create' => array(
        'error'   => 'Lisans oluşturulamadı, lütfen tekrar deneyin.',
        'success' => 'Lisans oluşturuldu.'
    ),

    'deletefile' => array(
        'error'   => 'Dosya silinemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya silindi.',
    ),

    'upload' => array(
        'error'   => 'Dosya(lar) yüklenemedi. Lütfen tekrar deneyin.',
        'success' => 'Dosya(lar) yüklendi.',
        'nofiles' => 'Yükleme için herhangi bir dosya seçmediniz veya karşıya yüklemeye çalıştığınız dosya çok büyük',
        'invalidfiles' => 'Bir veya daha fazla dosya çok büyük veya izin verilmeyen bir dosya türü. İzin verilen dosya türleri; png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, and lic.',
    ),

    'update' => array(
        'error'   => 'Lisans güncellenemedi, lütfen tekrar deneyin',
        'success' => 'Lisans güncellendi.'
    ),

    'delete' => array(
        'confirm'   => 'Lisansı silmek istediğinize emin misiniz?',
        'error'   => 'Lisansı silerken bir hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Lisans silindi.'
    ),

    'checkout' => array(
        'error'   => 'Lisans çıkışı yapılırken hata oluştu. Lütfen tekrar deneyin.',
        'success' => 'Lisans çıkışı yapıldı.',
        'not_enough_seats' => 'Ödeme için yeterli sayıda lisans yeri yok',
        'mismatch' => 'Girdiğiniz bu lisans türü lisans ile eşleşmiyor',
        'unavailable' => 'Bu varlığı atayamazsınız.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Lisans girişi yapılırken hata oluştu. Lütfen tekrar deneyin.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Lisans girişi yapıldı'
    ),

);
