<?php

return [

    'field' => [
        'invalid' => '字段不存在',
        'already_added' => '字段已存在',
        'none_selected' => '未选择字段',

        'create' => [
            'error' => '新增字段失败，请重试',
            'success' => '新增字段成功。',
            'assoc_success' => '增加字段到字段集成功',
        ],

        'update' => [
            'error' => '更新字段失败，请重试。',
            'success' => '更新字段成功',
        ],

        'delete' => [
            'confirm' => '你确定要删除这个字段？',
            'error' => '删除字段失败，请重试。',
            'success' => '删除字段成功',
            'in_use' => '字段还在使用中',
            'in_use_named' => ':name 仍附加到至少一个字段集，无法删除。',
            'bulk_success' => '字段删除成功。|:count 个字段已成功删除。',
            'partial_success' => ':count 个字段已成功删除，但其他字段无法删除。详情见下方。|:count 个字段已成功删除，但其他字段无法删除。详情见下方。',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => '现场集不存在',

        'create' => [
            'error' => '新增字段集失败，请重试',
            'success' => '新增字段集成功',
        ],

        'update' => [
            'error' => '更新字段集失败，请重试。',
            'success' => '更新字段集成功',
        ],

        'delete' => [
            'confirm' => '确定要删除这个字段集吗？',
            'error' => '删除字段集失败，请重试。',
            'success' => '删除字段集成功',
            'in_use' => '字段集还在使用中',
            'in_use_named' => ':name 仍附加到至少一个资产型号，无法删除。',
            'bulk_success' => '字段集删除成功。|:count 个字段集已成功删除。',
            'partial_success' => ':count 个字段集已成功删除，但其他字段集无法删除。详情见下方。|:count 个字段集已成功删除，但其他字段集无法删除。详情见下方。',
        ],

        'clone' => [
            'success' => '成功克隆字段集。',
        ],

    ],

    'fieldset_default_value' => [

        'error' => '验证默认字段集时出错。',

    ],

];
