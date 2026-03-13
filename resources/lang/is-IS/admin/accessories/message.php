<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Þessi aukabúnaður er nú þegar: count items sem skráð eru á notendur. Vinsamlegast skráðu inn aukabúnaðinn og reyndu aftur! ',

    'create' => array(
        'error'   => 'Aukabúnaðurinn var ekki skráður, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaðurinn var skráður.'
    ),

    'update' => array(
        'error'   => 'Aukabúnaðurinn var ekki uppfærður, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaðurinn var uppfærður.'
    ),

    'delete' => array(
        'confirm'   => 'Viltu örugglega eyða þessum tiltekna aukabúnaði?',
        'error'   => 'Það var eitthvað smá vesen að eyða aukabúnaðinum, vinsamlegast reyndu aftur.',
        'success' => 'Aukabúnaðinum var eytt.'
    ),

     'checkout' => array(
        'error'   		=> 'Aukabúnaður fór ekki í úttekt, vinsamlegast reyndu aftur!',
        'success' 		=> 'Aukabúnaður fór í úttekt.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Notandinn er ónothæfur. Vinsamlegast reyndu aftur.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Aukabúnaði var ekki skilað, vinsamlegast reyndu aftur!',
        'success' 		=> 'Aukabúnaði var skilað inn.',
        'user_does_not_exist' => 'Þessi notandi er ónothæfur, vinsamlegast reyndu aftur.'
    )


);
