<?php

return [

    'does_not_exist' => 'Dodatna oprema [:id] ne postoji.',
    'not_found' => 'Ova dodatna oprema nije pronađena.',
    'assoc_users' => 'Ovaj pribor trenutačno ima: brojčanu stavku označenu korisnicima. Provjerite pribor i pokušajte ponovo.',

    'create' => [
        'error' => 'Dodatak nije izrađen, pokušajte ponovo.',
        'success' => 'Dodatak je uspješno izrađen.',
    ],

    'update' => [
        'error' => 'Dodatak nije ažuriran, pokušajte ponovo',
        'success' => 'Dodatak je uspješno ažuriran.',
    ],

    'delete' => [
        'confirm' => 'Jeste li sigurni da želite izbrisati ovaj dodatak?',
        'error' => 'Došlo je do problema s brisanjem dodatne opreme. Molim te pokušaj ponovno.',
        'success' => 'Dodatak je uspješno izbrisan.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Dodatak nije provjeren, pokušajte ponovo',
        'success' => 'Usluga je uspješno provjerena.',
        'unavailable' => 'Dodatna oprema nije dostupna za izdavanje. Provjerite dostupnu količinu',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
        'checkout_qty' => [
            'lte' => 'Trenutno je dostupan samo jedan primjerak dodatne opreme ovog tipa, a Vi pokušavate izdati :checkout_qty. Molimo korigujte broj izdavanja ili ukupni lager ove dodatne opreme i pokušajte ponovno.|Postoji :number_currently_remaining ukupno primjeraka dodatne opreme, a Vi pokušavate izdati :checkout_qty. Molimo korigujte broj izdavanja ili ukupni lager ove dodatne opreme i pokušajte ponovno.',
        ],

    ],

    'checkin' => [
        'error' => 'Dodatna oprema nije prijavljena, pokušajte ponovo',
        'success' => 'Pristup je uspješno prijavljen.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nijedna polja nisu promijenjena, tako da ništa nije ažurirano.',
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
