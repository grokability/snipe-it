<?php

return [
    'does_not_exist' => 'Podjetje ne obstaja.',
    'deleted' => 'Izbrisano podjetje',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Podjetje ni bilo ustvarjeno, poskusite znova.',
        'success' => 'Podjetje je uspešno ustvarjeno.',
    ],
    'update' => [
        'error' => 'Podjetje ni bilo posodobljeno, poskusite znova',
        'success' => 'Podjetje uspešno posodobljeno.',
    ],
    'delete' => [
        'confirm' => 'Ste prepričani, da želite izbrisati to podjetje?',
        'error' => 'Prišlo je do težave pri brisanju podjetja. Prosim poskusite ponovno.',
        'success' => 'Podjetje je bilo uspešno izbrisano.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
