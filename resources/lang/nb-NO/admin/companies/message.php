<?php

return [
    'does_not_exist' => 'Bedriften eksisterer ikke.',
    'deleted' => 'Slett firma',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Bedriften ble ikke opprette. Prøv igjen.',
        'success' => 'Bedriften ble opprettet.',
    ],
    'update' => [
        'error' => 'Bedriften ble ikke oppdatert. Prøv igjen',
        'success' => 'Bedriften ble oppdatert.',
    ],
    'delete' => [
        'confirm' => 'Er du sikker på at du vil slette denne bedriften?',
        'error' => 'Feil ved sletting av bedrift. Prøv igjen.',
        'success' => 'Bedriften ble slettet.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
