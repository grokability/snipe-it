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
        'success'               => 'Rendszermentés visszaállítva. Jelentkezzen be újra.'
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
        'null_template' => 'A címkesablon nem található. Kérjük, válasszon egy sablont.',
        ],
    'webhook' => [
        'sending' => ':app tesztüzenet küldése...',
        'success' => 'A :webhook_name integráció működik!',
        'success_pt1' => 'Siker! Ellenőrizze a ',
        'success_pt2' => ' csatornát a tesztüzenethez, és ne felejtsen el a MENTÉS gombra kattintani a beállítások tárolásához.',
        '500' => '500 Szerverhiba.',
        'error' => 'Valami hiba történt. A Slack a következő üzenettel válaszolt: :error_message',
        'error_redirect' => 'HIBA: 301/302 :endpoint átirányítást ad vissza. Biztonsági okokból nem követjük az átirányításokat. Kérjük, használja a tényleges végpontot.',
        'error_misc' => 'Valami hiba történt :( ',
        'webhook_fail' => ' webhook értesítés sikertelen: Ellenőrizze, hogy az URL még érvényes-e.',
        'webhook_channel_not_found' => ' webhook csatorna hiányzik.',
        'ms_teams_deprecation' => 'A kiválasztott Microsoft Teams webhook URL 2025.12.31-től elavult. Használjon workflow URL-t. Részletek a Microsoft dokumentációjában <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">itt</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'A beállításai nem kerültek mentésre.',
        'mismatch' => 'Az adatbázisban 1 tétel van, amely figyelmet igényel, mielőtt engedélyezhetné a helyszín szerinti szűrést.|Az adatbázisban :count tétel van, amely figyelmet igényel, mielőtt engedélyezhetné a helyszín szerinti szűrést.',
    ],
];
