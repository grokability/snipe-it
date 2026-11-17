<?php

return [

    'does_not_exist' => 'Akcesorium [:id] nie istnieje.',
    'not_found' => 'To akcesorium nie zostało znalezione.',
    'assoc_users' => 'Akcesoria z tej kategorii zostały wydane do :count użytkowników. Zbierz akcesoria i spróbuj ponownie. ',

    'create' => [
        'error' => 'Akcesorium nie utworzono, spróbuj ponownie.',
        'success' => 'Akcesorium utworzono pomyślnie.',
    ],

    'update' => [
        'error' => 'Nie zaktualizowano Akcesorium, spróbuj ponownie',
        'success' => 'Akcesorium utworzono pomyślnie.',
    ],

    'delete' => [
        'confirm' => 'Czy na pewno chcesz usunąć to Akcesorium?',
        'error' => 'Wystąpił błąd podczas usuwania akcesorium. Proszę spróbować ponownie.',
        'success' => 'Akcesorium zostało usunięte pomyślnie.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Akcesoria nie zostały przypisane, spróbuj ponownie',
        'success' => 'Akcesoria przypisany pomyślnie.',
        'unavailable' => 'Akcesoria nie są dostępne do zakupu. Sprawdź ilość dostępną',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.',
        'checkout_qty' => [
            'lte' => 'Obecnie dostępne jest tylko jedno akcesorium tego typu i próbujesz wydać :checkout_qty. Dostostosuj ilość wydania lub stan magazynowy tego akcesorium i spróbuj ponownie. Obecnie jest :number_currently_remaining dostępnych akcesoriów i próbujesz wydać :checkout_qty. Dostostosuj ilość wydania lub stan magazynowy tego akcesorium i spróbuj ponownie.',
        ],

    ],

    'checkin' => [
        'error' => 'Akcesoria nie zostały przypisane, spróbuj ponownie',
        'success' => 'Akcesoria przypisane pomyślnie.',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Żadne pole nie zostało zmodyfikowane, więc nic nie zostało zaktualizowane.',
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
