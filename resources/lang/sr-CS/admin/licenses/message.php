<?php

return array(

    'does_not_exist' => 'Licenca ne postoji ili vi nemate dozvolu da je vidite.',
    'user_does_not_exist' => 'Korisnik ne postoji ili vi nemate ovlašćenja da ga vidite.',
    'asset_does_not_exist' 	=> 'Imovina koju pokušavate povezati s ovom licencom ne postoji.',
    'owner_doesnt_match_asset' => 'Imovina koju pokušavate povezati s ovom licencom nije u vlasništvu osobe koja je odabrana u padajućem meniju.',
    'assoc_users'	 => 'This license is currently checked out to a user and cannot be deleted. Please check the license in first, and then try deleting again. ',
    'select_asset_or_person' => 'Morate odabrati neku vrstu imovine ili korisnika, ali ne oboje.',
    'not_found' => 'Licenca nije pronađena',
    'seats_available' => ':seat_count mesta je dostupno',


    'create' => array(
        'error'   => 'Licenca nije kreirana, pokušajte ponovo.',
        'success' => 'Licenca je uspješno kreirana.'
    ),

    'deletefile' => array(
        'error'   => 'Datoteka nije izbrisana. Molim pokušajte ponovo.',
        'success' => 'Datoteka je uspešno obrisana.',
    ),

    'upload' => array(
        'error'   => 'Datoteke nisu prenesene. Molim pokušajte ponovo.',
        'success' => 'Datoteke su uspešno učitane.',
        'nofiles' => 'Niste odabrali nijednu datoteku za prenos ili je datoteka koju pokušavate preneti prevelika',
        'invalidfiles' => 'Jedna ili više datoteka je prevelika ili je vrsta datoteke koja nije dopuštena. Dopuštene vrste datoteka su png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml i lic.',
    ),

    'update' => array(
        'error'   => 'Licenca nije ažurirana, pokušajte ponovo',
        'success' => 'Licenca je uspješno ažurirana.'
    ),

    'delete' => array(
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovu licencu?',
        'error'   => 'Došlo je do problema sa brisanjem licence. Molim pokušajte ponovo.',
        'success' => 'Licenca je uspešno obrisana.'
    ),

    'checkout' => array(
        'error'   => 'Došlo je do problema prilikom provere licence. Molim pokušajte ponovo.',
        'success' => 'Licenca je uspešno proverena',
        'not_enough_seats' => 'Nema dovoljno dostupnih licenci za zaduživanje',
        'mismatch' => 'Dostavljeno mesto licence se ne poklapa sa licencom',
        'unavailable' => 'Ovo mesto nije dostupno za zaduživanje.',
        'license_is_inactive' => 'Licenca je istekla ili je otkazana.',
    ),

    'checkin' => array(
        'error'   => 'Došlo je do problema prilikom provere licence. Molim pokušajte ponovo.',
        'not_reassignable' => 'Mesto je iskorišćeno',
        'success' => 'Licenca je uspešno proverena'
    ),

);
