<?php

return [

    'update' => [
        'error'                 => 'Ett fel har uppstått vid uppdatering. ',
        'success'               => 'Inställningarna uppdaterades.',
    ],
    'backup' => [
        'delete_confirm'        => 'Är du säker på att du vill ta bort den här säkerhetskopian? Den här åtgärden kan inte ångras. ',
        'file_deleted'          => 'Säkerhetskopian har tagits bort. ',
        'generated'             => 'En ny säkerhetskopia skapades.',
        'file_not_found'        => 'Säkerhetskopian kunde inte hittas på servern.',
        'restore_warning'       => 'Ja, återställ den. Jag är medveten att detta kommer att skriva över befintlig data som redan finns i databasen. Detta kommer också att logga ut alla befintliga användare (inklusive dig själv).',
        'restore_confirm'       => 'Är du säker på att du vill återställa din databas från :filename?'
    ],
    'restore' => [
        'success'               => 'Din säkerhetskopia har återställts. Vänligen logga in igen.'
    ],
    'purge' => [
        'error'     => 'Ett fel har uppstått vid radering. ',
        'validation_failed'     => 'Raderingsbekräftelsekoden är felaktig. Vänligen skriv ordet "DELETE" i bekräftelserutan.',
        'success'               => 'Tidigare raderade poster har raderats för gott.',
    ],
    'mail' => [
        'sending' => 'Skickar testmeddelande...',
        'success' => 'E-post skickat!',
        'error' => 'E-postmeddelandet kunde inte skickas.',
        'additional' => 'Inga ytterligare felmeddelanden. Kontrollera dina e-postinställningar och din app-logg.'
    ],
    'ldap' => [
        'testing' => 'Testar LDAP-anslutning, Bindning och Query...',
        '500' => '500 Server Error. Kontrollera dina serverloggar för mer information.',
        'error' => 'Något gick snett :(',
        'sync_success' => 'Ett urval av 10 användare som returneras från LDAP-servern baserat på dina inställningar:',
        'testing_authentication' => 'Testar LDAP-autentisering...',
        'authentication_success' => 'Användaren har autentiserats via LDAP!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Skickar :app testmeddelande...',
        'success' => 'Din :webhook_name-integration fungerar!',
        'success_pt1' => 'Klart! Kontrollera ',
        'success_pt2' => ' kanal för ditt testmeddelande, och se till att klicka på SPARA nedan för att lagra dina inställningar.',
        '500' => '500 Server Error.',
        'error' => 'Något gick snett! :app svarade med: :error_message',
        'error_redirect' => 'FEL: 301/302 :endpoint returnerar en redirect. Av säkerhetsskäl följer vi inte redirects. Använd den faktiska endpointen.',
        'error_misc' => 'Någonting gick snett :( ',
        'webhook_fail' => 'webhook-notis misslyckades. Kontrollera att URL\'en fortfarande är giltig.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
