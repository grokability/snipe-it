<?php

return [

    'does_not_exist' => 'Piederums [:id] neeksistē.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Pašlaik šim piederumam ir: lietotāju skaits tiek pārbaudīts. Lūdzu, pārbaudiet piederumus un mēģiniet vēlreiz.',

    'create' => [
        'error' => 'Piederums netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Piederums tika veiksmīgi izveidots.',
    ],

    'update' => [
        'error' => 'Piederums netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piederums tika veiksmīgi atjaunināts.',
    ],

    'delete' => [
        'confirm' => 'Vai tiešām vēlaties dzēst šo piederumu?',
        'error' => 'Radās problēma, noņemot piederumu. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Piederums tika veiksmīgi izdzēsts.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Piederums netika pārbaudīts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piederums ir veiksmīgi izrakstīts.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Piederums netika atzīmēts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piederumi ir pārbaudīti veiksmīgi.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Neviens laukums netika mainīts, tāpēc nekas netika atjaunināts.',
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
