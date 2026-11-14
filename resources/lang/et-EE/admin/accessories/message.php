<?php

return [

    'does_not_exist' => 'Tarvik [:id] ei eksisteeri.',
    'not_found' => 'Seda tarvikut ei leitud.',
    'assoc_users' => 'Seda tarvikut on praegu :count väljastatud kasutajatele. Palun kontrollige tarvikuid ja proovige uuesti. ',

    'create' => [
        'error' => 'Tarvikut ei loodud, proovige uuesti.',
        'success' => 'See tarvik loodi edukalt.',
    ],

    'update' => [
        'error' => 'Tarvikut ei uuendatud. Proovige uuesti',
        'success' => 'Tarvikut uuendati edukalt.',
    ],

    'delete' => [
        'confirm' => 'Kas olete kindel, et soovite seda tarvikut kustutada?',
        'error' => 'Tarvikut ei õnnestunud kustutada. Palun proovi uuesti.',
        'success' => 'Lisaseade kustutati edukalt.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Lisatarvikut ei kontrollitud, palun proovige uuesti',
        'success' => 'Lisaseade edukalt kontrollitud.',
        'unavailable' => 'Tarvik ei ole väljastamiseks saadaval. Kontrolli laoseisu',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
        'checkout_qty' => [
            'lte' => 'Praegu on saadaval ainult üks seda tüüpi lisaseade ja te üritate väljastada :checkout_qty. Palun kohandage väljastatavate lisaseadmete kogust või selle lisaseadme koguarvu ja proovige uuesti.Saadaval on :number_currently_remaining lisaseadet ja te üritate väljastada :checkout_qty. Palun kohandage väljastavate lisaseadmete kogust või selle lisaseadme koguarvu ja proovige uuesti.',
        ],

    ],

    'checkin' => [
        'error' => 'Lisatarvikut ei olnud märgitud, palun proovige uuesti',
        'success' => 'Lisaseade kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
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
