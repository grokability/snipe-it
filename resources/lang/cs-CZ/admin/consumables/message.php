<?php

return [

    'invalid_category_type' => 'Kategorie musí být typu spotřební materiál.',
    'does_not_exist' => 'Spotřební materiál nebyl nalezen.',

    'create' => [
        'error' => 'Spotřební materiál se nepodařilo vytvořit, pokuste se o to znovu.',
        'success' => 'Spotřební materiál byl úspěšně vytvořen.',
    ],

    'update' => [
        'error' => 'Spotřební materiál se nepodařilo upravit, pokuste se o to znovu',
        'success' => 'Spotřební materiál byl v pořádku upraven.',
    ],

    'delete' => [
        'confirm' => 'Opravdu si přejete odstranit tento spotřební materiál?',
        'error' => 'Nepodařilo se nám spotřební materiál odstranit. Zkuste to prosím znovu.',
        'success' => 'Spotřební materiál byl v pořádku odstraněn.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Spotřební materiál se nepodařilo přidělit, pokuste se o to znovu',
        'success' => 'Spotřební materiál byl v pořádku přidělen.',
        'user_does_not_exist' => 'Tento uživatel není platný, zkuste to prosím jinak.',
        'unavailable' => 'Nedostatek spotřebního materiálu k výdeji. Zkontrolujte skladové zásoby.',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Spotřební materiál se nepodařilo převzít, pokuste se o to znovu',
        'success' => 'Spotřební materiál byl v pořádku převzat.',
        'user_does_not_exist' => 'Tento uživatel není platný, zkuste to prosím jinak.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Žádné pole nebyly změněny, takže nic nebylo aktualizováno.',
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
