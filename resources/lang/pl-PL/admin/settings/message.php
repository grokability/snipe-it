<?php

return [

    'update' => [
        'error'                 => 'Wystąpił błąd podczas aktualizacji. ',
        'success'               => 'Ustawienia zaktualizowane pomyślnie.',
    ],
    'backup' => [
        'delete_confirm'        => 'Czy na pewno chcesz usunąć kopie zapasową? Nie można cofnąć tej akcji. ',
        'file_deleted'          => 'Kopia zapasowa usunięta pomyślnie. ',
        'generated'             => 'Nowa kopia zapasowa utworzona pomyślnie.',
        'file_not_found'        => 'Nie odnaleziono kopii zapasowej na serwerze.',
        'restore_warning'       => 'Tak, przywróć. Mam świadomość, że spowoduje to nadpisanie istniejących danych w bazie danych. Spowoduje to również wylogowanie wszystkich istniejących użytkowników (w tym Ciebie).',
        'restore_confirm'       => 'Czy na pewno chcesz przywrócić bazę danych z :filename?'
    ],
    'restore' => [
        'success'               => 'Kopia zapasowa została przywrócona. Zaloguj się ponownie.'
    ],
    'purge' => [
        'error'     => 'Wystąpił błąd podczas czyszczenia. ',
        'validation_failed'     => 'Potwierdzenie czyszczenia jest niepoprawne. Wpisz słowo "DELETE" w polu potwierdzenia.',
        'success'               => 'Pomyślnie wyczyszczono rekordy usunięte.',
    ],
    'mail' => [
        'sending' => 'Wysyłanie testowej wiadomości e-mail...',
        'success' => 'Wiadomość wysłana!',
        'error' => 'Wiadomość nie może zostać wysłana.',
        'additional' => 'Nie podano dodatkowego komunikatu o błędzie. Sprawdź ustawienia poczty i logu aplikacji.'
    ],
    'ldap' => [
        'testing' => 'Testowanie połączenia LDAP, powiązania i zapytania ...',
        '500' => 'Błąd serwera 500. Sprawdź logi serwera, aby uzyskać więcej informacji.',
        'error' => 'Coś poszło nie tak :(',
        'sync_success' => 'Przykładowe 10 użytkowników zwrócona z serwera LDAP na podstawie Twoich ustawień:',
        'testing_authentication' => 'Testowanie uwierzytelniania LDAP...',
        'authentication_success' => 'Użytkownik uwierzytelniony z LDAP pomyślnie!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Wysyłanie wiadomości testowej :app...',
        'success' => 'Twoja integracja :webhook_name działa!',
        'success_pt1' => 'Sukces! Sprawdź ',
        'success_pt2' => ' kanał wiadomości testowej i pamiętaj, aby kliknąć ZAPISZ poniżej, aby zapisać ustawienia.',
        '500' => 'Błąd 500 serwera.',
        'error' => 'Coś poszło nie tak. :app odpowiedział: :error_message',
        'error_redirect' => 'BŁĄD: 301/302 :endpoint zwraca przekierowanie. Ze względów bezpieczeństwa nie podążamy za przekierowaniami. Proszę użyć aktualnego punktu końcowego.',
        'error_misc' => 'Coś poszło nie tak. :( ',
        'webhook_fail' => ' Powiadomienie webhook nie powiodło się: Sprawdź, czy adres URL jest nadal prawidłowy.',
        'webhook_channel_not_found' => ' Nie znaleziono kanału webhook.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Twoje ustawienia nie zostały zapisane',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
