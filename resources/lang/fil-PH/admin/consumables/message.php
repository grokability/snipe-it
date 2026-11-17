<?php

return [

    'invalid_category_type' => 'Ang kategorya ay dapat nasa kategorya ng consumable.',
    'does_not_exist' => 'Ang consumable ay hindi umiiral.',

    'create' => [
        'error' => 'Ang consumable ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Ang consumable ay matagumpay na nai-likha.',
    ],

    'update' => [
        'error' => 'Ang consumable ay hindi nai-update, manyaring subukang muli',
        'success' => 'Ang consumable ay matagumpay na nai-update.',
    ],

    'delete' => [
        'confirm' => 'Sigurado kaba na i-delete mo ang consumable na ito?',
        'error' => 'Mayroong isyu sa pag-delete ng consumable. Mangayaring subukang muli.',
        'success' => 'Ang consumable na ito ay matagumpay na nai-delete.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Ang consumable ay hindi nai-check out, mangyaring subukang muli',
        'success' => 'Ang consumable ay matagumpay na nai-check out.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
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
        'error' => 'Ang consumable ay hindi nai-check in, mangyaring subukang muli',
        'success' => 'Ang consumable ay matagumpay na nai-check in.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Walang nabagong mga field, kaya walang nai-update.',
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
