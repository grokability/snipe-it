<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Forbruksvaren finnes ikke.',

    'create' => [
        'error' => 'Forbruksvaren bli ikke opprettet, prøv igjen.',
        'success' => 'Forbruksvaren opprettet.',
    ],

    'update' => [
        'error' => 'Forbruksvaren ble ikke opprettet, prøv igjen',
        'success' => 'Forbruksvare oppdatert.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på at forbruksmateriellet skal slettes?',
        'error' => 'Noe gikk feil under sletting av forbruksvaren. Prøv igjen.',
        'success' => 'Forbruksmateriell ble slettet.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Forbruksvaren ble ikke tatt ut, prøv igjen',
        'success' => 'Forbruksvaren ble tatt ut.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
        'unavailable' => 'Det er ikke nok forbruksvarer for denne utsjekken. Kontroller antallet igjen. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Forbruksvaren ble ikke lagt inn, prøv igjen',
        'success' => 'Forbruksvaren ble tatt ut.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ingen felt ble endret, så ingenting ble oppdatert.',
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
