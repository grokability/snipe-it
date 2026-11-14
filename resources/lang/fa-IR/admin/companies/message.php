<?php

return [
    'does_not_exist' => 'شرکت وجود ندارد.',
    'deleted' => 'شرکت حذف شده',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'شرکت ایجاد نشد،دوباره سعی کنید.',
        'success' => 'شرکت ایجاد شد.',
    ],
    'update' => [
        'error' => 'شرکت ایجاد نشد،دوباره سعی کنید',
        'success' => 'شرکت آپدیت شد.',
    ],
    'delete' => [
        'confirm' => 'آیا مطمعن هستید میخواهید این شرکت را پاک کنید؟',
        'error' => 'موضوع شرکت حذف شد. لطفا دوباره سعی کنید.',
        'success' => 'این شرکت پاک شد.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
