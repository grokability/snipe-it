<?php

return [

    'does_not_exist' => 'Ang aksesoryang [:id] ay hindi umiiral.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Ang aksesoryang ito ay kasalukuyang mayroong :pag-check out sa pag-kwenta ng mga aytem sa mga gumagamit o user. Paki-tingnan sa mga aksesorya at subukang muli. ',

    'create' => [
        'error' => 'Ang aksesorya ay hindi naisagawa, mangyaring subukan muli.',
        'success' => 'Ang aksesorya ay matagumpay na nailikha.',
    ],

    'update' => [
        'error' => 'Ang aksesorya ay hindi nai-update, mangyaring subukang muli',
        'success' => 'Ang aksesorya ay matagumpay na nai-upadate.',
    ],

    'delete' => [
        'confirm' => 'Sigurado kaba na gusto mong i-delete ang aksesoryang ito?',
        'error' => 'Mayroong isyu sa pagdelete ng aksesorya. Mangyaring subukang muli.',
        'success' => 'Ang aksesorya ay matagumpay na nai-delete.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Ang aksesorya ay hindi na-check out, mangyaring subukang muli',
        'success' => 'Ang aksesorya ay matagumoay na nai-check out.',
        'unavailable' => 'Ang aksesorya ay hindi maaaring i-check out. Suriing mabuti ang natitirang bilang',
        'user_does_not_exist' => 'Ang user na iyon ay hindi tama. Mangyaring subukang muli.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Ang aksesorya ay hindi nai-check in, mangyaring subukang muli',
        'success' => 'Ang aksesorya ay matagumpay na nai-check in.',
        'user_does_not_exist' => 'Ang ang user na iyon ay hindi tama. Mangyaring subukang muli.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Walang nabagong mga field, kaya walang nai-update.',
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
