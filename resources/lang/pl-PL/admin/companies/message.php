<?php

return [
    'does_not_exist' => 'Wskazana firma nie istnieje.',
    'deleted' => 'Usunięta firma',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Firma nie została utworzona, spróbuj ponownie.',
        'success' => 'Firma utworzona pomyślnie.',
    ],
    'update' => [
        'error' => 'Firma nie została uaktualniona, spróbuj ponownie',
        'success' => 'Firma została uaktualniona pomyślnie.',
    ],
    'delete' => [
        'confirm' => 'Czy na pewno chcesz usunąć tę firmę?',
        'error' => 'Wystąpił problem podczas usuwania firmy. Spróbuj ponownie.',
        'success' => 'Firma została usunięta pomyślnie.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
