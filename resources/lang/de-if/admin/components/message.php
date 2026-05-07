<?php

return [

    'does_not_exist' => 'Komponente existiert nicht.',

    'create' => [
        'error' => 'Komponente wurde nicht erstellt. Bitte versuche es erneut.',
        'success' => 'Komponente wurde erfolgreich erstellt.',
    ],

    'update' => [
        'error' => 'Komponente wurde nicht aktualisiert. Bitte versuche es erneut',
        'success' => 'Komponente erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du diese Komponente löschen möchtest?',
        'error' => 'Es gab ein Problem beim Löschen der Firma. Bitte versuche es erneut.',
        'success' => 'Die Komponente wurde erfolgreich gelöscht.',
        'error_qty' => 'Einige Komponenten dieses Typs sind noch herausgegeben. Bitte nehmen Sie sie zurück und versuchen Sie es erneut.',
    ],

    'checkout' => [
        'error' => 'Komponente konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' => 'Komponente wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'unavailable' => 'Nicht genügend verbleibende Komponenten: :remaining verbleibend, :requested angefordert ',
    ],

    'checkin' => [
        'error' => 'Komponente konnte nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' => 'Komponente wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
    ],

];
