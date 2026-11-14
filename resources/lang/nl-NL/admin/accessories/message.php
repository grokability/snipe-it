<?php

return [

    'does_not_exist' => 'Het accessoire [:id] bestaat niet.',
    'not_found' => 'Dat accessoire werd niet gevonden.',
    'assoc_users' => 'Dit accessoire heeft op dit moment :count items uitgecheckt aan gebruikers. Check het accessoire in en probeer het opnieuw. ',

    'create' => [
        'error' => 'Het accessoire is niet aangemaakt. Probeer het opnieuw.',
        'success' => 'Het accessoire is aangemaakt.',
    ],

    'update' => [
        'error' => 'Het accessoire is niet bijgewerkt. Probeer het opnieuw.',
        'success' => 'Het accessoire is bijgewerkt.',
    ],

    'delete' => [
        'confirm' => 'Weet je zeker dat je dit accessoire wilt verwijderen?',
        'error' => 'Er is een probleem opgetreden bij het verwijderen van het accessoire. Probeer het opnieuw.',
        'success' => 'Het accessoire is verwijderd.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Het accessoire is niet uitgecheckt. Probeer het opnieuw.',
        'success' => 'Het accessoire is uitgecheckt.',
        'unavailable' => 'Accessoire kan niet worden uitgegeven. Controleer de beschikbare hoeveelheid',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
        'checkout_qty' => [
            'lte' => 'Er is momenteel slechts één beschikbaar accessoire van dit type en u probeert :checkout_qty uit te checken. Pas het uitcheck aantal of de totale voorraad van dit accessoire aan en probeer het opnieuw.|Er zijn :number_currently_remaining totaal beschikbare accessoires en u probeert :checkout_qty uit te checken. Pas het uitcheck aantal of de totale voorraad van dit accessoire aan en probeer het opnieuw.',
        ],

    ],

    'checkin' => [
        'error' => 'Het accessoire is niet ingecheckt. Probeer het opnieuw.',
        'success' => 'Het accessoire is ingecheckt.',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Er was geen veld geselecteerd dus is er niks gewijzigd.',
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
