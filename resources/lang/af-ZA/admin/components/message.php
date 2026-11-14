<?php

return [

    'does_not_exist' => 'Komponent bestaan ​​nie.',

    'create' => [
        'error' => 'Komponent is nie geskep nie, probeer asseblief weer.',
        'success' => 'Komponent geskep suksesvol.',
    ],

    'update' => [
        'error' => 'Komponent is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Komponent suksesvol opgedateer.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Is jy seker jy wil hierdie komponent uitvee?',
        'error' => 'Daar was \'n probleem met die verwydering van die komponent. Probeer asseblief weer.',
        'success' => 'Die komponent is suksesvol verwyder.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponent is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Komponent is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Geen velde is verander nie, so niks is opgedateer nie.',
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
        'error' => 'Komponent is nie nagegaan nie, probeer asseblief weer',
        'success' => 'Komponent is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
    ],

];
