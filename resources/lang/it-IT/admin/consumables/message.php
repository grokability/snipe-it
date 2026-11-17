<?php

return [

    'invalid_category_type' => 'La categoria deve essere consumabile.',
    'does_not_exist' => 'Il consumabile non esiste.',

    'create' => [
        'error' => 'Il consumabile non è stato creato, prova di nuovo.',
        'success' => 'Il consumabile è stato creato con successo.',
    ],

    'update' => [
        'error' => 'Il consumabile non è stato aggiornato, prova di nuovo',
        'success' => 'Il consumabile è stato aggiornato con successo.',
    ],

    'delete' => [
        'confirm' => 'Sei sicuro di voler cancellare questo consumabile?',
        'error' => 'C\'è stato un problema durante l\'eliminazione del consumabile. Prova di nuovo.',
        'success' => 'Il consumabile è stato cancellato con successo.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Il Consumabile non è stato assegnato, prova di nuovo',
        'success' => 'Il Consumabile è stato assegnato con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'unavailable' => 'Non ci sono abbastanza consumabili per questa assegnazione. Controlla la quantità rimasta. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Il consumabile non è stato restituito, riprova',
        'success' => 'Consumabile restituito con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nessun campo è stato modificato, quindi niente è stato aggiornato.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
