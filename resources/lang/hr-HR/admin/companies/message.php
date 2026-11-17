<?php

return [
    'does_not_exist' => 'Tvrtka ne postoji.',
    'deleted' => 'Obrisana tvrtka',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Tvrtka nije izrađena, pokušajte ponovo.',
        'success' => 'Tvrtka je uspješno stvorena.',
    ],
    'update' => [
        'error' => 'Tvrtka nije ažurirana, pokušajte ponovo',
        'success' => 'Tvrtka je uspješno ažurirana.',
    ],
    'delete' => [
        'confirm' => 'Jeste li sigurni da želite izbrisati ovu tvrtku?',
        'error' => 'Došlo je do problema s brisanjem tvrtke. Molim te pokušaj ponovno.',
        'success' => 'Tvrtka je uspješno izbrisana.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
