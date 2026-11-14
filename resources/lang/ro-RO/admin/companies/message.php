<?php

return [
    'does_not_exist' => 'Compania nu există.',
    'deleted' => 'Firmă ștearsă',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Compania nu a fost creată, încercați din nou.',
        'success' => 'Compania a creat cu succes.',
    ],
    'update' => [
        'error' => 'Compania nu a fost actualizată, încercați din nou',
        'success' => 'Compania a fost actualizată cu succes.',
    ],
    'delete' => [
        'confirm' => 'Sigur doriți să ștergeți această companie?',
        'error' => 'A apărut o problemă cu ștergerea companiei. Vă rugăm să încercați din nou.',
        'success' => 'Compania a fost șters cu succes.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
