<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Verbruiksgoedere bestaan ​​nie.',

    'create' => [
        'error' => 'Verbruiksgoedere is nie geskep nie, probeer asseblief weer.',
        'success' => 'Verbruiker geskep suksesvol.',
    ],

    'update' => [
        'error' => 'Verbruiksgoedere is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Verbruiksgoedere suksesvol opgedateer.',
    ],

    'delete' => [
        'confirm' => 'Is jy seker jy wil hierdie verbruiksgoed uitvee?',
        'error' => 'Daar was \'n probleem met die verwydering van die verbruik. Probeer asseblief weer.',
        'success' => 'Die verbruiksgoed is suksesvol verwyder.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Verbruiksgoedere is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Verbruiksgoedere is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Verbruiksgoedere is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Verbruiker word suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Geen velde is verander nie, so niks is opgedateer nie.',
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
