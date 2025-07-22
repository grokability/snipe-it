<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Nabytek/zasób nie istnieje.',
    'does_not_exist_var' => 'Nie znaleziono zasobu o tagu :asset_tag.',
    'no_tag' 	         => 'Nie podano tagu zasobu.',
    'does_not_exist_or_not_requestable' => 'Aktywo nie istnieje albo nie można go zażądać.',
    'assoc_users'	 	 => 'Ten nabytek/zasób jest przypisany do użytkownika i nie może być usunięty. Proszę sprawdzić przypisanie nabytków/zasobów a następnie spróbować ponownie.',
    'warning_audit_date_mismatch' 	=> 'Data następnego audytu (:next_audit_date) jest przed datą poprzedniego audytu (:last_audit_date). Zaktualizuj datę następnego audytu.',
    'labels_generated'   => 'Etykiety zostały pomyślnie wygenerowane.',
    'error_generating_labels' => 'Błąd podczas generowania etykiet.',
    'no_assets_selected' => 'Nie wybrano żadnych zasobów.',

    'create' => [
        'error'   		=> 'Nabytek nie został utworzony, proszę spróbować ponownie. :(',
        'success' 		=> 'Nowy nabytek został utworzony. :)',
        'success_linked' => 'Zasób o tagu :tag został utworzony pomyślnie. <strong><a href=":link" style="color: white;">Kliknij tutaj, aby wyświetlić</a></strong>.',
        'multi_success_linked' => 'Zasób z tagiem :link został utworzony pomyślnie.|:count aktywów zostało utworzonych pomyślnie. :links.',
        'partial_failure' => 'Nie można utworzyć zasobu. Powód: :failures|:count aktywów nie mogły zostać utworzone. Powód: :failed',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Nie zaktualizowano nabytku/zasobu, proszę spróbować ponownie',
        'success' 			=> 'Aktualizacja poprawna.',
        'encrypted_warning' => 'Zasób zaktualizowany pomyślnie, ale zaszyfrowane pola niestandardowe nie były ze względu na uprawnienia',
        'nothing_updated'	=>  'Żadne pole nie zostało wybrane, więc nic nie zostało zmienione.',
        'no_assets_selected'  =>  'Żadne aktywa nie zostały wybrane, więc nic nie zostało zmienione.',
        'assets_do_not_exist_or_are_invalid' => 'Wybrane zasoby nie mogą zostać zaktualizowane.',
    ],

    'restore' => [
        'error'   		=> 'Aktywo nie został przywrócony, spróbuj ponownie.',
        'success' 		=> 'Aktywo zostało przywrócone.',
        'bulk_success' 		=> 'Aktywo zostało pomyślnie przywrócone.',
        'nothing_updated'   => 'Żadne aktywa nie zostały wybrane, więc nic nie zostało przywrócone.', 
    ],

    'audit' => [
        'error'   		=> 'Audyt zasobu zakończony niepowodzeniem :error ',
        'success' 		=> 'Audyt aktywów pomyślnie zarejestrowany.',
    ],


    'deletefile' => [
        'error'   => 'Plik nie zostały usunięte. Spróbuj ponownie.',
        'success' => 'Plik zostały usunięty.',
    ],

    'upload' => [
        'error'   => 'Plik(i) nie zostały wysłane. Spróbuj ponownie.',
        'success' => 'Plik(i) zostały wysłane.',
        'nofiles' => 'Nie wybrałeś żadnych plików do przesłania, albo plik, który próbujesz przekazać jest zbyt duży',
        'invalidfiles' => 'Jeden lub więcej z wybranych przez ciebie plików jest jest za duży lub jego typ jest niewłaściwy. Dopuszczalne typy plików: png, gif, jpg, doc, docx, pdf, and txt.',
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
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Czy na pewno chcesz usunąć?',
        'error'   		=> 'Nie można usunąć. Proszę spróbować ponownie.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Aktywa nie zostały wybrane, więc nic nie zostało usunięte.',
        'success' 		=> 'Nabytek został usunięty.',
    ],

    'checkout' => [
        'error'   		=> 'Nie mogę wypisać nabytku/zasobu, proszę spróbować ponownie.',
        'success' 		=> 'Przypisano nabytek/zasób.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'not_available' => 'Ten składnik aktywów nie jest dostępny do zamówienia!',
        'no_assets_selected' => 'Musisz wybrać co najmniej jeden zasób z listy',
    ],

    'multi-checkout' => [
        'error'   => 'Zasób nie został zablokowany, spróbuj ponownie|Zasoby nie zostały zablokowane, spróbuj ponownie',
        'success' => 'Zasób wydany pomyślnie.|Zasoby wydane pomyślnie.',
    ],

    'checkin' => [
        'error'   		=> 'Nie można przypisać nabytku/zasobu, proszę spróbować ponownie',
        'success' 		=> 'Nabytek/zasób przypisany.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'already_checked_in'  => 'Aktywo jest już zaewidencjonowane.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Anuluj żądanie tego elementu',
    ],

];
