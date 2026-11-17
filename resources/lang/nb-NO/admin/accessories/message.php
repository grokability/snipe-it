<?php

return [

    'does_not_exist' => 'Leverandør finnes ikke.',
    'not_found' => 'Lisens ikke funnet',
    'assoc_users' => 'Denne modellen er tilknyttet en eller flere eiendeler og kan ikke slettes. Slett eiendelene, og prøv å slette modellen igjen. ',

    'create' => [
        'error' => 'Leverandør ble ikke opprettet. Prøv igjen.',
        'success' => 'Opprettelse av leverandør vellykket.',
    ],

    'update' => [
        'error' => 'Leverandør ble ikke oppdatert. Prøv igjen',
        'success' => 'Oppdatering av leverandør vellykket.',
    ],

    'delete' => [
        'confirm' => 'Er du sikker på at du vil slette denne leverandøren?',
        'error' => 'Det oppstod et problem under sletting av leverandør. Prøv igjen.',
        'success' => 'Sletting av leverandør vellykket.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Det oppstod et problem under utsjekk av lisens. Vennligst prøv igjen.',
        'success' => 'Vellykket utsjekk av lisens',
        'unavailable' => 'This seat is not available for checkout.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Det oppstod et problem under innsjekk av lisens. Vennligst prøv igjen.',
        'success' => 'Vellykket innsjekk av lisens',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
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
