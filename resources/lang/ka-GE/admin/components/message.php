<?php

return [

    'does_not_exist' => 'Component does not exist.',

    'create' => [
        'error' => 'Component was not created, please try again.',
        'success' => 'Component created successfully.',
    ],

    'update' => [
        'error' => 'Component was not updated, please try again',
        'success' => 'Component updated successfully.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this component?',
        'error' => 'There was an issue deleting the component. Please try again.',
        'success' => 'The component was deleted successfully.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Component was not checked out, please try again',
        'success' => 'Component checked out successfully.',
        'user_does_not_exist' => 'მომხმარებელი არ არსებობს ან არავალიდურია. გთხოვთ ხელახლა სცადოთ.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'No fields were changed, so nothing was updated.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Component was not checked in, please try again',
        'success' => 'Component checked in successfully.',
        'user_does_not_exist' => 'მომხმარებელი არ არსებობს ან არავალიდურია. გთხოვთ ხელახლა სცადოთ.',
    ],

];
