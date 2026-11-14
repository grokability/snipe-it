<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Þessi aukabúnaður er nú þegar: count items sem skráð eru á notendur. Vinsamlegast skráðu inn aukabúnaðinn og reyndu aftur! ',

    'create' => [
        'error' => 'Aukabúnaðurinn var ekki skráður, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaðurinn var skráður.',
    ],

    'update' => [
        'error' => 'Aukabúnaðurinn var ekki uppfærður, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaðurinn var uppfærður.',
    ],

    'delete' => [
        'confirm' => 'Viltu örugglega eyða þessum tiltekna aukabúnaði?',
        'error' => 'Það var eitthvað smá vesen að eyða aukabúnaðinum, vinsamlegast reyndu aftur.',
        'success' => 'Aukabúnaðinum var eytt.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Aukabúnaður fór ekki í úttekt, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaður fór í úttekt.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Notandinn er ónothæfur. Vinsamlegast reyndu aftur.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Aukabúnaði var ekki skilað, vinsamlegast reyndu aftur!',
        'success' => 'Aukabúnaði var skilað inn.',
        'user_does_not_exist' => 'Þessi notandi er ónothæfur, vinsamlegast reyndu aftur.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Engum reitum var breytt, svo ekkert var uppfært.',
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
