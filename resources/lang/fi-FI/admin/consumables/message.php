<?php

return [

    'invalid_category_type' => 'Kategorian on oltava kulutustarvikkeen kategoria.',
    'does_not_exist' => 'Kulutustarviketta ei ole.',

    'create' => [
        'error' => 'Kulutustarviketta ei luotu, yritä uudelleen.',
        'success' => 'Kulutustarvike luotiin onnistuneesti.',
    ],

    'update' => [
        'error' => 'Kulutustarviketta ei päivitetty, yritä uudelleen',
        'success' => 'Kulutustarvike päivitettiin onnistuneesti.',
    ],

    'delete' => [
        'confirm' => 'Haluatko varmasti poistaa tämän kulutustarvikkeen?',
        'error' => 'Kulutustarvikkeen poistamisessa oli ongelma. Yritä uudelleen.',
        'success' => 'Kulutustarvike poistettiin onnistuneesti.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Kulutustarviketta ei luovutettu, yritä uudelleen',
        'success' => 'Kulutustarvike luovutettiin.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
        'unavailable' => 'Ei riittävästi kulutustarvikkeita tätä lainausta varten. Tarkista jäljellä oleva määrä. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Kulutustarviketta ei palautettu, yritä uudelleen',
        'success' => 'Kulutustarvike palautettiin.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Mitään kentistä ei ollut muutettu, joten mitään ei päivitetty.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
