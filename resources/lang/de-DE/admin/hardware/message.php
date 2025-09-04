<?php

return [

    'undeployable' 		 => 'Die folgenden Assets sind nicht Einsetzbar und wurden aus der Herausgabe entfernt: :asset_tags',
    'does_not_exist' 	 => 'Asset existiert nicht.',
    'does_not_exist_var' => 'Asset mit Tag :asset_tag nicht gefunden.',
    'no_tag' 	         => 'Kein Asset Tag angegeben.',
    'does_not_exist_or_not_requestable' => 'Dieses Asset existiert nicht oder kann nicht angefordert werden.',
    'assoc_users'	 	 => 'Dieses Asset ist im Moment an einen Benutzer herausgegeben und kann nicht entfernt werden. Bitte buchen sie das Asset wieder ein und versuchen Sie dann erneut es zu entfernen. ',
    'warning_audit_date_mismatch' 	=> 'Das nächste Prüfdatum dieses Assets (:next_audit_date) liegt vor dem letzten Prüfungsdatum (:last_audit_date). Bitte aktualisieren Sie daher das nächste Prüfungsdatum.',
    'labels_generated'   => 'Labels wurden erfolgreich generiert.',
    'error_generating_labels' => 'Fehler beim Generieren der Labels.',
    'no_assets_selected' => 'Keine Assets ausgewählt.',

    'create' => [
        'error'   		=> 'Asset wurde nicht erstellt. Bitte versuchen Sie es erneut. :(',
        'success' 		=> 'Asset wurde erfolgreich erstellt. :)',
        'success_linked' => 'Asset mit Tag :tag wurde erfolgreich erstellt. <strong><a href=":link" style="color: white;">Klicken Sie hier, um</a></strong> anzuzeigen.',
        'multi_success_linked' => 'Asset mit Tag :links wurde erfolgreich erstellt.|:count Assets wurden erfolgreich erstellt. :links.',
        'partial_failure' => 'Ein Asset konnte nicht erstellt werden. Grund: :failures|:count Assets konnten nicht erstellt werden. Gründe: :failures',
        'target_not_found' => [
            'user' => 'Der zugeordnete Benutzer konnte nicht gefunden werden.',
            'asset' => 'Das zugewiesene Asset konnte nicht gefunden werden.',
            'location' => 'Der zugeordnete Standort konnte nicht gefunden werden.',
        ],
    ],

    'update' => [
        'error'   			=> 'Asset wurde nicht aktualisiert. Bitte versuchen Sie es erneut',
        'success' 			=> 'Asset wurde erfolgreich aktualisiert.',
        'encrypted_warning' => 'Das Asset wurde erfolgreich aktualisiert, aber verschlüsselte benutzerdefinierte Felder wurden aufgrund von Berechtigungen nicht aktualisiert',
        'nothing_updated'	=>  'Es wurden keine Felder ausgewählt, somit wurde auch nichts aktualisiert.',
        'no_assets_selected'  =>  'Es wurden keine Assets ausgewählt, somit wurde auch nichts aktualisiert.',
        'assets_do_not_exist_or_are_invalid' => 'Ausgewählte Assets können nicht aktualisiert werden.',
    ],

    'restore' => [
        'error'   		=> 'Asset wurde nicht wiederhergestellt, bitte versuchen Sie es noch einmal',
        'success' 		=> 'Asset erfolgreich wiederhergestellt.',
        'bulk_success' 		=> 'Asset erfolgreich wiederhergestellt.',
        'nothing_updated'   => 'Es wurden keine Assets ausgewählt, also wurde nichts wiederhergestellt.', 
    ],

    'audit' => [
        'error'   		=> 'Asset Audit fehlgeschlagen: :error ',
        'success' 		=> 'Asset-Audit erfolgreich protokolliert.',
    ],


    'deletefile' => [
        'error'   => 'Datei wurde nicht gelöscht. Bitte versuchen Sie es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ],

    'upload' => [
        'error'   => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuchen Sie es erneut.',
        'success' => 'Datei(en) erfolgreich hochgeladen.',
        'nofiles' => 'Es wurde keine Datei zum Hochladen ausgewählt, oder die Datei ist zu groß',
        'invalidfiles' => 'Eine oder mehrere Ihrer Dateien sind zu groß oder deren Dateityp ist nicht zugelassen. Zugelassene Dateitypen sind png, gif, jpg, doc, docx, pdf, und txt.',
    ],

    'import' => [
        'import_button'         => 'Prozess Import',
        'error'                 => 'Einige Elemente wurden nicht korrekt importiert.',
        'errorDetail'           => 'Die folgenden Elemente wurden aufgrund von Fehlern nicht importiert.',
        'success'               => 'Ihre Datei wurde importiert',
        'file_delete_success'   => 'Die Datei wurde erfolgreich gelöscht',
        'file_delete_error'      => 'Die Datei konnte nicht gelöscht werden',
        'file_missing' => 'Die ausgewählte Datei fehlt',
        'file_already_deleted' => 'Die ausgewählte Datei wurde bereits gelöscht',
        'header_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der Kopfzeile enthalten fehlerhafte UTF-8 Zeichen',
        'content_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der ersten Zeile des Inhalts enthalten fehlerhafte UTF-8-Zeichen',
        'transliterate_failure' => 'Umschreibung von :encoding nach UTF-8 fehlgeschlagen wegen ungültiger Zeichen in Eingabe'
    ],


    'delete' => [
        'confirm'   	=> 'Sind Sie sicher, dass Sie dieses Asset entfernen möchten?',
        'error'   		=> 'Beim Entfernen dieses Assets ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag ist derzeit herausgegeben. Überprüfen Sie dieses Gerät bevor Sie es löschen. [2,*]Asset Tags: :asset_tag sind derzeit herausgegeben. Überprüfen Sie diese Geräte bevor Sie sie löschen.',
        'nothing_updated'   => 'Es wurden keine Assets ausgewählt, somit wurde auch nichts gelöscht.',
        'success' 		=> 'Dieses Asset wurde erfolgreich entfernt.',
    ],

    'checkout' => [
        'error'   		=> 'Asset konnte nicht herausgegeben werden. Bitte versuchen Sie es erneut',
        'success' 		=> 'Asset wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer existiert nicht. Bitte versuchen Sie es erneut.',
        'not_available' => 'Dieses Asset kann nicht herausgegeben werden!',
        'no_assets_selected' => 'Sie müssen mindestens ein Asset aus der Liste auswählen',
    ],

    'multi-checkout' => [
        'error'   => 'Asset wurde nicht ausgebucht, bitte versuchen Sie es erneut|Assets wurden nicht ausgebucht, bitte versuchen Sie es erneut',
        'success' => 'Asset erfolgreich ausgbucht.|Assets erfolgreich ausgebucht.',
    ],

    'checkin' => [
        'error'   		=> 'Asset konnte nicht zurückgenommen werden. Bitte versuchen Sie es erneut',
        'success' 		=> 'Asset wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer existiert nicht. Bitte versuchen Sie es erneut.',
        'already_checked_in'  => 'Dieses Asset ist bereits zurückgenommen.',

    ],

    'requests' => [
        'error'   		=> 'Die Anfrage war nicht erfolgreich, bitte versuchen Sie es erneut.',
        'success' 		=> 'Anfrage erfolgreich eingereicht.',
        'canceled'      => 'Anfrage erfolgreich abgebrochen.',
        'cancel'        => 'Diese Artikelanfrage abbrechen',
    ],

];
