<?php

return array(

    'does_not_exist' => 'Akcesorium [:id] nie istnieje.',
    'not_found' => 'To akcesorium nie zostało znalezione.',
    'assoc_users'	 => 'Akcesoria z tej kategorii zostały wydane do :count użytkowników. Zbierz akcesoria i spróbuj ponownie. ',

    'create' => array(
        'error'   => 'Akcesorium nie utworzono, spróbuj ponownie.',
        'success' => 'Akcesorium utworzono pomyślnie.'
    ),

    'update' => array(
        'error'   => 'Nie zaktualizowano Akcesorium, spróbuj ponownie',
        'success' => 'Akcesorium utworzono pomyślnie.'
    ),

    'delete' => array(
        'confirm'   => 'Czy na pewno chcesz usunąć to Akcesorium?',
        'error'   => 'Wystąpił błąd podczas usuwania akcesorium. Proszę spróbować ponownie.',
        'success' => 'Akcesorium zostało usunięte pomyślnie.'
    ),

     'checkout' => array(
        'error'   		=> 'Akcesoria nie zostały przypisane, spróbuj ponownie',
        'success' 		=> 'Akcesoria przypisany pomyślnie.',
        'unavailable'   => 'Akcesoria nie są dostępne do zakupu. Sprawdź ilość dostępną',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.',
         'checkout_qty' => array(
            'lte'  => 'Obecnie dostępne jest tylko jedno akcesorium tego typu i próbujesz wydać :checkout_qty. Dostostosuj ilość wydania lub stan magazynowy tego akcesorium i spróbuj ponownie. Obecnie jest :number_currently_remaining dostępnych akcesoriów i próbujesz wydać :checkout_qty. Dostostosuj ilość wydania lub stan magazynowy tego akcesorium i spróbuj ponownie.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Akcesoria nie zostały przypisane, spróbuj ponownie',
        'success' 		=> 'Akcesoria przypisane pomyślnie.',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.'
    )


);
