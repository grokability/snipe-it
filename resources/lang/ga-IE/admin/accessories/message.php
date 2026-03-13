<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Tá an accessory seo faoi láthair: cuntais a sheiceáil le húsáideoirí. Seiceáil na gabhálais agus déan iarracht arís.',

    'create' => array(
        'error'   => 'Níor cruthaíodh an accessory, déan iarracht arís.',
        'success' => 'Rinneadh an cúlpháirtí a chruthú go rathúil.'
    ),

    'update' => array(
        'error'   => 'Níor nuashonraíodh an accessory, déan iarracht arís',
        'success' => 'Tugadh suas chun dáta an cúlpháirtí go rathúil'
    ),

    'delete' => array(
        'confirm'   => 'An bhfuil tú cinnte gur mian leat an accessory seo a scriosadh?',
        'error'   => 'Bhí ceist ann a scriosadh an accessory. Arís, le d\'thoil.',
        'success' => 'Scriosadh an cúlpháirtí go rathúil.'
    ),

     'checkout' => array(
        'error'   		=> 'Níor seiceáladh an Cúntóir amach, déan iarracht arís',
        'success' 		=> 'Rinne an cúntóir a sheiceáil go rathúil.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Níor seiceáladh an cúntóir isteach, déan iarracht arís',
        'success' 		=> 'Faireacán seiceáil go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.'
    )


);
