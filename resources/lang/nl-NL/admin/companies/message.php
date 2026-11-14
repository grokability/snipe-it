<?php

return [
    'does_not_exist' => 'Bedrijf bestaat niet.',
    'deleted' => 'Bedrijf verwijderd',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Bedrijf is niet aangemaakt. Probeer het opnieuw.',
        'success' => 'Bedrijf is aangemaakt.',
    ],
    'update' => [
        'error' => 'Bedrijf is niet gewijzigd. Probeer het opnieuw.',
        'success' => 'Bedrijf is gewijzigd.',
    ],
    'delete' => [
        'confirm' => 'Weet je het zeker dat je dit bedrijf wilt verwijderen?',
        'error' => 'Er is een probleem opgetreden bij het verwijderen van het bedrijf. Probeer het opnieuw.',
        'success' => 'Bedrijf is verwijderd.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
