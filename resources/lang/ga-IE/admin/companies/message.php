<?php

return [
    'does_not_exist' => 'Níl an chuideachta ann.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Níor cruthaíodh an chuideachta, déan iarracht arís.',
        'success' => 'Cruthaíodh an chuideachta go rathúil.',
    ],
    'update' => [
        'error' => 'Níor nuashonraíodh an chuideachta, déan iarracht arís',
        'success' => 'Cuideachta nuashonraithe go rathúil.',
    ],
    'delete' => [
        'confirm' => 'An bhfuil tú cinnte gur mian leat an chuideachta seo a scriosadh?',
        'error' => 'Bhí ceist ann a scriosadh an chuideachta. Arís, le d\'thoil.',
        'success' => 'Scriosadh an Chuideachta go rathúil.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
