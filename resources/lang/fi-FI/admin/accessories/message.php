<?php

return [

    'does_not_exist' => 'Oheistarviketta [:id] ei ole.',
    'not_found' => 'Tätä lisävarustetta ei löytynyt.',
    'assoc_users' => 'Oheistarviketta on tällä hetkellä luovutettuna :count nimikettä käyttäjille. Tarkista oheistarvikkeiden tila ja yritä uudelleen. ',

    'create' => [
        'error' => 'Oheistarviketta ei luotu, yritä uudelleen.',
        'success' => 'Oheistarvike on luotu.',
    ],

    'update' => [
        'error' => 'Oheistarviketta ei päivitetty, yritä uudelleen',
        'success' => 'Oheistarvike päivitettiin onnistuneesti.',
    ],

    'delete' => [
        'confirm' => 'Haluatko varmasti poistaa tämän oheistarvikkeen?',
        'error' => 'Oheistarvikkeen poistaminen ei onnistunut. Yritä uudelleen.',
        'success' => 'Oheistarvike poistettiin onnistuneesti.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Oheistarviketta ei luovutettu, yritä uudelleen',
        'success' => 'Oheistarvike luovutettiin onnistuneesti.',
        'unavailable' => 'Oheistarvike ei ole lainattavissa. Tarkista saatavilla oleva määrä',
        'user_does_not_exist' => 'Käyttäjä on virheellinen. Yritä uudelleen.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Oheistarviketta ei palautettu, yritä uudelleen',
        'success' => 'Oheistarvike palautettiin onnistuneesti.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Mitään kentistä ei ollut muutettu, joten mitään ei päivitetty.',
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
