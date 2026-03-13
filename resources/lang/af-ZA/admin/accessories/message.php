<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Hierdie bykomstige het tans: tel items wat uitgekontroleer is aan gebruikers. Kontroleer asseblief die bykomstighede en en probeer weer.',

    'create' => array(
        'error'   => 'Die bykomstigheid is nie geskep nie, probeer asseblief weer.',
        'success' => 'Die bykomstigheid is suksesvol geskep.'
    ),

    'update' => array(
        'error'   => 'Die bykomstigheid is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Die bykomstigheid is suksesvol opgedateer.'
    ),

    'delete' => array(
        'confirm'   => 'Is jy seker jy wil hierdie toebehore uitvee?',
        'error'   => 'Daar was \'n probleem met die verwydering van die bykomstigheid. Probeer asseblief weer.',
        'success' => 'Die bykomstigheid is suksesvol verwyder.'
    ),

     'checkout' => array(
        'error'   		=> 'Toebehore is nie nagegaan nie, probeer asseblief weer',
        'success' 		=> 'Toebehore suksesvol nagegaan.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Toebehore is nie nagegaan nie, probeer asseblief weer',
        'success' 		=> 'Toebehore is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.'
    )


);
