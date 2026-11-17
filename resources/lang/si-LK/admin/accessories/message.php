<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'This accessory currently has :count items checked out to users. Please check in the accessories and and try again. ',

    'create' => [
        'error' => 'The accessory was not created, please try again.',
        'success' => 'The accessory was successfully created.',
    ],

    'update' => [
        'error' => 'The accessory was not updated, please try again',
        'success' => 'The accessory was updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this accessory?',
        'error' => 'There was an issue deleting the accessory. Please try again.',
        'success' => 'The accessory was deleted successfully.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Accessory was not checked out, please try again',
        'success' => 'Accessory checked out successfully.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Accessory was not checked in, please try again',
        'success' => 'Accessory checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'No fields were changed, so nothing was updated.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
