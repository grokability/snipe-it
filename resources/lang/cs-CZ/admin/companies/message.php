<?php

return [
    'does_not_exist' => 'Společnost nebyla založena.',
    'deleted' => 'Odstraněné společnosti',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Společnost nebyla založena, zkuste to znovu.',
        'success' => 'Společnost byla v pořádku založena.',
    ],
    'update' => [
        'error' => 'Společnost se nepodařilo upravit, zkuste to prosím znovu',
        'success' => 'Společnost byla v pořádku upravena.',
    ],
    'delete' => [
        'confirm' => 'Opravdu si přejete odstranit tuto společnost?',
        'error' => 'Nepodařilo se nám zvolenou společnost odstranit. Zkuste to prosím znovu.',
        'success' => 'Společnost byla v pořádku odstraněna.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
