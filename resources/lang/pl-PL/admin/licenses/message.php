<?php

return array(

    'does_not_exist' => 'Licencja nie istnieje lub nie masz uprawnień do jej przeglądania.',
    'user_does_not_exist' => 'Użytkownik nie istnieje lub nie masz uprawnień do ich przeglądania.',
    'asset_does_not_exist' 	=> 'Aktywa, które chcesz skojarzyć z licencją nie istnieją.',
    'owner_doesnt_match_asset' => 'Aktywa, które chcesz skojarzyć z tą licencją są własnością kogoś innego niż osoba wskazana z rozwijanej listy.',
    'assoc_users'	 => 'Ten nabytek/zasób jest przypisany do użytkownika i nie może być usunięty. Proszę sprawdzić przypisanie nabytków/zasobów a następnie spróbować ponownie. ',
    'select_asset_or_person' => 'Musisz wybrać składnik aktywów lub użytkownika, ale nie oba.',
    'not_found' => 'Licencja nie została znaleziona',
    'seats_available' => ':seat_count dostępnych miejsc',


    'create' => array(
        'error'   => 'Licencja nie została utworzona, spróbuj ponownie.',
        'success' => 'Licencja została utworzona pomyślnie.'
    ),

    'deletefile' => array(
        'error'   => 'Plik nie został usunięty. Spróbuj ponownie.',
        'success' => 'Plik został usunięty pomyślnie.',
    ),

    'upload' => array(
        'error'   => 'Plik(i) nie zostały wysłane. Spróbuj ponownie.',
        'success' => 'Plik(i) zostały wysłane poprawnie.',
        'nofiles' => 'Nie wybrałeś żadnych plików do przesłania, albo plik, który próbujesz przekazać jest zbyt duży',
        'invalidfiles' => 'Jeden lub więcej z wybranych przez ciebie plików jest za duży lub jego typ nie jest dopuszczony. Dopuszczalne typy plików: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, and lic.',
    ),

    'update' => array(
        'error'   => 'Licencja nie została uaktualniona, spróbuj ponownie',
        'success' => 'Licencja została zaktualizowana pomyślnie.'
    ),

    'delete' => array(
        'confirm'   => 'Czy jesteś pewny, że chcesz usunąć tą licencję?',
        'error'   => 'Wystąpił problem podczas usuwania licencji. Spróbuj ponownie.',
        'success' => 'Licencja została usunięta pomyślnie.'
    ),

    'checkout' => array(
        'error'   => 'Nastąpił problem podczas weryfikacji licencji. Spróbuj ponownie',
        'success' => 'Licencja poprawna',
        'not_enough_seats' => 'Za mało dostępnych miejsc do zamówienia',
        'mismatch' => 'Podane miejsce licencji nie jest zgodne z licencją',
        'unavailable' => 'To miejsce nie jest dostępne do wydania.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Nastąpił problem podczas weryfikacji licencji. Spróbuj ponownie',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licencja poprawna'
    ),

);
