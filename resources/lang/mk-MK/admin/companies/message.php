<?php

return [
    'does_not_exist' => 'Компанијата не постои.',
    'deleted' => 'Избришана компанија',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Компанијата не е креирана, обидете се повторно.',
        'success' => 'Компанијата е успешно креирана.',
    ],
    'update' => [
        'error' => 'Компанијата не е креирана, обидете се повторно',
        'success' => 'Компанијата е ажурирана.',
    ],
    'delete' => [
        'confirm' => 'Дали сте сигурни дека сакате да ја избришете оваа компанија?',
        'error' => 'Имаше проблем со бришење на компанијата. Обидете се повторно.',
        'success' => 'Компанијата беше успешно избришана.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
