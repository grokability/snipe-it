<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Patērējamais nav.',

    'create' => [
        'error' => 'Patērējamais nav izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Izlietojamie tika veiksmīgi izveidoti.',
    ],

    'update' => [
        'error' => 'Patērējamais nav atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Izlietojamie tiek veiksmīgi atjaunināti.',
    ],

    'delete' => [
        'confirm' => 'Vai tiešām vēlaties izdzēst šo patērējamo?',
        'error' => 'Radās problēma, izdzēšot patērējamo. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Palīgmateriāls tika veiksmīgi dzēsts.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Neizdevās izņemt patēriņu, lūdzu, mēģiniet vēlreiz',
        'success' => 'Izlietojamie izrakstīti veiksmīgi.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
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
        'error' => 'Paredzētais patēriņš netika reģistrēts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Izlietojamie tiek veiksmīgi reģistrēti.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Neviens laukums netika mainīts, tāpēc nekas netika atjaunināts.',
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
