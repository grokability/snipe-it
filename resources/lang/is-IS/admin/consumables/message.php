<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Consumable does not exist.',

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
        'user_does_not_exist' => 'Notandinn er ónothæfur. Vinsamlegast reyndu aftur.',
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
        'user_does_not_exist' => 'Þessi notandi er ónothæfur, vinsamlegast reyndu aftur.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Engum reitum var breytt, svo ekkert var uppfært.',
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
