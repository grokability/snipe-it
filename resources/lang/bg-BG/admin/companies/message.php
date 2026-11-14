<?php

return [
    'does_not_exist' => 'Компанията не съществува.',
    'deleted' => 'Изтрита фирма',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Компанията не беше създадена, моля опитайте отново.',
        'success' => 'Компанията създадена успешно.',
    ],
    'update' => [
        'error' => 'Компанията не беше обновена, моля опитайте отново.',
        'success' => 'Компанията обновена успешно.',
    ],
    'delete' => [
        'confirm' => 'Сигурни ли сте, че желаете да изтриете тази компания?',
        'error' => 'Възникна проблем при изтриването на компанията. Моля опитайте отново.',
        'success' => 'Компанията бе изтрита успешно.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
