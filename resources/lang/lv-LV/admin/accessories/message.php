<?php

return array(

    'does_not_exist' => 'Piederums [:id] neeksistē.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Pašlaik šim piederumam ir: lietotāju skaits tiek pārbaudīts. Lūdzu, pārbaudiet piederumus un mēģiniet vēlreiz.',

    'create' => array(
        'error'   => 'Piederums netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Piederums tika veiksmīgi izveidots.'
    ),

    'update' => array(
        'error'   => 'Piederums netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piederums tika veiksmīgi atjaunināts.'
    ),

    'delete' => array(
        'confirm'   => 'Vai tiešām vēlaties dzēst šo piederumu?',
        'error'   => 'Radās problēma, noņemot piederumu. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Piederums tika veiksmīgi izdzēsts.'
    ),

     'checkout' => array(
        'error'   		=> 'Piederums netika pārbaudīts, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Piederums ir veiksmīgi izrakstīts.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Piederums netika atzīmēts, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Piederumi ir pārbaudīti veiksmīgi.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.'
    )


);
