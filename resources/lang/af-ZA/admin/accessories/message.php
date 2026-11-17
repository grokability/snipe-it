<?php

return [

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Hierdie bykomstige het tans: tel items wat uitgekontroleer is aan gebruikers. Kontroleer asseblief die bykomstighede en en probeer weer.',

    'create' => [
        'error' => 'Die bykomstigheid is nie geskep nie, probeer asseblief weer.',
        'success' => 'Die bykomstigheid is suksesvol geskep.',
    ],

    'update' => [
        'error' => 'Die bykomstigheid is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Die bykomstigheid is suksesvol opgedateer.',
    ],

    'delete' => [
        'confirm' => 'Is jy seker jy wil hierdie toebehore uitvee?',
        'error' => 'Daar was \'n probleem met die verwydering van die bykomstigheid. Probeer asseblief weer.',
        'success' => 'Die bykomstigheid is suksesvol verwyder.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Toebehore is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Toebehore suksesvol nagegaan.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Toebehore is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Toebehore is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Geen velde is verander nie, so niks is opgedateer nie.',
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
