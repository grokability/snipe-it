<?php

return [
    'does_not_exist' => '公司不存在',
    'deleted' => '已刪除公司',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => '新增公司失敗，請重試。',
        'success' => '新增公司成功。',
    ],
    'update' => [
        'error' => '更新公司失敗，請重試。',
        'success' => '更新公司成功。',
    ],
    'delete' => [
        'confirm' => '您確定要刪除此公司嗎？',
        'error' => '刪除公司時發生問題。請再試一次。',
        'success' => '刪除公司成功。',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
