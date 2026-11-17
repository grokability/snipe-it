<?php

return [

    'does_not_exist' => 'Tilbehøret [:id] finnes ikke.',
    'not_found' => 'Finner ikke dette tilbehøret.',
    'assoc_users' => 'Dette tilbehøret har for øyeblikket :count enheter utsjekket til brukere. Sjekk inn tilbehøret og prøv igjen. ',

    'create' => [
        'error' => 'Tilbehør ble ikke opprettet, vennligst prøv igjen.',
        'success' => 'Tilbehør optrettet.',
    ],

    'update' => [
        'error' => 'Tilbehør ble ikke oppdatert, vennligst prøv igjen',
        'success' => 'Tilbehør oppdatert.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på at du vil slette dette tilbehøret?',
        'error' => 'Det oppstod et problem under sletting av tilbehøret. Prøv igjen.',
        'success' => 'Tilbehøret ble slettet.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Tilbehør ble ikke sjekket ut. Prøv igjen',
        'success' => 'Vellykket utsjekking av tilbehør.',
        'unavailable' => 'Tilbehør er ikke tilgjengelig for utsjekk. Sjekk antall tilgjengelig',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Tilbehør ble ikke sjekket inn. Prøv igjen',
        'success' => 'Vellykket innsjekk av tilbehør.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Prøv igjen.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ingen felt ble endret, så ingenting ble oppdatert.',
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
