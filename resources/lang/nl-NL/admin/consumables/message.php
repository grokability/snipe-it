<?php

return [

    'invalid_category_type' => 'Deze categorie moet een verbruik categorie zijn.',
    'does_not_exist' => 'Verbruiksartikel bestaat niet.',

    'create' => [
        'error' => 'Verbruiksartikel is niet aangemaakt, probeer het opnieuw.',
        'success' => 'Verbruiksartikel is met succes aangemaakt.',
    ],

    'update' => [
        'error' => 'Verbruiksartikel is niet gewijzigd, probeer het opnieuw',
        'success' => 'Verbruiksartikel is met succes gewijzigd.',
    ],

    'delete' => [
        'confirm' => 'Weet je het zeker dat je deze verbruiksartikel wilt verwijderen?',
        'error' => 'Er was een probleem met het verwijderen van dit verbruiksartikel. Probeer het opnieuw.',
        'success' => 'Het verbruiksartikel is met succes verwijderd.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Verbruiksartikel is niet uitgecheckt, probeer het opnieuw',
        'success' => 'Verbruiksartikel is met succes uitgecheckt.',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
        'unavailable' => 'Er zijn niet genoeg verbruiksartikelen om uit te checken. Controleer de resterende hoeveelheid. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Verbruiksartikel is niet ingecheckt, probeer het opnieuw',
        'success' => 'Verbruiksartikel is met succes ingecheckt.',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Er was geen veld geselecteerd dus is er niks gewijzigd.',
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
