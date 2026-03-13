<?php

return array(

    'does_not_exist' => 'Aksesori itu tidak ada.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Aksesori saat ini memiliki :count item untuk pengguna. Silahkan cek di aksesoris dan dan coba lagi. ',

    'create' => array(
        'error'   => 'Aksesori tidak dapat dibuat, silahkan coba kembali.',
        'success' => 'Aksesori berhasil dibuat.'
    ),

    'update' => array(
        'error'   => 'Aksesori tidak dapat diperbaharui, silahkan coba kembali',
        'success' => 'Aksesori berhasil diperbaharui.'
    ),

    'delete' => array(
        'confirm'   => 'Anda yakin ingin menghapus aksesoris ini?',
        'error'   => 'Ada masalah untuk mengahpus aksesoris ini. Silahkan coba lagi.',
        'success' => 'Aksessoris ini berhasil dihapus.'
    ),

     'checkout' => array(
        'error'   		=> 'Aksesori belum diperiksa, silakan coba lagi',
        'success' 		=> 'Aksesori berhasil diperiksa.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Pengguna yang tidak valid. Silakan coba lagi.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Aksesori belum diperiksa, silakan coba lagi',
        'success' 		=> 'Aksesori berhasil diperiksa.',
        'user_does_not_exist' => 'Pengguna yang tidak valid. Silakan coba lagi.'
    )


);
