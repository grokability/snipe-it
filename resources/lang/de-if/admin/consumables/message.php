<?php

return [

    'invalid_category_type' => 'Die Kategorie muss eine Kategorie mit Verbrauchsmaterialien sein.',
    'does_not_exist' => 'Verbrauchsmaterial existiert nicht.',

    'create' => [
        'error' => 'Verbrauchsmaterial konnte nicht angelegt werden. Bitte versuche es erneut.',
        'success' => 'Verbrauchsmaterial erfolgreich angelegt.',
    ],

    'update' => [
        'error' => 'Verbrauchsmaterial konnte nicht aktualisiert werden. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du dieses Verbrauchsmaterial löschen möchtest?',
        'error' => 'Es gab ein Problem beim Löschen des Verbrauchsmaterials. Bitte versuche es erneut.',
        'success' => 'Das Verbrauchsmaterial wurde erfolgreich gelöscht.',
    ],

    'checkout' => [
        'error' => 'Das Verbrauchsmaterial wurde nicht herausgegeben. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'unavailable' => 'Es sind nicht genügend Verbrauchsmaterialien für diese Herausgabe vorhanden. Bitte überprüfe die verbleibende Menge. ',
    ],

    'checkin' => [
        'error' => 'Das Verbrauchsmaterial wurde nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
    ],

];
