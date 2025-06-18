<?php

return [

    'update' => [
        'error'                 => 'Während der Aktualisierung ist ein Fehler aufgetreten. ',
        'success'               => 'Die Einstellungen wurden erfolgreich aktualisiert.',
    ],
    'backup' => [
        'delete_confirm'        => 'Backup Datei wirklich löschen? Aktion kann nicht rückgängig gemacht werden. ',
        'file_deleted'          => 'Backup Datei erfolgreich gelöscht. ',
        'generated'             => 'Backup Datei erfolgreich erstellt.',
        'file_not_found'        => 'Backup Datei konnte nicht gefunden werden.',
        'restore_warning'       => 'Ja, wiederherstellen. Ich bestätige, dass dies alle vorhandenen Daten überschreibt, die derzeit in der Datenbank vorhanden sind. Diese Aktion wird auch alle bestehenden Benutzer abmelden (einschließlich Ihnen).',
        'restore_confirm'       => 'Sind Sie sicher, dass Sie Ihre Datenbank aus :filename wiederherstellen möchten?'
    ],
    'restore' => [
        'success'               => 'Ihr Systembackup wurde wiederhergestellt. Bitte melden Sie sich erneut an.'
    ],
    'purge' => [
        'error'     => 'Beim Bereinigen ist ein Fehler augetreten. ',
        'validation_failed'     => 'Falsche Bereinigungsbestätigung. Bitte geben Sie das Wort "DELETE" im Bestätigungsfeld ein.',
        'success'               => 'Gelöschte Einträge erfolgreich bereinigt.',
    ],
    'mail' => [
        'sending' => 'Test E-Mail wird gesendet...',
        'success' => 'Mail gesendet!',
        'error' => 'E-Mail konnte nicht gesendet werden.',
        'additional' => 'Keine zusätzliche Fehlermeldung vorhanden. Überprüfen Sie Ihre E-Mail-Einstellungen und Ihr App-Protokoll.'
    ],
    'ldap' => [
        'testing' => 'Teste LDAP Verbindung, Binding & Abfrage ...',
        '500' => '500 Serverfehler. Bitte überprüfen Sie Ihre Server-Logs für weitere Informationen.',
        'error' => 'Etwas ist schiefgelaufen :(',
        'sync_success' => 'Ein Beispiel von 10 Benutzern, die vom LDAP-Server basierend auf Ihren Einstellungen zurückgegeben wurden:',
        'testing_authentication' => 'LDAP-Authentifizierung wird getestet...',
        'authentication_success' => 'Benutzer wurde erfolgreich gegen LDAP authentifiziert!'
    ],
    'labels' => [
        'null_template' => 'Etikettenvorlage nicht gefunden. Bitte wählen Sie eine Vorlage aus.',
        ],
    'webhook' => [
        'sending' => ':app Testnachricht wird gesendet...',
        'success' => 'Ihre :webhook_name Integration funktioniert!',
        'success_pt1' => 'Erfolgreich! Überprüfen Sie den ',
        'success_pt2' => ' Kanal für Ihre Testnachricht und klicken Sie auf Speichern, um Ihre Einstellungen zu speichern.',
        '500' => '500 Server Error.',
        'error' => 'Etwas ist schief gelaufen. :app antwortete mit: :error_message',
        'error_redirect' => 'FEHLER: 301/302 :endpoint gibt eine Umleitung zurück. Aus Sicherheitsgründen folgen wir keinen Umleitungen. Bitte verwenden Sie den aktuellen Endpunkt.',
        'error_misc' => 'Etwas ist schiefgelaufen. :( ',
        'webhook_fail' => ' Webhook-Benachrichtigung fehlgeschlagen: Überprüfen Sie, ob die URL noch gültig ist.',
        'webhook_channel_not_found' => ' Webhook-Channel nicht gefunden.',
        'ms_teams_deprecation' => 'Die ausgewählte Microsoft Teams-Webhook-URL wird zum 31. Dezember 2025 eingestellt. Bitte verwenden Sie stattdessen eine Workflow-URL. Die Dokumentation von Microsoft zur Erstellung eines Workflows finden Sie <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">hier.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Ihre Einstellungen wurden nicht gespeichert.',
        'mismatch' => 'Es gibt 1 Element in der Datenbank, das Ihre Aufmerksamkeit benötigt, bevor Sie die Standortbereicherung aktivieren können. Es gibt :count Elemente in der Datenbank, die Ihre Aufmerksamkeit benötigen, bevor Sie die Standortbereicherung aktivieren können.',
    ],
];
