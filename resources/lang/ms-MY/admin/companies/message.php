<?php

return [
    'does_not_exist' => 'Syarikat tidak wujud.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Syarikat tidak dicipta, sila cuba lagi.',
        'success' => 'Syarikat berjaya berjaya.',
    ],
    'update' => [
        'error' => 'Syarikat tidak dikemas kini, sila cuba lagi',
        'success' => 'Syarikat dikemas kini dengan jayanya.',
    ],
    'delete' => [
        'confirm' => 'Adakah anda pasti ingin memadamkan syarikat ini?',
        'error' => 'Ada masalah memusnahkan syarikat. Sila cuba lagi.',
        'success' => 'Syarikat telah berjaya dipadam.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
