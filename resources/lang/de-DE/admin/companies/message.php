<?php

return [
    'does_not_exist' => 'Firma existiert nicht.',
    'deleted' => 'Gelöschte Firma',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Firma wurde nicht erstellt, bitte versuchen Sie es noch einmal.',
        'success' => 'Firma wurde erfolgreich angelegt.',
    ],
    'update' => [
        'error' => 'Firma wurde nicht aktualisiert, bitte versuchen Sie es erneut',
        'success' => 'Firma erfolgreich aktualisiert.',
    ],
    'delete' => [
        'confirm' => 'Sind Sie sich sicher, dass Sie diese Firma löschen wollen?',
        'error' => 'Es gab ein Problem beim Löschen der Firma. Bitte versuchen Sie es erneut.',
        'success' => 'Die Firma wurde erfolgreich gelöscht.',
        'bulk_success' => 'Firma erfolgreich gelöscht.|:count Firmen wurden erfolgreich gelöscht.',
        'partial_success' => 'Firma wurde erfolgreich gelöscht. Siehe weitere Informationen unten. | :count Firmen wurden erfolgreich gelöscht. Siehe weitere Informationen unten.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
