<?php

return [
    'does_not_exist' => '公司不存在。',
    'deleted' => '已删除公司',
    'assoc_users' => '该公司当前至少与 Snipe-IT 中的另一个项目（位置、用户、许可证、资产等）相关联，无法删除。请更新您的资产型号，使其不再引用此公司，然后重试。 ',
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
        'bulk_success' => '公司删除成功。|:count 个公司已成功删除。',
        'partial_success' => '公司删除成功。请参阅下方附加信息。| :count 个公司已成功删除。请参阅下方附加信息。',
    ],
    'webhook_fields_require_superuser' => 'Webhook 配置字段（:fields）只能由超级用户修改。',
];
