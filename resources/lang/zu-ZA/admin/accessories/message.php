<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Lesi sesekeli samanje sino: ukubala izinto ezihlolwe kubasebenzisi. Sicela uhlole izesekeli bese uzama futhi.',

    'create' => [
        'error' => 'Impahla ayidalwanga, sicela uzame futhi.',
        'success' => 'I-accessory yenziwe ngempumelelo.',
    ],

    'update' => [
        'error' => 'Isesekeli asizange sibuyekezwe, sicela uzame futhi',
        'success' => 'I-accessory ibuyekezwe ngempumelelo.',
    ],

    'delete' => [
        'confirm' => 'Ingabe uqinisekile ukuthi ufisa ukususa le ndawo yokufinyelela?',
        'error' => 'Kube nenkinga yokususa i-accessory. Ngicela uzame futhi.',
        'success' => 'Isesekeli sisusiwe ngempumelelo.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Ukufinyelela akuzange kuhlolwe, sicela uzame futhi',
        'success' => 'Ukufinyelela kufakwe ngempumelelo.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Ukufinyelela akuzange kungenwe, sicela uzame futhi',
        'success' => 'Okufinyeleleka kuhlolwe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Azikho amasimu ashintshiwe, ngakho akukho lutho olubuyekeziwe.',
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
