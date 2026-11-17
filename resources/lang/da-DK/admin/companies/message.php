<?php

return [
    'does_not_exist' => 'Virksomheden eksisterer ikke.',
    'deleted' => 'Slettet virksomhed',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Virksomheden blev ikke oprettet, prøve igen.',
        'success' => 'Virksomhed oprettet.',
    ],
    'update' => [
        'error' => 'Virksomheden blev ikke opdateret, prøv igen',
        'success' => 'Virksomheden blev opdateret.',
    ],
    'delete' => [
        'confirm' => 'Er du sikker på du vil slette denne virksomhed?',
        'error' => 'Der opstod et problem under sletning af virksomheden. Prøv igen.',
        'success' => 'Virksomheden blev slettet.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
