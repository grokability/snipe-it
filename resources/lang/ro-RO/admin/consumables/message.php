<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Consumul nu există.',

    'create' => [
        'error' => 'Consumul nu a fost creat, încercați din nou.',
        'success' => 'Consumul a fost creat cu succes.',
    ],

    'update' => [
        'error' => 'Consumul nu a fost actualizat, încercați din nou',
        'success' => 'Consumul a fost actualizat cu succes.',
    ],

    'delete' => [
        'confirm' => 'Sigur doriți să ștergeți acest consumabil?',
        'error' => 'A apărut o problemă cu ștergerea consumabilelor. Vă rugăm să încercați din nou.',
        'success' => 'Consumul a fost șters cu succes.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Consumul nu a fost verificat, încercați din nou',
        'success' => 'Consumul a fost verificat cu succes.',
        'user_does_not_exist' => 'Acest utilizator este nevalid. Vă rugăm să încercați din nou.',
        'unavailable' => 'Nu sunt destule consumabile pentru această comandă de comandă. Vă rugăm să verificaţi cantitatea rămasă. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Consumul nu a fost verificat, încercați din nou',
        'success' => 'Consumul a fost verificat cu succes.',
        'user_does_not_exist' => 'Acest utilizator este nevalid. Vă rugăm să încercați din nou.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nu au fost modificate câmpuri, deci nimic nu a fost actualizat.',
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
