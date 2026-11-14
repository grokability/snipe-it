<?php

return [

    'does_not_exist' => 'Component bestaat niet.',

    'create' => [
        'error' => 'Component is niet aangemaakt, probeer het nogmaals.',
        'success' => 'Component succesvol aangemaakt.',
    ],

    'update' => [
        'error' => 'Component is niet bijgewerkt, probeer het nogmaals.',
        'success' => 'Component succesvol bijgewerkt.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Weet je zeker dat je dit component wil verwijderen?',
        'error' => 'Er ging iets mis bij het verwijderen van het component. Probeer het nogmaals.',
        'success' => 'Component succesvol verwijderd.',
        'error_qty' => 'Sommige componenten van dit type zijn nog steeds uitgecheckt. Check ze in en probeer het opnieuw.',
    ],

    'checkout' => [
        'error' => 'Component is niet uitgecheckt, probeer het nogmaals',
        'success' => 'Component succesvol uitgecheckt.',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
        'unavailable' => 'Er zijn niet genoeg componenten over: :resterend, :requested gevraagd ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Er was geen veld geselecteerd dus is er niks gewijzigd.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Component is niet ingecheckt, probeer het nogmaals',
        'success' => 'Component succesvol ingecheckt.',
        'user_does_not_exist' => 'Deze gebruiker is ongeldig. Probeer het opnieuw.',
    ],

];
