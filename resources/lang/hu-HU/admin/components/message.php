<?php

return [

    'does_not_exist' => 'Az alkatrész nem létezik.',

    'create' => [
        'error' => 'Összetevő nem jött létre, próbálkozz újra.',
        'success' => 'Az alkatrész sikeresen létrejött.',
    ],

    'update' => [
        'error' => 'Az alkatrész nem frissült, próbálkozz újra',
        'success' => 'Az alkatrész sikeresen létrejött.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Biztosan törölni szeretnéd az alkatrészt?',
        'error' => 'Probléma támadt a alkatrész törlésével. Próbálkozzon újra.',
        'success' => 'Az alkatrész sikeresen törlődött.',
        'error_qty' => 'Néhány ilyen típusú alkatrész még ki van adva. Kérjük, előbb végezze el azok visszavételét, majd próbálja újra.',
    ],

    'checkout' => [
        'error' => 'Az alkatrész nem lett kiadva, próbálkozz újra',
        'success' => 'Az alkatrész sikeresen kiadva.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.',
        'unavailable' => 'Nem marad elég alkatrész: :remaining marad, :requested igényelve ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nincsenek mezők megváltoztak, így semmi sem frissült.',
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
        'error' => 'Az alkatrész nem lett visszavéve, próbálkozz újra',
        'success' => 'Az alkatrész sikeresen visszavéve.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.',
    ],

];
