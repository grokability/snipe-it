<?php

return array(
    'about_licenses_title'      => 'Lisanslar Hakkında',
    'about_licenses'            => 'Lisanslar yazılım takibi için kullanılır.  Kullanıcı sayısı kadar kişide kullanılabilir',
    'checkin'  					=> 'Lisans Kullanıcısı Girişi',
    'checkout_history'  		=> 'Çıkış Geçmişi',
    'checkout'  				=> 'Lisans Kullanıcı Çıkışı',
    'edit'  					=> 'Lisansı Düzenle',
    'filetype_info'				=> 'İzin verilen dosya türleri; png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar.',
    'clone'  					=> 'Lisansı Kopyala',
    'history_for'  				=> 'Geçmiş ',
    'in_out'  					=> 'Giriş/Çıkış',
    'info'  					=> 'Lisans Bilgisi',
    'license_seats'  			=> 'Lisans Kullanıcıları',
    'seat'  					=> 'Kullanıcı',
    'seat_count'  				=> 'Hak :count',
    'seats'  					=> 'Kullanıcılar',
    'software_licenses'  		=> 'Yazılım Lisansları',
    'user'  					=> 'Kullanıcı',
    'view'  					=> 'Lisansı Göster',
    'delete_disabled'           => 'Bazı koltuklar hala kullanıma alınmış olduğundan bu lisans henüz silinemez.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Tüm koltukları ayır',
                'modal'             => 'Bu işlem bir lisans hakkını geri alacak. | Bu işlem, bu lisans için atanmış tüm :checkedout_seats_count lisans hakkını geri alacak.',
                'enabled_tooltip'   => 'Bu lisans için hem kullanıcılardan hem de varlıklardan TÜM lisansları kontrol edin',
                'disabled_tooltip'  => 'Şu anda teslim alınmış koltuk olmadığından bu devre dışı bırakıldı',
                'disabled_tooltip_reassignable'  => 'Lisans yeniden atanamadığı için bu devre dışı bırakıldı',
                'success'           => 'Lisans başarıyla kontrol edildi! | Tüm lisanslar başarıyla kontrol edildi!',
                'log_msg'           => 'Lisans arayüzündeki toplu lisans iade işlemiyle geri alındı',
            ],

            'checkout_all'              => [
                'button'                => 'Tüm koltukları incele',
                'modal'                 => 'Bu işlem, müsait olan ilk kullanıcıya bir koltuğun ödemesini yapacaktır. | Bu işlem, tüm :available_seats_count koltukları ilk müsait kullanıcılara teslim edecektir. Bir kullanıcı, bu lisansı henüz kendisine teslim etmemişse ve kullanıcı hesabında Otomatik Lisans Atama özelliği etkinleştirilmişse, bu koltuk için uygun kabul edilir.',
                'enabled_tooltip'   => 'TÜM kullanıcılara TÜM koltukları (veya mevcut olan sayıda) ödeme yapın',
                'disabled_tooltip'  => 'Ulaşılabilir koltruk olmadığı için bu devre dışı bırakıldı',
                'success'           => 'Lisans başarıyla kontrol edildi! | :count lisansları başarıyla teslim alındı!',
                'error_no_seats'    => 'Bu lisans için kalan koltuk kalmadı.',
                'warn_not_enough_seats'    => ':count kullanıcılara bu lisans atandı, ancak mevcut lisans koltuklarımız tükendi.',
                'warn_no_avail_users'    => 'Yapacak bir şey yok. Henüz kendisine bu lisans atanmamış kullanıcı yok.',
                'log_msg'           => 'Lisans GUI\'sinde toplu lisans ödemesi yoluyla teslim alındı',


            ],
    ],

    'below_threshold' => 'Bu lisans için minimum adet :min_amt olmak üzere yalnızca :remaining_count hak kaldı. Daha fazla lisans hakkı satın almayı düşünebilirsiniz.',
    'below_threshold_short' => 'Bu ürün, minimum sipariş miktarının altındadır.',
);
