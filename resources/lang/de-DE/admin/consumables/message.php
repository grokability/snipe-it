<?php

return [

    'invalid_category_type' => 'Diese Kategorie muss eine Kategorie vom Typ Verbrauchsmaterialien sein.',
    'does_not_exist' => 'Verbrauchsmaterial existiert nicht.',

    'create' => [
        'error' => 'Verbrauchsmaterial konnte nicht angelegt werden. Bitte versuchen Sie es erneut.',
        'success' => 'Verbrauchsmaterial erfolgreich angelegt.',
    ],

    'update' => [
        'error' => 'Verbrauchsmaterial konnte nicht aktualisiert werden. Bitte versuchen Sie es erneut',
        'success' => 'Verbrauchsmaterial erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Sind Sie sicher, dass Sie dieses Verbrauchsmaterial löschen möchten?',
        'error' => 'Es gab Probleme beim Löschen des Verbrauchsmaterials. Bitte versuchen Sie es erneut.',
        'success' => 'Das Verbrauchsmaterial wurde erfolgreich gelöscht.',
    ],

    'checkout' => [
        'error' => 'Das Verbrauchsmaterial wurde nicht herausgegeben. Bitte versuchen Sie es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuchen Sie es noch einmal.',
        'unavailable' => 'Es sind nicht genügend Verbrauchsmaterialien für diese Herausgabe vorhanden. Bitte überprüfen Sie die verbleibende Menge. ',
    ],

    'checkin' => [
        'error' => 'Das Verbrauchsmaterial konnte nicht zurückgenommen werden. Bitte versuchen Sie es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Der angegebene Benutzer existiert nicht. Bitte versuchen Sie es erneut.',
    ],

];
