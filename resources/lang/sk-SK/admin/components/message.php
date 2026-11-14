<?php

return [

    'does_not_exist' => 'Komponent neexistuje.',

    'create' => [
        'error' => 'Komponent sa nepodarilo pridať, skúste znovu.',
        'success' => 'Komponent bol úspešne pridaný.',
    ],

    'update' => [
        'error' => 'Komponent sa nepodarilo upraviť, skúste znovu',
        'success' => 'Komponent bol úspešne upravený.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete zmazať tento komponent?',
        'error' => 'Nastal problém pri mazani komponentu. Prosím skúste znovu.',
        'success' => 'Komponent bol úspešne zmazaný.',
        'error_qty' => 'Niektoré komponenty tohto typu sú stále odovzdané. Prosím prevezmite ich a skúste to znova.',
    ],

    'checkout' => [
        'error' => 'Komponent nebol priradený, prosím skúste znovu',
        'success' => 'Komponent bol úspešne priradený.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'unavailable' => 'Nedostatok komponentov: :remaining skladom, :requested vyžiadaných ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
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
        'error' => 'Komponent nebol prevzatý, skúste znovu',
        'success' => 'Komponent bol úspešne prevzatý.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
    ],

];
