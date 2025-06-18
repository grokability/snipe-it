<?php

return [

    'update' => [
        'error'                 => 'Počas upravovania sa vyskytla chyba. ',
        'success'               => 'Nastavenia boli úspešne upravené.',
    ],
    'backup' => [
        'delete_confirm'        => 'Ste si istý, že chcete odstrániť tento súbor so zálohou? Táto akcia sa nedá vrátiť. ',
        'file_deleted'          => 'Súbor so zálohou bol úspešne odstránený. ',
        'generated'             => 'Nový súbor so zálohou bol úspešne vytvorený.',
        'file_not_found'        => 'Súbor so zálohou sa nepodarilo nájsť na serveri.',
        'restore_warning'       => 'Áno, obnoviť. Uvedomujem si, že táto akcia prepíše všetky existujúce dáta v databáze. Taktiež budú odhlásení všetci používatelia (vrátane vás).',
        'restore_confirm'       => 'Ste si istí, že chcete obnoviť databázu z :fielname?'
    ],
    'restore' => [
        'success'               => 'Vaša systémová záloha bola obnovená. Prosím znovu sa prihláste.'
    ],
    'purge' => [
        'error'     => 'Počas čistenia sa vyskytla chyba. ',
        'validation_failed'     => 'Potvrdenie odstránenia nie je správne. Prosím napíšte slovo "DELETE" do políčka na potvrdenie.',
        'success'               => 'Odstránené záznamy boli úspešne očistené.',
    ],
    'mail' => [
        'sending' => 'Posielam testovací email...',
        'success' => 'Email odoslaný!',
        'error' => 'Email sa nepodarilo odoslať.',
        'additional' => 'Podrobná správa o chybe nie je dostupná. Skontrolujte nastavenia pošty a logy.'
    ],
    'ldap' => [
        'testing' => 'Testujem LDAP spojenie, väzbu a dopyty ...',
        '500' => '500 chyba servera. Prosím skontroluje serverové logy pre viac informácií.',
        'error' => 'Niečo sa pokazilo :(',
        'sync_success' => 'Ukážka 10 používateľov vrátená z LDAP server na základe vašich nastavení:',
        'testing_authentication' => 'Testujem LDAP autentifikáciu...',
        'authentication_success' => 'Používateľ sa úspešne autentifikoval voči LDAP-u!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Posielam :app testovaciu správu...',
        'success' => 'Vaša :webhook_name integrácia funguje!',
        'success_pt1' => 'Úspešné! Skontrolujte ',
        'success_pt2' => ' kanál pre vaše testovacie správy a uistite sa, že kliknete na tlačidlo ULOŽIŤ pre uloženie nastavení.',
        '500' => '500 Chyba servera.',
        'error' => 'Nastala chyba. :app odpovedala s: :error_message',
        'error_redirect' => 'CHBA: 301/302 :endpoint vrátil presmerovanie. Z bezpečnostných dôvodov nenasledujeme presmerovania. Prosím použite správny koncový bod.',
        'error_misc' => 'Niečo sa pokazilo. :( ',
        'webhook_fail' => ' webhook notifikácia zlyhala: Overte správnosť zadanej URL adresy.',
        'webhook_channel_not_found' => ' kanál webhooku nebol nájdený.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
