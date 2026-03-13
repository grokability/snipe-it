<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Lesi sesekeli samanje sino: ukubala izinto ezihlolwe kubasebenzisi. Sicela uhlole izesekeli bese uzama futhi.',

    'create' => array(
        'error'   => 'Impahla ayidalwanga, sicela uzame futhi.',
        'success' => 'I-accessory yenziwe ngempumelelo.'
    ),

    'update' => array(
        'error'   => 'Isesekeli asizange sibuyekezwe, sicela uzame futhi',
        'success' => 'I-accessory ibuyekezwe ngempumelelo.'
    ),

    'delete' => array(
        'confirm'   => 'Ingabe uqinisekile ukuthi ufisa ukususa le ndawo yokufinyelela?',
        'error'   => 'Kube nenkinga yokususa i-accessory. Ngicela uzame futhi.',
        'success' => 'Isesekeli sisusiwe ngempumelelo.'
    ),

     'checkout' => array(
        'error'   		=> 'Ukufinyelela akuzange kuhlolwe, sicela uzame futhi',
        'success' 		=> 'Ukufinyelela kufakwe ngempumelelo.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Ukufinyelela akuzange kungenwe, sicela uzame futhi',
        'success' 		=> 'Okufinyeleleka kuhlolwe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.'
    )


);
