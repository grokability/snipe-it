<?php

return array(

    'does_not_exist' => 'Komponent eksisterer ikke.',

    'create' => array(
        'error'   => 'Komponent blev ikke oprettet, prøv igen.',
        'success' => 'Komponent oprettet med succes.'
    ),

    'update' => array(
        'error'   => 'Komponent blev ikke opdateret, prøv igen',
        'success' => 'Komponent opdateret med succes.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på, at du vil slette denne komponent?',
        'error'   => 'Der opstod et problem ved at slette komponenten. Prøv igen.',
        'success' => 'Komponenten blev slettet korrekt.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Komponent blev ikke tjekket ud, prøv igen',
        'success' 		=> 'Komponent tjekket ud med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
        'unavailable'      => 'Ikke nok komponenter tilbage: :remaining remaining :requested requested ',
    ),

    'checkin' => array(
        'error'   		=> 'Komponent blev ikke tjekket ind, prøv igen',
        'success' 		=> 'Komponent tjekket ind med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.'
    )


);
