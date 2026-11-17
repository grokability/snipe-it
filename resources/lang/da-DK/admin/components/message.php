<?php

return [

    'does_not_exist' => 'Komponent eksisterer ikke.',

    'create' => [
        'error' => 'Komponent blev ikke oprettet, prøv igen.',
        'success' => 'Komponent oprettet med succes.',
    ],

    'update' => [
        'error' => 'Komponent blev ikke opdateret, prøv igen',
        'success' => 'Komponent opdateret med succes.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på, at du vil slette denne komponent?',
        'error' => 'Der opstod et problem ved at slette komponenten. Prøv igen.',
        'success' => 'Komponenten blev slettet korrekt.',
        'error_qty' => 'Nogle komponenter af denne type er stadig låst. Tjek dem ind, og forsøg igen.',
    ],

    'checkout' => [
        'error' => 'Komponent blev ikke tjekket ud, prøv igen',
        'success' => 'Komponent tjekket ud med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
        'unavailable' => 'Ikke nok komponenter tilbage: :remaining remaining :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ingen felter blev ændret, så intet er blevet opdateret.',
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
        'error' => 'Komponent blev ikke tjekket ind, prøv igen',
        'success' => 'Komponent tjekket ind med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
    ],

];
