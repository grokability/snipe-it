<?php

return [

    'does_not_exist' => 'Komponenten existerar inte.',

    'create' => [
        'error' => 'Komponenten kunde inte skapas. Vänligen försök igen.',
        'success' => 'Komponent skapad.',
    ],

    'update' => [
        'error' => 'Komponenten kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Komponent uppdaterad.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill radera den här komponenten?',
        'error' => 'Kunde inte ta bort komponenten. Vänligen försök igen.',
        'success' => 'Komponent raderad.',
        'error_qty' => 'Vissa komponenter av denna typ är fortfarande utcheckade. Vänligen checka in dem och försök igen.',
    ],

    'checkout' => [
        'error' => 'Komponenten kunde inte checkas. Vänligen försök igen.',
        'success' => 'Komponent utcheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
        'unavailable' => 'Inte tillräckligt med komponenter kvar: :remaining kvar, :requested efterfrågat ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Inga fält ändrades, så ingenting uppdaterades.',
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
        'error' => 'Komponenten kunde inte checkas in. Vänligen försök igen.',
        'success' => 'Komponent incheckad.',
        'user_does_not_exist' => 'Den användaren är ogiltig. Vänligen försök igen.',
    ],

];
