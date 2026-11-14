<?php

return [

    'invalid_category_type' => 'ប្រភេទត្រូវតែជាប្រភេទប្រើប្រាស់បាន។',
    'does_not_exist' => 'សម្ភារៈប្រើប្រាស់មិនមានទេ។',

    'create' => [
        'error' => 'Consumable was not created, please try again.',
        'success' => 'Consumable created successfully.',
    ],

    'update' => [
        'error' => 'Consumable was not updated, please try again',
        'success' => 'Consumable updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this consumable?',
        'error' => 'There was an issue deleting the consumable. Please try again.',
        'success' => 'The consumable was deleted successfully.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Consumable was not checked out, please try again',
        'success' => 'Consumable checked out successfully.',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
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
        'error' => 'Consumable was not checked in, please try again',
        'success' => 'Consumable checked in successfully.',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'គ្មាន fields ត្រូវបានផ្លាស់ប្តូរ ដូច្នេះគ្មានអ្វីត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ។',
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
