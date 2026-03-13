<?php

return array(
    'about_licenses_title'      => 'O Licencjach',
    'about_licenses'            => 'Licencje są używane do śledzenia oprogramowania.  Posiadają określoną liczbę stanowisk, które mogą być przypisane do osób fizycznych.',
    'checkin'  					=> 'Sprawdź płatność',
    'checkout_history'  		=> 'Historia płatności',
    'checkout'  				=> 'Sprawdź płatność',
    'edit'  					=> 'Edytuj licencje',
    'filetype_info'				=> 'Dozwolone typy plików: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar.',
    'clone'  					=> 'Duplikuj licencje',
    'history_for'  				=> 'Historia dla ',
    'in_out'  					=> 'Wejście/Wyjście',
    'info'  					=> 'Informacja o licencji',
    'license_seats'  			=> 'Licencje',
    'seat'  					=> 'Miejsce',
    'seat_count'  				=> 'Siedzenie :count',
    'seats'  					=> 'Miejsca',
    'software_licenses'  		=> 'Licencje oprogramowania',
    'user'  					=> 'Użytkownik',
    'view'  					=> 'Podgląd licencji',
    'delete_disabled'           => 'Ta licencja nie może być jeszcze usunięta, ponieważ niektóre miejsca są nadal zablokowane.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Zaznacz wszystkie miejsca',
                'modal'             => 'Ta akcja będzie sprawdzać jedno miejsce. | Ta akcja będzie sprawdzać wszystkie :checkedout_seats_count miejsc dla tej licencji.',
                'enabled_tooltip'   => 'Zaznacz WSZYSTKIE miejsca dla tej licencji zarówno od użytkowników, jak i aktywów',
                'disabled_tooltip'  => 'To jest wyłączone, ponieważ nie ma obecnie zamówionych miejsc',
                'disabled_tooltip_reassignable'  => 'To jest wyłączone, ponieważ licencja nie jest przypisywana ponownie',
                'success'           => 'Licencja pomyślnie odblokowana! | Wszystkie licencje zostały pomyślnie sprawdzone!',
                'log_msg'           => 'Sprawdzone poprzez sprawdzenie licencji zbiorczej w interfejsie licencyjnym',
            ],

            'checkout_all'              => [
                'button'                => 'Zamów wszystkie miejsca',
                'modal'                 => 'Ta akcja obejmie jedno miejsce pierwszemu dostępnemu użytkownikowi. | Ta akcja obejmie wszystkie miejsca :available_seats_count dla pierwszych dostępnych użytkowników. Użytkownik jest uważany za dostępny dla tego miejsca, jeśli nie ma jeszcze tej licencji wyrejestrowanej dla nich, a własność automatycznego przypisywania licencji jest włączona na ich koncie użytkownika.',
                'enabled_tooltip'   => 'Sprawdź WSZYSTKIE miejsca (lub tyle miejsc) dla WSZYSTKICH użytkowników',
                'disabled_tooltip'  => 'To jest wyłączone, ponieważ nie ma obecnie dostępnych miejsc',
                'success'           => 'Licencja została pomyślnie wyczyszczona! | :count licencji zostały pomyślnie wyczyszczone!',
                'error_no_seats'    => 'Nie ma pozostałych miejsc dla tej licencji.',
                'warn_not_enough_seats'    => ':count użytkownicy zostali przypisani do tej licencji, ale zabrakło nam dostępnych miejsc do licencji.',
                'warn_no_avail_users'    => 'Nic do zrobienia. Nie ma żadnych użytkowników, którzy nie mają jeszcze przypisanej im tej licencji.',
                'log_msg'           => 'Zamówione za pomocą licencji masowej w interfejsie licencyjnym',


            ],
    ],

    'below_threshold' => 'Istnieją tylko :remaining_count miejsc dla tej licencji z minimalną ilością :min_amt. Możesz rozważyć zakup większej liczby miejsc.',
    'below_threshold_short' => 'Ta pozycja jest poniżej minimalnej wymaganej ilości.',
);
