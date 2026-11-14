<?php

return [
    'does_not_exist' => 'Företaget finns inte.',
    'deleted' => 'Raderat företag',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Företaget kunde inte skapas. Vänligen försök igen.',
        'success' => 'Företag skapat.',
    ],
    'update' => [
        'error' => 'Företaget kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Företag uppdaterat.',
    ],
    'delete' => [
        'confirm' => 'Är du säker på att du vill ta bort det här företaget?',
        'error' => 'Ett fel uppstod när företaget skulle tas bort. Vänligen försök igen.',
        'success' => 'Företag raderat.',
        'bulk_success' => 'Företaget har raderats.|:count företag har raderats.',
        'partial_success' => 'Företaget har raderats. Se ytterligare information nedan. |:count företag har raderats. Se ytterligare information nedan.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
