<?php

return [

    'update' => [
        'error'                 => 'Vyskytla se chyba při aktualizaci. ',
        'success'               => 'Nastavení úspěšně uloženo.',
    ],
    'backup' => [
        'delete_confirm'        => 'Opravdu chcete vymazat tento záložní soubor? Tuto akci nelze vrátit zpět. ',
        'file_deleted'          => 'Záložní soubor byl úspěšně smazán. ',
        'generated'             => 'Byla úspěšně vytvořena nová záloha.',
        'file_not_found'        => 'Tento záložní soubor nebyl na serveru nalezen.',
        'restore_warning'       => 'Ano, obnovit. Potvrzuji, že toto přepíše existující data v databázi. Tato akce taky odhlásí všechny uživatele (včetně vás).',
        'restore_confirm'       => 'Jste si jisti, že chcete obnovit databázi z :filename?'
    ],
    'restore' => [
        'success'               => 'Vaše záloha systému byla obnovena. Přihlaste se prosím znovu.'
    ],
    'purge' => [
        'error'     => 'Během čištění došlo k chybě. ',
        'validation_failed'     => 'Vaše potvrzení o čištění je nesprávné. Zadejte prosím slovo "DELETE" do potvrzovacího rámečku.',
        'success'               => 'Vymazané záznamy byly úspěšně vyčištěny.',
    ],
    'mail' => [
        'sending' => 'Odesílání testovacího e-mailu...',
        'success' => 'E-mail odeslán!',
        'error' => 'E-mail se nepodařilo odeslat.',
        'additional' => 'Porobná zpárva o chybě není dostupná. Zkontrolujte nastavení pošty a log.'
    ],
    'ldap' => [
        'testing' => 'Testování LDAP připojení, vazby a dotazu ...',
        '500' => '500 Server error. Zkontrolujte serverové logy pro více informací.',
        'error' => 'Něco se pokazilo :(',
        'sync_success' => '10 příkladových uživatelů z LDAP serveru podle vašeho nastavení:',
        'testing_authentication' => 'Testování LDAP ověření...',
        'authentication_success' => 'Uživatel byl úspěšně ověřen přes LDAP!'
    ],
    'labels' => [
        'null_template' => 'Šablona štítku nebyla nalezena. Vyberte prosím šablonu.',
        ],
    'webhook' => [
        'sending' => 'Odesílání testovací zprávy :app...',
        'success' => 'Vaše integrace :webhook_name funguje!',
        'success_pt1' => 'Úspěšně! Zkontrolujte ',
        'success_pt2' => ' kanál pro vaši testovací zprávu a ujistěte se, že klepněte na tlačítko ULOŽIT pro uložení nastavení.',
        '500' => '500 Server Error.',
        'error' => 'Něco se pokazilo. :app odpověděla v: :error_message',
        'error_redirect' => 'CHYBA: 301/302 :endpoint vrací přesměrování. Z bezpečnostních důvodů nesledujeme přesměrování. Použijte prosím skutečný koncový bod.',
        'error_misc' => 'Něco se nepovedlo.',
        'webhook_fail' => 'Odeslání webhook notifikace selhalo: Zkontrolujte, zda je URL stále platná.',
        'webhook_channel_not_found' => ' webhook kanál nebyl nalezen.',
        'ms_teams_deprecation' => 'Vybraná adresa webhooku Microsoft Teams bude ukončena k 31. prosinci 2025. Použijte prosím adresu URL pro workflow. Dokumentaci Microsoftu k vytvoření workflow najdete
<a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">zde.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Vaše nastavení nebylo uloženo.',
        'mismatch' => 'V databázi je 1 položka, která potřebuje vaši pozornost, než budete moci povolit zjišťování polohy. V databázi jsou :count položky, které potřebují vaši pozornost, než budete moci povolit zjišťování polohy.',
    ],
];
