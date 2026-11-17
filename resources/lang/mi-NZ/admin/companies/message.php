<?php

return [
    'does_not_exist' => 'Kāore te Kamupene i te tīariari.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Kāore i hangaia te Kamupene, tēnā whakamātau anō.',
        'success' => 'I angitu te Kamupene.',
    ],
    'update' => [
        'error' => 'Kāore te Kamupene i whakahoutia, tēnā whakamātau anō',
        'success' => 'Kua angitu te Kamupene.',
    ],
    'delete' => [
        'confirm' => 'Kei te hiahia koe ki te muku i tenei kamupene?',
        'error' => 'He raru kei te whakakore i te kamupene. Tena ngana ano.',
        'success' => 'I mukua te Kamupene.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
