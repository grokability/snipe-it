<?php

return [

    'update' => [
        'error'                 => 'Während der Aktualisierung ist ein Fehler aufgetreten. ',
        'success'               => 'Die Einstellungen wurden erfolgreich aktualisiert.',
    ],
    'backup' => [
        'delete_confirm'        => 'Backup Datei wirklich löschen? Aktion kann nicht rückgängig gemacht werden. ',
        'file_deleted'          => 'Backup Datei erfolgreich gelöscht. ',
        'generated'             => 'Neue Backup Datei erfolgreich erstellt.',
        'file_not_found'        => 'Backup Datei konnte nicht gefunden werden.',
        'restore_warning'       => 'Ja, wiederherstellen. Ich bestätige, dass dies alle vorhandenen Daten überschreibt, die derzeit in der Datenbank vorhanden sind. Diese Aktion wird auch alle bestehenden Benutzer abmelden (einschließlich Dir).',
        'restore_confirm'       => 'Bist du sicher, dass du deine Datenbank aus :filename wiederherstellen möchten?'
    ],
    'restore' => [
        'success'               => 'Ihr Systembackup wurde wiederhergestellt. Bitte melde dich erneut an.'
    ],
    'purge' => [
        'error'     => 'Beim Bereinigen ist ein Fehler augetreten. ',
        'validation_failed'     => 'Falsche Bereinigungsbestätigung. Bitte gib das Wort "DELETE" im Bestätigungsfeld ein.',
        'success'               => 'Gelöschte Einträge erfolgreich bereinigt.',
    ],
    'mail' => [
        'sending' => 'Test E-Mail wird gesendet...',
        'success' => 'Mail gesendet!',
        'error' => 'E-Mail konnte nicht gesendet werden.',
        'additional' => 'Keine zusätzliche Fehlermeldung vorhanden. Überprüfe deine E-Mail-Einstellungen und dein App-Protokoll.'
    ],
    'ldap' => [
        'testing' => 'Teste LDAP Verbindung, Binding & Abfrage ...',
        '500' => '500 Serverfehler. Bitte überprüfe Deine Server-Logs für weitere Informationen.',
        'error' => 'Etwas ist schiefgelaufen :(',
        'sync_success' => 'Ein Beispiel von 10 Benutzern, die vom LDAP-Server basierend auf Deinen Einstellungen zurückgegeben wurden:',
        'testing_authentication' => 'LDAP-Authentifizierung wird getestet...',
        'authentication_success' => 'Benutzer wurde erfolgreich gegen LDAP authentifiziert!'
    ],
    'labels' => [
        'null_template' => 'Etikettenvorlage nicht gefunden. Bitte wähle eine Vorlage aus.',
        ],
    'webhook' => [
        'sending' => ':app Testnachricht wird gesendet ...',
        'success' => 'Deine :webhook_name Integration funktioniert!',
        'success_pt1' => 'Erfolgreich! Überprüfe den ',
        'success_pt2' => ' Kanal für deine Testnachricht und klicke unten auf SPEICHERN, um die Einstellungen zu sichern.',
        '500' => '500 Server Fehler.',
        'error' => 'Etwas ist schiefgelaufen. :app antwortete mit: :error_message',
        'error_redirect' => 'FEHLER: 301/302 :endpoint gibt eine Umleitung zurück. Aus Sicherheitsgründen folgen wir keine Umleitungen. Bitte verwende den aktuellen Endpunkt.',
        'error_misc' => 'Etwas ist schiefgelaufen! :( ',
        'webhook_fail' => ' Webhook-Benachrichtigung fehlgeschlagen: Überprüfe, ob die URL noch gültig ist.',
        'webhook_channel_not_found' => ' Webhook-Channel nicht gefunden.',
        'ms_teams_deprecation' => 'Die ausgewählte Microsoft Teams-Webhook-URL wird am 31. Dezember 2025 abgeschaltet. Bitte nutze stattdessen eine Workflow-URL. Wie du so einen Workflow erstellst, steht in der Microsoft-Dokumentation <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">hier.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Deine Einstellungen wurden nicht gespeichert.',
        'mismatch' => 'Es gibt 1 Element in der Datenbank, das Deine Aufmerksamkeit benötigt, bevor Du die Standortbereicherung aktivieren kannst. Es gibt :count Elemente in der Datenbank, die Deine Aufmerksamkeit benötigen, bevor Du die Standortbereicherung aktivieren kannst.',
    ],
];
