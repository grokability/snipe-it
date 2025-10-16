<?php

return array(

    'does_not_exist' => 'Lisensen finnes ikke, eller du har ikke tillatelse til å se den.',
    'user_does_not_exist' => 'Brukeren finnes ikke, eller du har ikke tillatelse til å se dem.',
    'asset_does_not_exist' 	=> 'Eiendelen du prøver å koble til denne lisensen eksisterer ikke.',
    'owner_doesnt_match_asset' => 'Eiendelen du prøver å koble til denne lisensen er eid av noen andre enn personen du har valgt i tildelt til-nedtrekkslista.',
    'assoc_users'	 => 'Denne lisensen er sjekket ut til en bruker og kan ikke slettes. Vennligst sjekk inn lisensen først, og forsøk sletting på nytt. ',
    'select_asset_or_person' => 'Du må velge en ressurs eller en bruker, men ikke begge.',
    'not_found' => 'Lisens ikke funnet',
    'seats_available' => ':seat_count seter tilgjengelige',


    'create' => array(
        'error'   => 'Lisens ble ikke opprettet, prøv igjen.',
        'success' => 'Vellykket opprettelse av lisens.'
    ),

    'deletefile' => array(
        'error'   => 'Fil ble ikke slettet. Prøv igjen.',
        'success' => 'Fil ble slettet.',
    ),

    'upload' => array(
        'error'   => 'Fil(er) ble ikke lastet opp. Prøv igjen.',
        'success' => 'Fil(er) ble slettet.',
        'nofiles' => 'Ingen fil er valgt til opplasting, eller filen er for stor',
        'invalidfiles' => 'En eller flere av filene er for stor, eller er en filtype som ikke er tillatt. Tillatte filtyper er png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, og lic.',
    ),

    'update' => array(
        'error'   => 'Lisens ble ikke oppdatert, prøv igjen',
        'success' => 'Vellykket oppdatering av lisens.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette denne lisensen?',
        'error'   => 'Det oppstod et problem under sletting av lisens. Vennligst prøv igjen.',
        'success' => 'Vellykket sletting av lisens.'
    ),

    'checkout' => array(
        'error'   => 'Det oppstod et problem under utsjekk av lisens. Vennligst prøv igjen.',
        'success' => 'Vellykket utsjekk av lisens',
        'not_enough_seats' => 'Ikke nok lisensseter tilgjengelige for utsjekking',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Det oppstod et problem under innsjekk av lisens. Vennligst prøv igjen.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Vellykket innsjekk av lisens'
    ),

);
