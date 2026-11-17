<?php

return [
    'does_not_exist' => 'החברה לא קיימת.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'החברה לא נוצרה, נסה שוב.',
        'success' => 'החברה יצרה בהצלחה.',
    ],
    'update' => [
        'error' => 'החברה לא עודכנה, נסה שוב',
        'success' => 'החברה עודכנה בהצלחה.',
    ],
    'delete' => [
        'confirm' => 'האם אתה בטוח שברצונך למחוק חברה זו?',
        'error' => 'היתה בעיה במחיקת החברה. בבקשה נסה שוב.',
        'success' => 'החברה נמחקה בהצלחה.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
