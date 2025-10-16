<?php

return array(

    'does_not_exist' => 'Enten findes licensen ikke eller også har du ikke tilladelse til at se den.',
    'user_does_not_exist' => 'Brugeren findes ikke, eller du har ikke tilladelse til at se dem.',
    'asset_does_not_exist' 	=> 'Aktivet, du forsøger at associere med denne licens, findes ikke.',
    'owner_doesnt_match_asset' => 'Aktivet, du forsøger at associere med denne licens, ejes af en anden end den person, der er valgt i Tildelt-rullemenuen.',
    'assoc_users'	 => 'Denne licens er i øjeblikket tjekket ud til en bruger og kan ikke slettes. Kontroller venligst licensen først, og prøv derefter at slette igen.',
    'select_asset_or_person' => 'Du skal vælge et aktiv eller en bruger, men ikke begge.',
    'not_found' => 'Licens blev ikke fundet',
    'seats_available' => ':seat_count sæder til rådighed',


    'create' => array(
        'error'   => 'Licensen blev ikke oprettet, prøv igen.',
        'success' => 'Licensen er oprettet med succes.'
    ),

    'deletefile' => array(
        'error'   => 'Filen er ikke slettet. Prøv igen.',
        'success' => 'Filen er slettet korrekt.',
    ),

    'upload' => array(
        'error'   => 'Fil (er) ikke uploadet. Prøv igen.',
        'success' => 'Fil (er), der blev uploadet korrekt.',
        'nofiles' => 'Du valgte ikke nogen filer til upload, eller filen du forsøger at uploade er for stor',
        'invalidfiles' => 'En eller flere af dine filer er for store eller er en filtype, der ikke er tilladt. Tilladte filtyper er png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml og lic.',
    ),

    'update' => array(
        'error'   => 'Licensen blev ikke opdateret, prøv igen',
        'success' => 'Licensen opdateres med succes.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på, at du vil slette denne licens?',
        'error'   => 'Der opstod et problem ved at slette licensen. Prøv igen.',
        'success' => 'Licensen blev slettet korrekt.'
    ),

    'checkout' => array(
        'error'   => 'Der var et problem med at tjekke licensen. Prøv igen.',
        'success' => 'Licensen blev tjekket ud med succes',
        'not_enough_seats' => 'Ikke nok licenser til rådighed til kassen',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Der var et problem at kontrollere licensen. Prøv igen.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licensen blev tjekket ind med succes'
    ),

);
