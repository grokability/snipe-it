<?php

return [

    'undeployable' 		 => 'Następujące środki nie mogą zostać wydane i zostały ograniczone w tym zakresie: :asset_tags',
    'does_not_exist' 	 => 'Środek nie istnieje',
    'does_not_exist_var' => 'Nie znaleziono środka o tagu :asset_tag',
    'no_tag' 	         => 'Nie podano numeru środka.',
    'does_not_exist_or_not_requestable' => 'Środek nie istnieje albo nie można o niego wnioskować.',
    'assoc_users'	 	 => 'Ten środek jest przypisany do użytkownika i nie może być usunięty. Proszę sprawdzić przypisanie środków a następnie spróbować ponownie.',
    'warning_audit_date_mismatch' 	=> 'Data następnego audytu (:next_audit_date) jest przed datą poprzedniego audytu (:last_audit_date). Zaktualizuj datę następnego audytu.',
    'labels_generated'   => 'Etykiety zostały pomyślnie wygenerowane.',
    'error_generating_labels' => 'Błąd podczas generowania etykiet.',
    'no_assets_selected' => 'Nie wybrano żadnych środków.',

    'create' => [
        'error'   		=> 'Środeknie został utworzony, proszę spróbować ponownie. :(',
        'success' 		=> 'Nowy środek został utworzony.  :)',
        'success_linked' => 'Środek o numerze: :tag został utworzony pomyślnie. <strong><a href=":link" style="color: white;">Kliknij tutaj, aby go wyświetlić</a></strong>.',
        'multi_success_linked' => 'Środek o numerze: :link został utworzony pomyślnie.|:count środków zostało utworzonych pomyślnie. :links.',
        'partial_failure' => 'Nie można utworzyć środka. Powód: :failures|:count środków nie mogło zostać utworzonych. Powód: :failed',
        'target_not_found' => [
            'user' => 'Nie znaleziono przypisanego użytkownika.',
            'asset' => 'Nie znaleziono przypisanego środka.',
            'location' => 'Nie znaleziono przypisanej lokalizacji.',
        ],
    ],

    'update' => [
        'error'   			=> 'Nie zaktualizowano środka, proszę spróbować ponownie',
        'success' 			=> 'Aktualizacja poprawna.',
        'encrypted_warning' => 'Środek zaktualizowany pomyślnie, ale zaszyfrowane pola niestandardowe nie zostały zaktualizowane ze względu na brak uprawnień.',
        'nothing_updated'	=>  'Żadne pole nie zostało wybrane, więc nic nie zostało zmienione.',
        'no_assets_selected'  =>  'Żadne środki nie zostały wybrane, więc nic nie zostało zmienione.',
        'assets_do_not_exist_or_are_invalid' => 'Wybrane środki nie mogą zostać zaktualizowane.',
    ],

    'restore' => [
        'error'   		=> 'Środek nie został przywrócony, spróbuj ponownie.',
        'success' 		=> 'Środek został przywrócony.',
        'bulk_success' 		=> 'Środek został pomyślnie przywrócony.',
        'nothing_updated'   => 'Żadne środki nie zostały wybrane, więc nic nie zostało przywrócone. ', 
    ],

    'audit' => [
        'error'   		=> 'Audyt środka zakończony niepowodzeniem :error ',
        'success' 		=> 'Audyt środka pomyślnie zarejestrowany.',
    ],


    'deletefile' => [
        'error'   => 'Plik nie zostały usunięty. Spróbuj ponownie.',
        'success' => 'Plik został usunięty.',
    ],

    'upload' => [
        'error'   => 'Plik(i) nie zostały wysłane. Spróbuj ponownie.',
        'success' => 'Plik(i) zostały wysłane.',
        'nofiles' => 'Nie wybrałeś żadnych plików do przesłania, albo plik, który próbujesz przekazać jest zbyt duży',
        'invalidfiles' => 'Jeden lub więcej z wybranych przez ciebie plików jest za duży lub jego typ jest niewłaściwy. Dopuszczalne typy plików: png, gif, jpg, doc, docx, pdf, oraz txt.',
    ],

    'import' => [
        'import_button'         => 'Przetwórz import',
        'error'                 => 'Niektóre elementy nie zostały poprawnie zaimportowane.',
        'errorDetail'           => 'Następujące elementy nie zostały zaimportowane z powodu błędów.',
        'success'               => 'Twój plik został zaimportowany',
        'file_delete_success'   => 'Twój plik został poprawnie usunięty',
        'file_delete_error'      => 'Plik nie może zostać usunięty',
        'file_missing' => 'Brakuje wybranego pliku',
        'file_already_deleted' => 'Wybrany plik został już usunięty',
        'header_row_has_malformed_characters' => 'Jeden lub więcej atrybutów w wierszu nagłówka zawiera nieprawidłowe znaki UTF-8',
        'content_row_has_malformed_characters' => 'Jeden lub więcej atrybutów w pierwszym rzędzie zawartości zawiera nieprawidłowe znaki UTF-8',
        'transliterate_failure' => 'Transformacja z :encoding do UTF-8 zakończyła się niepowodzeniem z powodu nieprawidłowych znaków wejściowych'
    ],


    'delete' => [
        'confirm'   	=> 'Czy na pewno chcesz usunąć?',
        'error'   		=> 'Nie można usunąć. Proszę spróbować ponownie.',
        'assigned_to_error' => '{1}Tag środka: :asset_tag jest obecnie wydany. Przyjmij ponownie ten sprzęt przed usunięciem.|[2,*]Tag środka: :asset_tag są obecnie wydane. Przyjmij ponownie te sprzętu przed usunięciem.',
        'nothing_updated'   => 'Środki nie zostały wybrane, więc nic nie zostało usunięte.',
        'success' 		=> 'Środek został usunięty.',
    ],

    'checkout' => [
        'error'   		=> 'Nie można wydać środka. Spróbuj ponownie.',
        'success' 		=> 'Środek przypisano pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'not_available' => 'Ten środek nie jest dostępny do wydania!',
        'no_assets_selected' => 'Musisz wybrać co najmniej jeden środek z listy',
    ],

    'multi-checkout' => [
        'error'   => 'Środek nie został przypisany, spróbuj ponownie|Środki nie zostały przypisane, spróbuj ponownie',
        'success' => 'Środek wydany pomyślnie.|Środki wydane pomyślnie.',
    ],

    'checkin' => [
        'error'   		=> 'Środek nie został przyjęty, proszę spróbować ponownie',
        'success' 		=> 'Pomyślnie przyjęto środek.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'already_checked_in'  => 'Środek jest już przyjęty.',

    ],

    'requests' => [
        'error'   		=> 'Wniosek zakończył się niepowodzeniem, spróbuj ponownie.',
        'success' 		=> 'Pomyślnie wysłano wniosek.',
        'canceled'      => 'Pomyślnie anulowano wniosek.',
        'cancel'        => 'Anuluj żądanie tego elementu',
    ],

];
