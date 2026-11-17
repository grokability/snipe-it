<?php

return [

    'undeployable' => 'Die folgenden Assets sind nicht Einsetzbar und wurden von checkout: :asset_tags entfernt',
    'does_not_exist' => 'Asset existiert nicht.',
    'does_not_exist_var' => 'Asset mit Asset-Tag :asset_tag nicht gefunden.',
    'no_tag' => 'Kein Asset-Tag angegeben.',
    'does_not_exist_or_not_requestable' => 'Dieses Asset existiert nicht oder kann nicht angefordert werden.',
    'assoc_users' => 'Dieses Asset ist im Moment an einen Benutzer herausgegeben und kann nicht entfernt werden. Bitte buche das Asset wieder ein und versuche dann erneut, es zu entfernen. ',
    'warning_audit_date_mismatch' => 'Das nächste Prüfdatum dieses Assets (:next_audit_date) liegt vor dem letzten Prüfdatum (:last_audit_date). Bitte aktualisieren Sie das nächste Prüfdatum.',
    'labels_generated' => 'Labels wurden erfolgreich generiert.',
    'error_generating_labels' => 'Fehler beim Generieren der Labels.',
    'no_assets_selected' => 'Keine Assets ausgewählt.',

    'create' => [
        'error' => 'Asset wurde nicht erstellt. Bitte versuche es erneut. :(',
        'success' => 'Asset wurde erfolgreich erstellt. :)',
        'success_no_checkout' => 'Asset wurde erfolgreich angelegt, wurde aber nicht ausgecheckt, da Sie keine Berechtigung zum Auschecken von Assets haben.',
        'checkout_skipped_no_permission' => 'Das Asset wurde angelegt, aber nicht an das angeforderte Ziel ausgecheckt, da Sie nicht die Berechtigung haben, Assets auszuchecken.',
        'success_linked' => 'Asset mit Tag :tag wurde erfolgreich erstellt. <strong><a href=":link" style="color: white;">Klicke hier, um</a></strong> anzuzeigen.',
        'multi_success_linked' => 'Asset mit Tag :links wurde erfolgreich erstellt.|:count Assets wurden erfolgreich erstellt. :links.',
        'partial_failure' => 'Ein Asset konnte nicht erstellt werden. Grund: :failures|:count Assets konnten nicht erstellt werden. Gründe: :failures',
        'target_not_found' => [
            'user' => 'Der zugeordnete Benutzer konnte nicht gefunden werden.',
            'asset' => 'Das zugewiesene Asset konnte nicht gefunden werden.',
            'location' => 'Der zugeordnete Standort konnte nicht gefunden werden.',
        ],
    ],

    'update' => [
        'error' => 'Asset wurde nicht aktualisiert. Bitte versuche es erneut',
        'success' => 'Asset wurde erfolgreich aktualisiert.',
        'encrypted_warning' => 'Das Asset wurde erfolgreich aktualisiert, aber verschlüsselte benutzerdefinierte Felder wurden aufgrund von Berechtigungen nicht aktualisiert',
        'nothing_updated' => 'Es wurden keine Felder ausgewählt, somit wurde auch nichts aktualisiert.',
        'no_assets_selected' => 'Es wurden keine Assets ausgewählt, somit wurde auch nichts aktualisiert.',
        'assets_do_not_exist_or_are_invalid' => 'Ausgewählte Assets können nicht aktualisiert werden.',
    ],

    'bulk_update' => [
        'success' => 'Asset erfolgreich aktualisiert.|:count Assets wurden erfolgreich aktualisiert.',
        'partial' => ':success Asset(s) erfolgreich aktualisiert, :failed fehlgeschlagen. Siehe das Ergebnisarray für Details.',
        'error' => 'Keine Assets wurden aktualisiert. Siehe das Ergebnisarray für Details.',
    ],

    'restore' => [
        'error' => 'Asset wurde nicht wiederhergestellt, bitte versuche es noch einmal',
        'success' => 'Asset erfolgreich wiederhergestellt.',
        'bulk_success' => 'Asset erfolgreich wiederhergestellt.',
        'nothing_updated' => 'Es wurden keine Assets ausgewählt, also wurde nichts wiederhergestellt.',
    ],

    'audit' => [
        'error' => 'Asset Prüfung fehlgeschlagen: :error ',
        'success' => 'Asset-Audit erfolgreich protokolliert.',
    ],

    'deletefile' => [
        'error' => 'Datei wurde nicht gelöscht. Bitte versuche es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ],

    'upload' => [
        'error' => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuche es erneut.',
        'success' => 'Datei(en) wurden erfolgreich hochgeladen.',
        'nofiles' => 'Du hast keine Datei zum Hochladen ausgewählt, oder die Datei, die du hochladen möchtest, ist zu groß',
        'invalidfiles' => 'Eine oder mehrere Deiner Dateien sind zu groß, oder deren Dateityp ist nicht zugelassen. Zugelassene Dateitypen sind png, gif, jpg, doc, docx, pdf, und txt.',
    ],

    'import' => [
        'import_button' => 'Importvorgang',
        'error' => 'Einige Elemente wurden nicht korrekt importiert.',
        'errorDetail' => 'Die folgenden Elemente wurden aufgrund von Fehlern nicht importiert.',
        'success' => 'Deine Datei wurde importiert',
        'file_delete_success' => 'Deine Datei wurde erfolgreich gelöscht',
        'file_delete_error' => 'Die Datei konnte nicht gelöscht werden',
        'file_missing' => 'Die ausgewählte Datei fehlt',
        'file_already_deleted' => 'Die ausgewählte Datei wurde bereits gelöscht',
        'file_missing_on_disk' => 'Die Datei für diesen Import ist nicht mehr auf der Festplatte. Sie wurde möglicherweise außerhalb von Snipe-IT gelöscht. Diesen Eintrag löschen und die Datei erneut hochladen, um es erneut zu versuchen.',
        'file_empty' => 'Diese Datei hat keine Datenzeilen. Nichts kann daraus importiert werden.',
        'already_processing' => 'Dieser Import wird gerade von einem anderen Benutzer bearbeitet. Bitte warten Sie, bis er beendet ist, bevor Sie es erneut versuchen.',
        'header_row_missing' => 'Diese Datei hat keine erkannte Header-Zeile. Löschen Sie diesen Eintrag und laden Sie die Datei erneut hoch, um es erneut zu versuchen.',
        'header_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der Kopfzeile enthalten fehlerhafte UTF-8 Zeichen',
        'content_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der ersten Zeile des Inhalts enthalten fehlerhafte UTF-8-Zeichen',
        'transliterate_failure' => 'Umschreibung von :encoding nach UTF-8 fehlgeschlagen wegen ungültiger Zeichen in Eingabe',
        'bulk_delete' => [
            'button' => 'Ausgewählte löschen (:count)',
            'confirm_title' => 'Ausgewählte Importdateien löschen?',
            'confirm_body' => 'Sie sind dabei, :count Importdatei(en) dauerhaft zu löschen. Dies kann nicht rückgängig gemacht werden.',
            'confirm_button' => 'Löschen',
            'success' => 'Importdatei erfolgreich gelöscht.|:count Importdateien wurden erfolgreich gelöscht.',
            'skipped' => ':count Datei(en) wurden übersprungen, da Sie keine Berechtigung zum Löschen haben.',
            'select_all' => 'Alle Dateien auf dieser Seite auswählen',
            'select_row' => 'Wählen Sie :file für Massenlöschung',
        ],
        'row_count' => '{0} Keine Datenzeilen in dieser Datei|{1} :count Datenzeile zum Importieren|[2,*] :count Datenzeilen zum Importieren',
        'summary' => [
            'created' => 'Erstellt',
            'updated' => 'Aktualisiert',
            'skipped' => 'Übersprungen als Duplikate',
            'errored' => 'Fehler',
            'no_changes' => 'Der Import wurde beendet, aber es wurde nichts erstellt oder aktualisiert. Jede Zeile wurde übersprungen, normalerweise weil die zugrunde liegenden Datensätze bereits existierten. Prüfen Sie die Zähler unten und passen Sie die CSV- oder Importart an, wenn das nicht das ist, was Sie erwartet haben.',
        ],
        'type_required' => 'Bitte wählen Sie einen Import-Typ, bevor Sie fortfahren.',
        'processing' => 'Ihr Import wird verarbeitet. Bitte warten Sie, bis dieser Vorgang beendet ist, bevor Sie die Seite schließen.',
        'backup_running' => 'Sicherung wird vor dem Importieren ausgeführt. Dies kann eine Weile dauern. Bitte warten.',
        'backup_label' => 'Sicherung vor dem Import',
        'backup_complete' => 'Sicherung abgeschlossen',
        'import_label' => 'Import',
        'required_fields_missing' => 'Die folgenden Pflichtfelder sind nicht zugeordnet: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(Fehlendes Asset Tag)',
            'missing_asset_tag_message' => 'Zeile übersprungen: kein Asset Tag angegeben.',
            'asset_not_found_message' => 'Asset mit diesem Tag existiert nicht. Importiere zuerst Assets und führe den Verlauf-Import erneut durch.',
            'target_not_matched_message' => 'Kein :target_type stimmt mit ":name" überein. Für Benutzer schalten Sie die Match-by-Optionen in Schritt 1 ein oder erstellen Sie zuerst den Benutzer. Stellen Sie bei Standorten sicher, dass der CSV-Standortname genau mit einem vorhandenen Standort übereinstimmt.',
            'invalid_target_type_message' => 'Zieltyp ":value" ist nicht erkannt. Benutzen Sie "Benutzer" oder "Standort" oder lassen Sie die Spalte leer, um den Benutzer automatisch voreinzustellen.',
        ],
        'wizard' => [
            'step_type' => 'Typ auswählen',
            'step_map' => 'Felder zuordnen',
            'step_preview' => 'Vorschau',
            'back' => 'Zurück',
            'next' => 'Nächste',
            'preview_button' => 'Vorschau',
            'process' => 'Import verarbeiten',
            'preview_intro' => 'Vorschau der ersten :count Zeile(n) nach Anwendung Ihrer Zuordnung. Verwenden Sie die Zurück-Taste, wenn Sie die zugeordneten Attribute vor dem Import bearbeiten müssen.',
        ],
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du dieses Asset entfernen möchtest?',
        'error' => 'Beim Entfernen dieses Assets ist ein Fehler aufgetreten. Bitte versuche es erneut.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag ist derzeit ausgebucht. Überprüfe dieses Gerät bevor Du es löschst. [2,*]Asset Tags: :asset_tag sind derzeit ausgebucht. Überprüfe diese Geräte bevor Du sie löschst.',
        'nothing_updated' => 'Es wurden keine Assets ausgewählt, somit wurde auch nichts gelöscht.',
        'success' => 'Dass Asset wurde erfolgreich entfernt.',
    ],

    'checkout' => [
        'error' => 'Asset konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' => 'Asset wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'not_available' => 'Dieses Asset kann nicht herausgegeben werden!',
        'no_assets_selected' => 'Du musst mindestens ein Asset aus der Liste auswählen',
    ],

    'multi-checkout' => [
        'error' => 'Asset wurde nicht ausgecheckt, bitte versuche es erneut.|Assets wurden nicht ausgecheckt, bitte versuche es erneut',
        'success' => 'Asset erfolgreich ausgecheckt.|Assets erfolgreich ausgecheckt.',
    ],

    'multi-checkin' => [
        'error' => 'Asset wurde nicht zurückgenommen, bitte versuchen Sie es erneut|Assets wurden nicht zurückgenommen, bitte versuchen Sie es erneut',
        'success' => 'Asset erfolgreich zurückgenommen.|Assets erfolgreich zurückgenommen.',
        'no_assets_selected' => 'Du musst mindestens ein Asset aus der Liste auswählen',
    ],

    'multi-audit' => [
        'success' => ':count Asset erfolgreich geprüft.|:count Assets erfolgreich geprüft.',
        'partial_error' => ':success Asset geprüft :failed fehlgeschlagen. Prüfen Sie die Fehler unten und versuchen Sie es erneut.|:success Assets geprüft, :failed fehlgeschlagen. Prüfen Sie die Fehler unten und versuchen Sie es erneut.',
        'no_assets_selected' => 'Du musst mindestens ein Asset aus der Liste auswählen',
    ],

    'checkin' => [
        'error' => 'Asset konnte nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' => 'Asset wurde erfolgreich zurückgenommen.',
        'updated_without_checkin' => 'Asset wurde bereits eingecheckt, aber die von Ihnen festgelegten Formularfelder (Status, Lage, Name, Notizen) wurden trotzdem angewendet.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'already_checked_in' => 'Dieses Asset ist bereits zurückgenommen.',
        'force_checkin_orphaned_success' => 'Ungültige Zuordnung erfolgreich gelöscht.',
        'force_checkin_not_orphaned' => 'Gegenstand ist nicht in einem ungültigen Zuweisungszustand.',
        'force_checkin_error' => 'Ungültige Zuordnung konnte nicht gelöscht werden.',

    ],

    'requests' => [
        'error' => 'Das Asset wurde nicht angefordert, bitte versuche es erneut.',
        'success' => 'Anfrage erfolgreich eingereicht.',
        'canceled' => 'Anfrage erfolgreich abgebrochen.',
        'cancel' => 'Storniere diese Gegenstandanfrage',
        'duplicate' => 'Sie haben bereits eine aktive Anfrage für dieses Element.',
        'no_active' => 'Sie haben keine aktive Anfrage, um diese abbrechen zu können.',
        'insufficient_stock' => 'Nicht genug Bestand, um diese Anfrage zu erfüllen. Zuerst Bestand aufstocken.',
        'confirm_cancel_by_admin' => ":user's Anfrage für :item abbrechen?",
        'no_selection' => 'Es wurden keine Einträge ausgewählt',
        'row_stale' => 'Anfrage #:id ist nicht mehr ausstehend und wurde übersprungen.',
        'row_qty_invalid' => 'Anfrage #:id hat eine ungültige Menge und wurde übersprungen.',
        'row_user_missing' => 'Anfrage #:id hat keinen gültigen Zielbenutzer und wurde übersprungen.',
        'row_asset_missing' => 'Request #:id hat kein gültiges Ziel-Asset und wurde übersprungen.',
        'row_asset_not_requesters' => 'Anfrage #:id übersprungen: Das ausgewählte Asset gehört nicht zum anfragenden Benutzer.',
        'row_asset_not_available' => 'Anfrage #:id übersprungen: Das ausgewählte Asset ist keine verfügbare Einheit dieses Modells.',
        'row_asset_taken' => 'Anfrage #:id übersprungen: Das ausgewählte Asset wurde einer anderen Person zugewiesen, bevor es abgeschickt wurde.',
        'row_company_mismatch' => 'Anfrage #:id übersprungen: :user kann keine Artikel von dieser Firma empfangen.',
        'row_over_allocated' => 'Anfrage #:id übersprungen: Nicht genug verfügbarer Bestand beim Absenden vorhanden.',
        'no_target_assets_for_user' => ':user hat keine Assets zum installieren.',
        'no_available_units' => 'Keine verfügbare Anzahl von Einheiten dieses Modells zum Herausgeben verfügbar.',
        'bulk_summary' => ':fulfilled von :total Anfragen erfüllt.',
        'bulk_fulfill_notification_intro' => 'Folgendes gilt für jede angekreuzte Anfrage, wenn sie erfüllt wird:',
    ],

];
