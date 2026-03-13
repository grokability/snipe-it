<?php

return array(

    'does_not_exist' => 'Tilbehøret [:id] finnes ikke.',
    'not_found' => 'Finner ikke dette tilbehøret.',
    'assoc_users'	 => 'Dette tilbehøret har for øyeblikket :count enheter utsjekket til brukere. Sjekk inn tilbehøret og prøv igjen. ',

    'create' => array(
        'error'   => 'Tilbehør ble ikke opprettet, vennligst prøv igjen.',
        'success' => 'Tilbehør optrettet.'
    ),

    'update' => array(
        'error'   => 'Tilbehør ble ikke oppdatert, vennligst prøv igjen',
        'success' => 'Tilbehør oppdatert.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette dette tilbehøret?',
        'error'   => 'Det oppstod et problem under sletting av tilbehøret. Prøv igjen.',
        'success' => 'Tilbehøret ble slettet.'
    ),

     'checkout' => array(
        'error'   		=> 'Tilbehør ble ikke sjekket ut. Prøv igjen',
        'success' 		=> 'Vellykket utsjekking av tilbehør.',
        'unavailable'   => 'Tilbehør er ikke tilgjengelig for utsjekk. Sjekk antall tilgjengelig',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Tilbehør ble ikke sjekket inn. Prøv igjen',
        'success' 		=> 'Vellykket innsjekk av tilbehør.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.'
    )


);
