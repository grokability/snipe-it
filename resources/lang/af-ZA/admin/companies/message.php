<?php

return [
    'does_not_exist' => 'Maatskappy bestaan ​​nie.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Maatskappy is nie geskep nie, probeer asseblief weer.',
        'success' => 'Maatskappy geskep suksesvol.',
    ],
    'update' => [
        'error' => 'Maatskappy is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Maatskappy suksesvol opgedateer.',
    ],
    'delete' => [
        'confirm' => 'Is jy seker jy wil hierdie maatskappy uitvee?',
        'error' => 'Daar was \'n probleem met die verwydering van die maatskappy. Probeer asseblief weer.',
        'success' => 'Die maatskappy is suksesvol verwyder.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
