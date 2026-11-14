<?php

return [

    'does_not_exist' => 'Takový díl neexistuje.',

    'create' => [
        'error' => 'Nepodařilo se vytvořit díl, zkuste to prosím znovu.',
        'success' => 'Díl byl v pořádku vytvořen.',
    ],

    'update' => [
        'error' => 'Nepodařilo se upravit díl, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku upraven.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Opravdu si přejete odstranit tento díl?',
        'error' => 'Nepodařilo se díl odstranit. Zkuste to prosím později.',
        'success' => 'Díl byl v pořádku odstraněn.',
        'error_qty' => 'Některé komponenty tohoto typu jsou stále vydané. Nejprve je prosím vraťte a poté akci opakujte.',
    ],

    'checkout' => [
        'error' => 'Díl se nepodařilo předat, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku předán.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.',
        'unavailable' => 'Nedostatek součástí: :remaining zbývají, :requested vyžádaných.',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Žádné pole nebyly změněny, takže nic nebylo aktualizováno.',
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
        'error' => 'Díl se nepodařilo převzít, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku převzat.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.',
    ],

];
