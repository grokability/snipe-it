<?php

return array(

    'does_not_exist' => 'Ang aksesoryang [:id] ay hindi umiiral.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Ang aksesoryang ito ay kasalukuyang mayroong :pag-check out sa pag-kwenta ng mga aytem sa mga gumagamit o user. Paki-tingnan sa mga aksesorya at subukang muli. ',

    'create' => array(
        'error'   => 'Ang aksesorya ay hindi naisagawa, mangyaring subukan muli.',
        'success' => 'Ang aksesorya ay matagumpay na nailikha.'
    ),

    'update' => array(
        'error'   => 'Ang aksesorya ay hindi nai-update, mangyaring subukang muli',
        'success' => 'Ang aksesorya ay matagumpay na nai-upadate.'
    ),

    'delete' => array(
        'confirm'   => 'Sigurado kaba na gusto mong i-delete ang aksesoryang ito?',
        'error'   => 'Mayroong isyu sa pagdelete ng aksesorya. Mangyaring subukang muli.',
        'success' => 'Ang aksesorya ay matagumpay na nai-delete.'
    ),

     'checkout' => array(
        'error'   		=> 'Ang aksesorya ay hindi na-check out, mangyaring subukang muli',
        'success' 		=> 'Ang aksesorya ay matagumoay na nai-check out.',
        'unavailable'   => 'Ang aksesorya ay hindi maaaring i-check out. Suriing mabuti ang natitirang bilang',
        'user_does_not_exist' => 'Ang user na iyon ay hindi tama. Mangyaring subukang muli.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Ang aksesorya ay hindi nai-check in, mangyaring subukang muli',
        'success' 		=> 'Ang aksesorya ay matagumpay na nai-check in.',
        'user_does_not_exist' => 'Ang ang user na iyon ay hindi tama. Mangyaring subukang muli.'
    )


);
