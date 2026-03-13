<?php

return array(

    'does_not_exist' => 'Aksesori [:id] tidak wujud.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Aksesori ini pada masa ini mempunyai: mengira item yang diperiksa kepada pengguna. Sila semak aksesori dan cuba lagi.',

    'create' => array(
        'error'   => 'Aksesori tidak dicipta, sila cuba lagi.',
        'success' => 'Aksesori telah berjaya dicipta.'
    ),

    'update' => array(
        'error'   => 'Aksesori gagal dikemaskini, sila cuba lagi',
        'success' => 'Aksesori berjaya dikemaskini.'
    ),

    'delete' => array(
        'confirm'   => 'Anda pasti anda mahu membuang aksesori ini?',
        'error'   => 'Ralat berlaku semasa membuang aksesori. Sila cuba lagi.',
        'success' => 'Aksesori berjaya dibuang.'
    ),

     'checkout' => array(
        'error'   		=> 'Aksesori tidak diperiksa, sila cuba lagi',
        'success' 		=> 'Aksesori diperiksa dengan jayanya.',
        'unavailable'   => 'Tiada aksesori untuk dikeluarkan. Sila periksa kuantiti sedia ada',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Aksesori tidak diperiksa, sila cuba lagi',
        'success' 		=> 'Aksesori diperiksa dengan jayanya.',
        'user_does_not_exist' => 'Pengguna itu tidak sah. Sila cuba lagi.'
    )


);
