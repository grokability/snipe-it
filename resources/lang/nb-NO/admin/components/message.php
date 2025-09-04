<?php

return array(

    'does_not_exist' => 'Komponenten finnes ikke.',

    'create' => array(
        'error'   => 'Komponenten ble ikke opprettet, vennligst prøv igjen.',
        'success' => 'Komponent ble opprettet.'
    ),

    'update' => array(
        'error'   => 'Komponenten ble ikke oppdatert. Vennligst prøv igjen',
        'success' => 'Komponent ble oppdatert.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette denne komponenten?',
        'error'   => 'Det oppstod et problem under sletting av komponenten. Vennligst prøv igjen.',
        'success' => 'Sletting av komponent vellykket.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Komponent ble ikke sjekket ut. Prøv igjen',
        'success' 		=> 'Vellykket utsjekk av komponent.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'unavailable'      => 'Ikke nok komponenter igjen: :remaining gjenværende, :requested ',
    ),

    'checkin' => array(
        'error'   		=> 'Komponenten ble ikke sjekket inn, vennligst prøv igjen',
        'success' 		=> 'Vellykket innsjekk av komponent.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.'
    )


);
