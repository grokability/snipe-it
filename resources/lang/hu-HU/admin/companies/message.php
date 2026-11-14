<?php

return [
    'does_not_exist' => 'A vállalat nem létezik.',
    'deleted' => 'Törölt cég',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'A vállalat nem jött létre, próbálkozz újra.',
        'success' => 'Vállalat sikeresen létrehozva.',
    ],
    'update' => [
        'error' => 'A vállalat nem frissült, próbálkozz újra',
        'success' => 'Vállalat sikereresen frissítve.',
    ],
    'delete' => [
        'confirm' => 'Biztosan törölni szeretnéd a vállalatot?',
        'error' => 'Probléma támadt a vállalat törlésével. Próbálkozz újra.',
        'success' => 'A vállalat sikeresen törölve lett.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
