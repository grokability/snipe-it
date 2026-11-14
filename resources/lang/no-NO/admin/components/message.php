<?php

return [

    'does_not_exist' => 'Komponenten finnes ikke.',

    'create' => [
        'error' => 'Komponenten ble ikke opprettet, vennligst prøv igjen.',
        'success' => 'Komponent ble opprettet.',
    ],

    'update' => [
        'error' => 'Komponenten ble ikke oppdatert. Vennligst prøv igjen',
        'success' => 'Komponent ble oppdatert.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på at du vil slette denne komponenten?',
        'error' => 'Det oppstod et problem under sletting av komponenten. Vennligst prøv igjen.',
        'success' => 'Sletting av komponent vellykket.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponent ble ikke sjekket ut. Prøv igjen',
        'success' => 'Vellykket utsjekk av komponent.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'unavailable' => 'Ikke nok komponenter igjen: :remaining gjenværende, :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ingen felt ble endret, så ingenting ble oppdatert.',
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
        'error' => 'Komponenten ble ikke sjekket inn, vennligst prøv igjen',
        'success' => 'Vellykket innsjekk av komponent.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
    ],

];
