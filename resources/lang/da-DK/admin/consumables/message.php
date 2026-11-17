<?php

return [

    'invalid_category_type' => 'Kategorien skal være en forbrugsvarekategori.',
    'does_not_exist' => 'Forbrugsstoffer findes ikke.',

    'create' => [
        'error' => 'Forbruget er ikke oprettet, prøv igen.',
        'success' => 'Forbrugsvarer oprettes med succes.',
    ],

    'update' => [
        'error' => 'Forbruget blev ikke opdateret, prøv igen',
        'success' => 'Forbrugsstoffer opdateret med succes.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på, at du ønsker at slette denne forbrugsstof?',
        'error' => 'Der opstod et problem ved at slette forbruget. Prøv igen.',
        'success' => 'Forbruget blev slettet.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Forbruget blev ikke tjekket ud, prøv igen',
        'success' => 'Forbrugsmaterialet tjekket ud med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
        'unavailable' => 'Der er ikke nok forbrugsvarer til denne kasse. Tjek venligst antallet til venstre. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Forbruget blev ikke tjekket ind, prøv igen',
        'success' => 'Forbrugsstoffer kontrolleres med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ingen felter blev ændret, så intet er blevet opdateret.',
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
