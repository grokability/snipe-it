<?php

return [

    'does_not_exist' => 'Pribor [:Id] ne postoji.',
    'not_found' => 'Ta dodatna oprema nije pronađena.',
    'assoc_users' => 'Ovaj pribor trenutno ima :count stavku označenu korisnicima. Proverite pribor i pokušajte ponovo. ',

    'create' => [
        'error' => 'Pribor nije kreiran. Pokušajte ponovo.',
        'success' => 'Pribor je uspešno kreiran.',
    ],

    'update' => [
        'error' => 'Pribor nije ažuriran. Pokušajte ponovo',
        'success' => 'Pribor je uspešno ažuriran.',
    ],

    'delete' => [
        'confirm' => 'Da li ste sigurni da želite brisanje pribora?',
        'error' => 'Došlo je do problema s brisanjem dodatne opreme, pribora. Molim pokušajte ponovo.',
        'success' => 'Pribor je uspešno izbrisan.',
    ],

    'checkout' => [
        'error' => 'Pribor nije potvrdjen, pokušajte ponovo',
        'success' => 'Pribor je uspešno proveren.',
        'unavailable' => 'Pribor nije dostupan za zaduživanje. Proverite dostupnu količinu',
        'user_does_not_exist' => 'Korisnik nevažeći. Molim pokušajte ponovo.',
        'checkout_qty' => [
            'lte' => 'Trenutno ima samo jedna dostupna dodatna oprema ove vrste, a vi pokušavate da zadužite :checkout_qty. Molim vas prilagodite količinu za zaduživanje prema dostupnom stanju ove opreme i pokušajte ponovo.|Trenutno ima ukupno :number_currently_remaining dodatne opreme, a vi pokušavate da zadužite :checkout_qty. Molim vas prilagodite količinu za zaduživanje prema dostupnom stanju ove opreme i pokušajte ponovo.',
        ],

    ],

    'checkin' => [
        'error' => 'Pribor nije prijavljen, pokušajte ponovo',
        'success' => 'Pribor je uspešno prijavljen.',
        'user_does_not_exist' => 'Korisnik nevažeći. Molim pokušaj te ponovo.',
    ],

];
