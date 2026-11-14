<?php

return [
    'does_not_exist' => 'Компани байхгүй байна.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Компани бүтээгдээгүй, дахин оролдоно уу.',
        'success' => 'Компани амжилттай байгуулагдсан.',
    ],
    'update' => [
        'error' => 'Компани шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Компани амжилттай шинэчлэгдсэн.',
    ],
    'delete' => [
        'confirm' => 'Та энэ компанийг устгахыг хүсч байгаадаа итгэлтэй байна уу?',
        'error' => 'Компаниыг устгахад асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Компани амжилттай устгасан.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
