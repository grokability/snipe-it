<?php

return array(

    'does_not_exist' => 'Leverandør finnes ikke.',
    'not_found' => 'Lisens ikke funnet',
    'assoc_users'	 => 'Denne modellen er tilknyttet en eller flere eiendeler og kan ikke slettes. Slett eiendelene, og prøv å slette modellen igjen. ',

    'create' => array(
        'error'   => 'Leverandør ble ikke opprettet. Prøv igjen.',
        'success' => 'Opprettelse av leverandør vellykket.'
    ),

    'update' => array(
        'error'   => 'Leverandør ble ikke oppdatert. Prøv igjen',
        'success' => 'Oppdatering av leverandør vellykket.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette denne leverandøren?',
        'error'   => 'Det oppstod et problem under sletting av leverandør. Prøv igjen.',
        'success' => 'Sletting av leverandør vellykket.'
    ),

     'checkout' => array(
        'error'   		=> 'Det oppstod et problem under utsjekk av lisens. Vennligst prøv igjen.',
        'success' 		=> 'Vellykket utsjekk av lisens',
        'unavailable'   => 'This seat is not available for checkout.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Det oppstod et problem under innsjekk av lisens. Vennligst prøv igjen.',
        'success' 		=> 'Vellykket innsjekk av lisens',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.'
    )


);
