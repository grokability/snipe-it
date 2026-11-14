<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Níl inbhuanaithe ann.',

    'create' => [
        'error' => 'Níor cruthaíodh inbhuanaithe, déan iarracht arís.',
        'success' => 'Inbhuanaithe a cruthaíodh go rathúil.',
    ],

    'update' => [
        'error' => 'Níor nuashonraíodh inmhuirearaithe, déan iarracht arís',
        'success' => 'Nuashonraithe go rathúil.',
    ],

    'delete' => [
        'confirm' => 'An bhfuil tú cinnte gur mian leat an t-inchaite seo a scriosadh?',
        'error' => 'Bhí ceist ann a scriosadh an inchaite. Arís, le d\'thoil.',
        'success' => 'Scriosadh an inchaite go rathúil.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Níor sheiceáil amach an méid inmhuirearaithe, déan iarracht arís',
        'success' => 'Seiceáil inbhuanaithe go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
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
        'error' => 'Níor rinneadh seiceáil inmhuirearaithe, déan iarracht arís',
        'success' => 'Seiceáil inbhuanaithe go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Níor athraíodh aon réimsí, mar sin níor nuashonraíodh aon rud.',
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
