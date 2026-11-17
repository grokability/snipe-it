<?php

return [
    'does_not_exist' => 'Firma ne postoji.',
    'deleted' => 'Obrisana kompanija',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Tvrtka nije kreirana, pokušajte ponovo.',
        'success' => 'Firma je uspešno kreirana.',
    ],
    'update' => [
        'error' => 'Firma nije ažurirana, pokušajte ponovo',
        'success' => 'Firma je uspešno ažurirana.',
    ],
    'delete' => [
        'confirm' => 'Da li ste sigurni da želite izbrisati ovu firmu?',
        'error' => 'Došlo je do problema s brisanjem firme. Molim pokušaj te ponovo.',
        'success' => 'Firma je uspešno izbrisana.',
        'bulk_success' => 'Kompanija je uspešno izbrisana.|:count kompanije su uspešno izbrisane.',
        'partial_success' => 'Kompanija je uspešno izbrisana. Pogledajte dodatne informacije ispod. | :count kompanije su uspešno izbrisane. Pogledajte dodatne informacije ispod.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
