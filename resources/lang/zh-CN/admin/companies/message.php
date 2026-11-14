<?php

return [
    'does_not_exist' => '公司不存在。',
    'deleted' => '已删除公司',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => '新增公司失败，请重试。',
        'success' => '新增公司成功。',
    ],
    'update' => [
        'error' => '更新公司失败，请重试。',
        'success' => '更新公司成功。',
    ],
    'delete' => [
        'confirm' => '确定要删除这个公司吗？',
        'error' => '删除公司出错，请重试。',
        'success' => '删除公司成功。',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
