<?php

return [
    'does_not_exist' => 'Inkampani ayikho.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Inkampani ayidalwanga, sicela uzame futhi.',
        'success' => 'Inkampani idalwe ngempumelelo.',
    ],
    'update' => [
        'error' => 'Inkampani ayizange ibuyekezwe, sicela uzame futhi',
        'success' => 'Inkampani ibuyekezwe ngempumelelo.',
    ],
    'delete' => [
        'confirm' => 'Uqinisekile ukuthi ufisa ukususa le nkampani?',
        'error' => 'Kube nenkinga yokususa inkampani. Ngicela uzame futhi.',
        'success' => 'INkampani isuliwe ngempumelelo.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
