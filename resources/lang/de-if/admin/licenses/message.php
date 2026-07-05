<?php

return [

    'does_not_exist' => 'Die Lizenz existiert nicht oder du hast keine Berechtigung, sie anzusehen.',
    'user_does_not_exist' => 'Benutzer existiert nicht oder Sie haben keine Berechtigung, sie anzusehen.',
    'asset_does_not_exist' => 'Der Gegenstand, mit dem du diese Lizenz verknüpfen möchtest, existiert nicht.',
    'owner_doesnt_match_asset' => 'Der Gegenstand, den du mit dieser Lizenz verknüpfen möchtest, gehört jemand anderem als der im Dropdown-Feld ausgewählten Person.',
    'assoc_users' => 'Diese Lizenz ist derzeit einem Benutzer zugeordnet und kann nicht gelöscht werden. Bitte nimm die Lizenz zurück und versuche anschließend erneut, diese zu löschen. ',
    'select_asset_or_person' => 'Du musst ein Asset oder einen Benutzer auswählen, aber nicht beides.',
    'not_found' => 'Lizenz nicht gefunden',
    'seats_available' => ':seat_count Plätze verfügbar',

    'create' => [
        'error' => 'Lizenz wurde nicht erstellt, bitte versuche es erneut.',
        'success' => 'Die Lizenz wurde erfolgreich erstellt.',
    ],

    'deletefile' => [
        'error' => 'Datei wurde nicht gelöscht. Bitte versuche es erneut.',
        'success' => 'Datei erfolgreich gelöscht.',
    ],

    'upload' => [
        'error' => 'Datei(en) wurde(n) nicht hochgeladen. Bitte versuche es erneut.',
        'success' => 'Datei(en) wurden erfolgreich hochgeladen.',
        'nofiles' => 'Du hast keine Datei zum Hochladen ausgewählt, oder die Datei, die du hochladen möchtest, ist zu groß',
        'invalidfiles' => 'Eine oder mehrere Deiner Dateien sind zu groß oder ist ein Dateityp, der nicht zulässig ist. Erlaubte Dateitypen sind png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml und lic.',
    ],

    'update' => [
        'error' => 'Die Lizenz wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Die Lizenz wurde erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du diese Lizenz löschen willst?',
        'error' => 'Beim Löschen der Lizenz ist ein Problem aufgetreten. Bitte versuche es erneut.',
        'success' => 'Die Lizenz wurde erfolgreich gelöscht.',
        'bulk_success' => 'Die ausgewählten Lizenzen wurden erfolgreich gelöscht.',
        'partial_success' => 'Lizenz erfolgreich gelöscht. Siehe weitere Informationen unten. | :count Lizenzen wurden erfolgreich gelöscht. Siehe weitere Informationen unten.',
        'bulk_checkout_warning' => ':license_name has seats that are currently checked out and cannot be deleted. Please check in all seats before deleting.',
    ],

    'checkout' => [
        'error' => 'Lizenz wurde nicht herausgegeben, bitte versuche es erneut.',
        'success' => 'Lizenz wurde erfolgreich herausgegeben',
        'not_enough_seats' => 'Nicht genügend Lizenz-Plätze zur Herausgabe verfügbar',
        'mismatch' => 'Die bereitgestellte Lizenzplatzierung entspricht nicht der Lizenz',
        'unavailable' => 'Dieser Platz ist nicht zur Ausleihe verfügbar.',
        'license_is_inactive' => 'Diese Lizenz ist abgelaufen oder beendet.',
    ],

    'checkin' => [
        'error' => 'Lizenz wurde nicht zurückgenommen, bitte versuche es erneut.',
        'not_reassignable' => 'Platz wurde verwendet',
        'success' => 'Die Lizenz wurde erfolgreich zurückgenommen',
    ],

];
