<?php

return [

    'undeployable' 		 => 'Die folgenden Assets sind nicht Einsetzbar und wurden von checkout: :asset_tags entfernt',
    'does_not_exist' 	 => 'Asset existiert nicht.',
    'does_not_exist_var' => 'Asset mit Asset-Tag :asset_tag nicht gefunden.',
    'no_tag' 	         => 'Kein Asset-Tag angegeben.',
    'does_not_exist_or_not_requestable' => 'Dieses Asset existiert nicht oder kann nicht angefordert werden.',
    'assoc_users'	 	 => 'Dieses Asset ist im Moment an einen Benutzer herausgegeben und kann nicht entfernt werden. Bitte buche das Asset wieder ein und versuche dann erneut, es zu entfernen. ',
    'warning_audit_date_mismatch' 	=> 'Das nächste Prüfdatum dieses Assets (:next_audit_date) liegt vor dem letzten Prüfdatum (:last_audit_date). Bitte aktualisieren Sie das nächste Prüfdatum.',
    'labels_generated'   => 'Labels wurden erfolgreich generiert.',
    'error_generating_labels' => 'Fehler beim Generieren der Labels.',
    'no_assets_selected' => 'Keine Assets ausgewählt.',

    'create' => [
        'error'   		=> 'Asset wurde nicht erstellt. Bitte versuche es erneut. :(',
        'success' 		=> 'Asset wurde erfolgreich erstellt. :)',
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
        'error'   			=> 'Asset wurde nicht aktualisiert. Bitte versuche es erneut',
        'success' 			=> 'Asset wurde erfolgreich aktualisiert.',
        'encrypted_warning' => 'Das Asset wurde erfolgreich aktualisiert, aber verschlüsselte benutzerdefinierte Felder wurden aufgrund von Berechtigungen nicht aktualisiert',
        'nothing_updated'	=>  'Es wurden keine Felder ausgewählt, somit wurde auch nichts aktualisiert.',
        'no_assets_selected'  =>  'Es wurden keine Assets ausgewählt, somit wurde auch nichts aktualisiert.',
        'assets_do_not_exist_or_are_invalid' => 'Ausgewählte Assets können nicht aktualisiert werden.',
    ],

    'restore' => [
        'error'   		=> 'Asset wurde nicht wiederhergestellt, bitte versuche es noch einmal',
        'success' 		=> 'Asset erfolgreich wiederhergestellt.',
        'bulk_success' 		=> 'Asset erfolgreich wiederhergestellt.',
        'nothing_updated'   => 'Es wurden keine Assets ausgewählt, also wurde nichts wiederhergestellt.', 
    ],

    'audit' => [
        'error'   		=> 'Asset Prüfung fehlgeschlagen: :error ',
        'success' 		=> 'Asset-Audit erfolgreich protokolliert.',
    ],


    'deletefile' => [
        'error'   => 'Datei wurde nicht gelöscht. Bitte versuche es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ],

    'upload' => [
        'error'   => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuche es erneut.',
        'success' => 'Datei(en) wurden erfolgreich hochgeladen.',
        'nofiles' => 'Du hast keine Datei zum Hochladen ausgewählt, oder die Datei, die du hochladen möchtest, ist zu groß',
        'invalidfiles' => 'Eine oder mehrere Deiner Dateien sind zu groß, oder deren Dateityp ist nicht zugelassen. Zugelassene Dateitypen sind png, gif, jpg, doc, docx, pdf, und txt.',
    ],

    'import' => [
        'import_button'         => 'Importvorgang',
        'error'                 => 'Einige Elemente wurden nicht korrekt importiert.',
        'errorDetail'           => 'Die folgenden Elemente wurden aufgrund von Fehlern nicht importiert.',
        'success'               => 'Deine Datei wurde importiert',
        'file_delete_success'   => 'Deine Datei wurde erfolgreich gelöscht',
        'file_delete_error'      => 'Die Datei konnte nicht gelöscht werden',
        'file_missing' => 'Die ausgewählte Datei fehlt',
        'file_already_deleted' => 'Die ausgewählte Datei wurde bereits gelöscht',
        'header_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der Kopfzeile enthalten fehlerhafte UTF-8 Zeichen',
        'content_row_has_malformed_characters' => 'Ein oder mehrere Attribute in der ersten Zeile des Inhalts enthalten fehlerhafte UTF-8-Zeichen',
        'transliterate_failure' => 'Umschreibung von :encoding nach UTF-8 fehlgeschlagen wegen ungültiger Zeichen in Eingabe'
    ],


    'delete' => [
        'confirm'   	=> 'Bist du sicher, dass du dieses Asset entfernen möchtest?',
        'error'   		=> 'Beim Entfernen dieses Assets ist ein Fehler aufgetreten. Bitte versuche es erneut.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag ist derzeit ausgebucht. Überprüfe dieses Gerät bevor Du es löschst. [2,*]Asset Tags: :asset_tag sind derzeit ausgebucht. Überprüfe diese Geräte bevor Du sie löschst.',
        'nothing_updated'   => 'Es wurden keine Assets ausgewählt, somit wurde auch nichts gelöscht.',
        'success' 		=> 'Dass Asset wurde erfolgreich entfernt.',
    ],

    'checkout' => [
        'error'   		=> 'Asset konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' 		=> 'Asset wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'not_available' => 'Dieses Asset kann nicht herausgegeben werden!',
        'no_assets_selected' => 'Du musst mindestens ein Asset aus der Liste auswählen',
    ],

    'multi-checkout' => [
        'error'   => 'Asset wurde nicht ausgecheckt, bitte versuche es erneut.|Assets wurden nicht ausgecheckt, bitte versuche es erneut',
        'success' => 'Asset erfolgreich ausgecheckt.|Assets erfolgreich ausgecheckt.',
    ],

    'checkin' => [
        'error'   		=> 'Asset konnte nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' 		=> 'Asset wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'already_checked_in'  => 'Dieses Asset ist bereits zurückgenommen.',

    ],

    'requests' => [
        'error'   		=> 'Das Asset wurde nicht angefordert, bitte versuche es erneut.',
        'success' 		=> 'Anfrage erfolgreich eingereicht.',
        'canceled'      => 'Anfrage erfolgreich abgebrochen.',
        'cancel'        => 'Storniere diese Gegenstandanfrage',
    ],

];
