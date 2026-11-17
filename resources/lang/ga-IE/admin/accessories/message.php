<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Tá an accessory seo faoi láthair: cuntais a sheiceáil le húsáideoirí. Seiceáil na gabhálais agus déan iarracht arís.',

    'create' => [
        'error' => 'Níor cruthaíodh an accessory, déan iarracht arís.',
        'success' => 'Rinneadh an cúlpháirtí a chruthú go rathúil.',
    ],

    'update' => [
        'error' => 'Níor nuashonraíodh an accessory, déan iarracht arís',
        'success' => 'Tugadh suas chun dáta an cúlpháirtí go rathúil',
    ],

    'delete' => [
        'confirm' => 'An bhfuil tú cinnte gur mian leat an accessory seo a scriosadh?',
        'error' => 'Bhí ceist ann a scriosadh an accessory. Arís, le d\'thoil.',
        'success' => 'Scriosadh an cúlpháirtí go rathúil.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Níor seiceáladh an Cúntóir amach, déan iarracht arís',
        'success' => 'Rinne an cúntóir a sheiceáil go rathúil.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Níor seiceáladh an cúntóir isteach, déan iarracht arís',
        'success' => 'Faireacán seiceáil go rathúil.',
        'user_does_not_exist' => 'Tá an úsáideoir neamhbhailí. Arís, le d\'thoil.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Níor athraíodh aon réimsí, mar sin níor nuashonraíodh aon rud.',
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
