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
        'bulk_success' => 'Dodatna oprema je uspešno izbrisana.|:count dodatne opreme je uspešno izbrisano.',
        'partial_success' => ':count dodatne opreme je uspešno izbrisano, ali ostalo nije moglo biti izbrisano. Pogledajte ispod za više detalja.|:count dodatne opreme je uspešno izbrisano, ali ostalo nije moglo biti izbrisano. Pogledajte ispod za više detalja.',
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

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Polja nisu menjana, tako da ništa nije ažurirano.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
