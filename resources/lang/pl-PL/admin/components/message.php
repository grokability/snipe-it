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

    'checkin' => [
        'error' => 'Składnik nie został odebrany, spróbuj ponownie',
        'success' => 'Składnik został odebrany pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Spróbuj ponownie.',
    ],

];
