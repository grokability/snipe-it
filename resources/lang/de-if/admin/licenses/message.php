<?php

return array(

    'does_not_exist' => 'Die Lizenz existiert nicht oder du hast keine Berechtigung, sie anzusehen.',
    'user_does_not_exist' => 'Benutzer existiert nicht oder Sie haben keine Berechtigung, sie anzusehen.',
    'asset_does_not_exist' 	=> 'Der Gegenstand, mit dem du diese Lizenz verknüpfen möchtest, existiert nicht.',
    'owner_doesnt_match_asset' => 'Der Gegenstand, den du mit dieser Lizenz verknüpfen möchtest, gehört jemand anderem als der im Dropdown-Feld ausgewählten Person.',
    'assoc_users'	 => 'Diese Lizenz ist derzeit einem Benutzer zugeordnet und kann nicht gelöscht werden. Bitte nimm die Lizenz zurück und versuche anschließend erneut, diese zu löschen. ',
    'select_asset_or_person' => 'Du musst ein Asset oder einen Benutzer auswählen, aber nicht beides.',
    'not_found' => 'Lizenz nicht gefunden',
    'seats_available' => ':seat_count Plätze verfügbar',


    'create' => array(
        'error'   => 'Lizenz wurde nicht erstellt, bitte versuche es erneut.',
        'success' => 'Die Lizenz wurde erfolgreich erstellt.'
    ),

    'deletefile' => array(
        'error'   => 'Datei wurde nicht gelöscht. Bitte versuche es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ),

    'upload' => array(
        'error'   => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuche es erneut.',
        'success' => 'Datei(en) wurden erfolgreich hochgeladen.',
        'nofiles' => 'Du hast keine Datei zum Hochladen ausgewählt, oder die Datei, die du hochladen möchtest, ist zu groß',
        'invalidfiles' => 'Eine oder mehrere Deiner Dateien sind zu groß oder ist ein Dateityp, der nicht zulässig ist. Erlaubte Dateitypen sind png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml und lic.',
    ),

    'update' => array(
        'error'   => 'Die Lizenz wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Die Lizenz wurde erfolgreich aktualisiert.'
    ),

    'delete' => array(
        'confirm'   => 'Bist du sicher, dass du diese Lizenz löschen willst?',
        'error'   => 'Beim Löschen der Lizenz ist ein Problem aufgetreten. Bitte versuche es erneut.',
        'success' => 'Die Lizenz wurde erfolgreich gelöscht.'
    ),

    'checkout' => array(
        'error'   => 'Lizenz wurde nicht herausgegeben, bitte versuche es erneut.',
        'success' => 'Lizenz wurde erfolgreich herausgegeben',
        'not_enough_seats' => 'Nicht genügend Lizenz-Plätze zur Herausgabe verfügbar',
        'mismatch' => 'Die bereitgestellte Lizenzplatzierung entspricht nicht der Lizenz',
        'unavailable' => 'Dieser Platz ist nicht zur Ausleihe verfügbar.',
        'license_is_inactive' => 'Diese Lizenz ist abgelaufen oder beendet.',
    ),

    'checkin' => array(
        'error'   => 'Lizenz wurde nicht zurückgenommen, bitte versuche es erneut.',
        'not_reassignable' => 'Platz wurde verwendet',
        'success' => 'Die Lizenz wurde erfolgreich zurückgenommen'
    ),

);
