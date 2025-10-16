<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Imovina koju pokušavate povezati s ovom licencom ne postoji.',
    'owner_doesnt_match_asset' => 'Imovina koju pokušavate povezati s ovom licencom u vlasništvu je nekog drugog osim osobe koja je odabrana u odjeljku za padajući izbornik.',
    'assoc_users'	 => 'Ova je licenca trenutno provjerena korisniku i ne može se izbrisati. Najprije provjerite licencu, a zatim pokušajte ponovno brisati.',
    'select_asset_or_person' => 'Morate odabrati neku vrstu imovine ili korisnika, ali ne oboje.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Licenca nije izrađena, pokušajte ponovo.',
        'success' => 'Licenca je uspješno stvorena.'
    ),

    'deletefile' => array(
        'error'   => 'Datoteka nije izbrisana. Molim te pokušaj ponovno.',
        'success' => 'Datoteka je uspješno obrisana.',
    ),

    'upload' => array(
        'error'   => 'Datoteke nisu prenesene. Molim te pokušaj ponovno.',
        'success' => 'Datoteke su uspješno učitane.',
        'nofiles' => 'Niste odabrali nijednu datoteku za prijenos ili je datoteka koju pokušavate prenijeti prevelika',
        'invalidfiles' => 'Jedna ili više datoteka je prevelika ili je vrsta datoteke koja nije dopuštena. Dopuštene vrste datoteka su png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml i lic.',
    ),

    'update' => array(
        'error'   => 'Licenca nije ažurirana, pokušajte ponovo',
        'success' => 'Licenca je uspješno ažurirana.'
    ),

    'delete' => array(
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovu licencu?',
        'error'   => 'Došlo je do problema s brisanjem licence. Molim te pokušaj ponovno.',
        'success' => 'Licenca je uspješno obrisana.'
    ),

    'checkout' => array(
        'error'   => 'Došlo je do problema prilikom provjere licence. Molim te pokušaj ponovno.',
        'success' => 'Licenca je uspješno provjerena',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'U licenci se provjeravala problem. Molim te pokušaj ponovno.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licenca je uspješno provjerena'
    ),

);
