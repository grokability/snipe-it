<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Ko tenei taputapu i tenei wa: ko nga nama kua taatatia ki nga kaiwhakamahi. Titiro koa ki nga taputapu me te ngana ano.',

    'create' => [
        'error' => 'Kaore i hangaia te taputapu, ngana ano ngana.',
        'success' => 'I angitu te waihanga i te taputapu.',
    ],

    'update' => [
        'error' => 'Kāore i te whakahouhia te taputapu, tēnā whakamātau anō',
        'success' => 'Kua pai te whakahoutanga o te taputapu.',
    ],

    'delete' => [
        'confirm' => 'Kei te hiahia koe ki te muku i tenei taputapu?',
        'error' => 'He raruraru kei te whakakore i te taputapu. Tena ngana ano.',
        'success' => 'Kua whakakorehia te taputapu.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Kaore ano kia uruhia te uru, ka ngana ano',
        'success' => 'He pai te tirotiro i te Accessory.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Kaore i te takiuruhia te Accessory, tēnā whakamātau anō',
        'success' => 'Whakaratohia te Accessory i te angitu.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Kaore i whakarereke nga mara, naore i whakahoutia.',
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
