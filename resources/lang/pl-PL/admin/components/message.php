<?php

return [

    'does_not_exist' => 'Składnik nie istnieje.',

    'create' => [
        'error' => 'Składnik nie został utworzony, spróbuj ponownie.',
        'success' => 'Składnik został utworzony pomyślnie.',
    ],

    'update' => [
        'error' => 'Składnik nie został uaktualniony, spróbuj ponownie',
        'success' => 'Składnik został zaktualizowany pomyślnie.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Czy na pewno chcesz usunąć ten składnik?',
        'error' => 'Wystąpił problem podczas usuwania składnika. Spróbuj ponownie.',
        'success' => 'Składnik został usunięty pomyślnie.',
        'error_qty' => 'Część komponentów tego typu jest nadal wydana. Sprawdź je i spróbuj ponownie.',
    ],

    'checkout' => [
        'error' => 'Składnik nie został wydany, spróbuj ponownie',
        'success' => 'Składnik został wydany pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Spróbuj ponownie.',
        'unavailable' => 'Niewystarczająca ilość pozostałych komponentów: :remaining pozostało, :requested żądano ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Żadne pole nie zostało zmodyfikowane, więc nic nie zostało zaktualizowane.',
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
        'error' => 'Składnik nie został odebrany, spróbuj ponownie',
        'success' => 'Składnik został odebrany pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Spróbuj ponownie.',
    ],

];
