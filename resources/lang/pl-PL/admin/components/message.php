<?php

return array(

    'does_not_exist' => 'Składnik nie istnieje.',

    'create' => array(
        'error'   => 'Składnik nie został utworzony, spróbuj ponownie.',
        'success' => 'Składnik został utworzony pomyślnie.'
    ),

    'update' => array(
        'error'   => 'Składnik nie został uaktualniony, spróbuj ponownie',
        'success' => 'Składnik został zaktualizowany pomyślnie.'
    ),

    'delete' => array(
        'confirm'   => 'Czy na pewno chcesz usunąć ten składnik?',
        'error'   => 'Wystąpił problem podczas usuwania składnika. Spróbuj ponownie.',
        'success' => 'Składnik został usunięty pomyślnie.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Składnik nie został wydany, spróbuj ponownie',
        'success' 		=> 'Składnik został wydany pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Spróbuj ponownie.',
        'unavailable'      => 'Niewystarczająca ilość pozostałych komponentów: :remaining pozostało, :requested żądano ',
    ),

    'checkin' => array(
        'error'   		=> 'Składnik nie został odebrany, spróbuj ponownie',
        'success' 		=> 'Składnik został odebrany pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Spróbuj ponownie.'
    )


);
