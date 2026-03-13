<?php

return array(

    'does_not_exist' => 'Tilbehøret [:id] findes ikke.',
    'not_found' => 'Tilbehøret blev ikke fundet.',
    'assoc_users'	 => 'Dette tilbehør har pt. :count emner tjekket ud til brugere. Tjek tilbehør ind og prøv igen.',

    'create' => array(
        'error'   => 'Tilbehøret blev ikke oprettet, prøv venligst igen.',
        'success' => 'Tilbehøret blev oprettet.'
    ),

    'update' => array(
        'error'   => 'Tilbehøret blev ikke opdateret, prøv venligst igen',
        'success' => 'Tilbehøret blev opdateret med success.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på du vil slette dette tilbehør?',
        'error'   => 'Der opstod et problem under sletning af tilbehøret. Prøv venligst igen.',
        'success' => 'Tilbehøret blev slettet med success.'
    ),

     'checkout' => array(
        'error'   		=> 'Tilbehør blev ikke tjekket ud, prøv igen',
        'success' 		=> 'Tilbehør er tjekket ud.',
        'unavailable'   => 'Tilbehør er ikke tilgængeligt til kassen. Tjek antal tilgængelige',
        'user_does_not_exist' => 'Den bruger er ikke gyldig. Prøv igen.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Tilbehør blev ikke tjekket ind, prøv igen',
        'success' 		=> 'Tilbehør er tjekket ind.',
        'user_does_not_exist' => 'Den bruger er ikke gyldig. Prøv igen.'
    )


);
