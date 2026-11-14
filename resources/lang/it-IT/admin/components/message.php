<?php

return [

    'does_not_exist' => 'Il Componente non esiste.',

    'create' => [
        'error' => 'Il Componente non è stato creato, riprova.',
        'success' => 'Componente creato correttamente.',
    ],

    'update' => [
        'error' => 'Il Componente non è stato aggiornato, riprova',
        'success' => 'Componente aggiornato correttamente.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Sei sicuro di voler eliminare questo Componente?',
        'error' => 'Si è verificato un problema durante l\'eliminazione del componente. Riprova.',
        'success' => 'Il componente è stato eliminato correttamente.',
        'error_qty' => 'Alcuni componenti di questo tipo sono ancora assegnati. Effettuane la Restituzione e riprova.',
    ],

    'checkout' => [
        'error' => 'Il Componente non è stato assegnato, si prega di riprovare',
        'success' => 'Componente assegnato con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'unavailable' => 'Non ci sono abbastanza componenti: ne rimangono :remaining , ne servono :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nessun campo è stato modificato, quindi niente è stato aggiornato.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Componente non restituito, riprova',
        'success' => 'Componente restituito con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
    ],

];
