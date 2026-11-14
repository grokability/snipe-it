<?php

return [
    'does_not_exist' => 'Ang Kumpanya ay hindi umiiral.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Ang kumpanya ay hindi naisagawa, magyaring subukang muli.',
        'success' => 'Ang kumpanya ay matagumpay na naisagawa.',
    ],
    'update' => [
        'error' => 'Ang kumpanya ay hindi nai-update, magyaring subukang muli',
        'success' => 'Ang kumpanya ay matagumpay na nai-update.',
    ],
    'delete' => [
        'confirm' => 'Sigurado kaba na i-delete mo ang kumpanyang ito?',
        'error' => 'Mayroong isyu sa pag-delete ng kumpanya. Mangayaring subukang muli.',
        'success' => 'Ang kompayang ito ay matagumpay na nailikha.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
