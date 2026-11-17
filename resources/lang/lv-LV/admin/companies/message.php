<?php

return [
    'does_not_exist' => 'Kompānija neeksistē.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Uzņēmums netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Uzņēmums veiksmīgi izveidots.',
    ],
    'update' => [
        'error' => 'Uzņēmums netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Uzņēmums ir veiksmīgi atjaunināts.',
    ],
    'delete' => [
        'confirm' => 'Vai tiešām vēlaties izdzēst šo uzņēmumu?',
        'error' => 'Radās problēma, izlaižot uzņēmumu. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Uzņēmums tika veiksmīgi dzēsts.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
