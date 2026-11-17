<?php

return [

    'does_not_exist' => 'L\'accessorio [:id] non esiste.',
    'not_found' => 'Questo accessorio non è stato trovato.',
    'assoc_users' => 'Questo Accessorio ha attualmente :count articoli assegnati agli utenti. Si prega di ritirare gli Accessori e riprovare. ',

    'create' => [
        'error' => 'L\'Accessorio non è stato creato, si prega di riprovare.',
        'success' => 'Accessorio creato con successo.',
    ],

    'update' => [
        'error' => 'L\'Accessorio non è stato aggiornato, si prega di riprovare',
        'success' => 'Accessorio aggiornato con successo.',
    ],

    'delete' => [
        'confirm' => 'Sei sicuro di voler eliminare quest\'Accessorio?',
        'error' => 'Si è verificato un problema cercando di eliminare l\'accessorio. Si prega di riprovare.',
        'success' => 'L\'accessorio è stato eliminato con successo.',
        'bulk_success' => 'Accessorio eliminato con successo.|:count accessori eliminati con successo.',
        'partial_success' => ':count accessorio eliminato con successo, ma altri non possono essere eliminati. Vedi sotto per dettagli.|:count accessori eliminati con successo, ma altri non possono essere eliminati. Vedi sotto per dettagli.',
    ],

    'checkout' => [
        'error' => 'L\'accessorio non è stato assegnato, si prega di riprovare',
        'success' => 'Accessorio assegnato correttamente.',
        'unavailable' => 'Accessorio non disponibile per l\'assegnazione. Controlla la quantità disponibile',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'checkout_qty' => [
            'lte' => 'Al momento c\'è solo un accessorio disponibile di questo tipo, ma si sta cercando di assegnarne :checkout_qty. Si prega di modificare la quantità da assegnare oppure la quantità totale in magazzino di questo accessorio e poi riprovare.|Ci sono :number_currently_remaining accessori disponibili in magazzino, ma si sta cercando di assegnarne :checkout_qty. Si prega di regolare la quantità da assegnare oppure la quantità totale in magazzino di questo accessorio e poi riprovare.',
        ],

    ],

    'checkin' => [
        'error' => 'L\'accessorio non è stato restituito correttamente, riprova',
        'success' => 'Accessorio restituito con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nessun campo è stato modificato, quindi niente è stato aggiornato.',
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
