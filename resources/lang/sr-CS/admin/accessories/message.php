<?php

return array(

    'does_not_exist' => 'Pribor [:Id] ne postoji.',
    'not_found' => 'Ta dodatna oprema nije pronađena.',
    'assoc_users'	 => 'Ovaj pribor trenutno ima :count stavku označenu korisnicima. Proverite pribor i pokušajte ponovo. ',

    'create' => array(
        'error'   => 'Pribor nije kreiran. Pokušajte ponovo.',
        'success' => 'Pribor je uspešno kreiran.'
    ),

    'update' => array(
        'error'   => 'Pribor nije ažuriran. Pokušajte ponovo',
        'success' => 'Pribor je uspešno ažuriran.'
    ),

    'delete' => array(
        'confirm'   => 'Da li ste sigurni da želite brisanje pribora?',
        'error'   => 'Došlo je do problema s brisanjem dodatne opreme, pribora. Molim pokušajte ponovo.',
        'success' => 'Pribor je uspešno izbrisan.'
    ),

     'checkout' => array(
        'error'   		=> 'Pribor nije potvrdjen, pokušajte ponovo',
        'success' 		=> 'Pribor je uspešno proveren.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Korisnik nevažeći. Molim pokušajte ponovo.'
    ),

    'checkin' => array(
        'error'   		=> 'Pribor nije prijavljen, pokušajte ponovo',
        'success' 		=> 'Pribor je uspešno prijavljen.',
        'user_does_not_exist' => 'Korisnik nevažeći. Molim pokušaj te ponovo.'
    )


);
