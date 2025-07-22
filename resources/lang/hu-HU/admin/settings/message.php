<?php

return [

    'update' => [
        'error'                 => 'Hiba történt frissítés közben. ',
        'success'               => 'A beállítások sikeresen frissítve.',
    ],
    'backup' => [
        'delete_confirm'        => 'Biztosan törölni szeretné ezt a biztonsági másolatot? Ez a művelet nem vonható vissza.',
        'file_deleted'          => 'A biztonsági mentés sikeresen törölve lett.',
        'generated'             => 'Új biztonsági másolatot sikerült létrehozni.',
        'file_not_found'        => 'A biztonsági másolat nem található a kiszolgálón.',
        'restore_warning'       => 'Igen, állítsa vissza. Tudomásul veszem, hogy ez felülírja az adatbázisban jelenleg meglévő adatokat. Ez egyben az összes meglévő felhasználó (beleértve Önt is) kijelentkezik.',
        'restore_confirm'       => 'Biztos, hogy vissza szeretné állítani az adatbázisát a :filename -ből?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Hiba történt a tisztítás során.',
        'validation_failed'     => 'A tisztítás megerősítése helytelen. Kérjük, írja be a "DELETE" szót a megerősítő mezőbe.',
        'success'               => 'A törölt rekordok sikeresen feltöltöttek.',
    ],
    'mail' => [
        'sending' => 'Teszt e-mail küldése...',
        'success' => 'Levél elküldve!',
        'error' => 'A levelet nem lehetett elküldeni.',
        'additional' => 'Nincs további hibaüzenet. Ellenőrizze a levelezési beállításokat és az alkalmazás naplóját.'
    ],
    'ldap' => [
        'testing' => 'LDAP kapcsolat, kötés és lekérdezés tesztelése ...',
        '500' => '500 Szerverhiba. Kérjük, további információkért ellenőrizze a szervernaplókat.',
        'error' => 'Valami hiba történt :(',
        'sync_success' => 'Az LDAP-kiszolgálóról visszaküldött 10 felhasználó mintája az Ön beállításai alapján:',
        'testing_authentication' => 'LDAP-hitelesítés tesztelése...',
        'authentication_success' => 'A felhasználó sikeresen hitelesített az LDAP-nál!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => ':app tesztüzenet küldése...',
        'success' => 'A :webhook_name integráció működik!',
        'success_pt1' => 'Siker! Ellenőrizze a ',
        'success_pt2' => ' csatornát a tesztüzenethez, és ne felejtsen el a MENTÉS gombra kattintani a beállítások tárolásához.',
        '500' => '500 Szerverhiba.',
        'error' => 'Valami hiba történt. A Slack a következő üzenettel válaszolt: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'Valami hiba történt :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
