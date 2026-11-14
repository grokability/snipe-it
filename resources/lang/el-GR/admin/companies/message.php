<?php

return [
    'does_not_exist' => 'Αυτή η εταιρεία δεν υπάρχει.',
    'deleted' => 'Διαγραμμένη εταιρεία',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Η εταιρεία δεν δημιουργήθηκε, παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Η εταιρεία δημιουργήθηκε με επιτυχία.',
    ],
    'update' => [
        'error' => 'Η εταιρεία δεν ενημερώθηκε, δοκιμάστε ξανά',
        'success' => 'Η εταιρεία ενημερώθηκε με επιτυχία.',
    ],
    'delete' => [
        'confirm' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτήν την εταιρεία;',
        'error' => 'Υπήρξε ένα ζήτημα διαγράφοντας την εταιρεία. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Η εταιρεία έχει διαγραφεί με επιτυχία.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
