<?php

return [

    'invalid_category_type' => 'Kategorin måste vara en förbrukningsvarukategori.',
    'does_not_exist' => 'Förbrukningvaran existerar inte.',

    'create' => [
        'error' => 'Förbrukningsvaran kunde inte skapas. Vänligen försök igen.',
        'success' => 'Förbrukningsvara skapad.',
    ],

    'update' => [
        'error' => 'Förbrukningsvaran kunde inte uppdateras.Vänligen försök igen.',
        'success' => 'Förbruksningsvara uppdaterad.',
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill radera denna förbrukningsartiklar?',
        'error' => 'Kunde inte ta bort förbrukningsvaran. Vänligen försök igen.',
        'success' => 'Förbrukningsvaran raderad.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Förbrukningsvaran kunde inte checkas ut. Vänligen försök igen.',
        'success' => 'Förbrukningsvaran utcheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
        'unavailable' => 'Det finns inte tillräckligt med förbrukningsvaror för denna utcheckning. Vänligen kontrollera antalet kvar i lager. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Förbrukningsvaran kunde inte checkas in. Vänligen försök igen.',
        'success' => 'Förbrukningsvara incheckad.',
        'user_does_not_exist' => 'Användaren är ogiltig. Vänligen försök igen.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Inga fält ändrades, så ingenting uppdaterades.',
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
