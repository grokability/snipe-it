<?php

return [

    'invalid_category_type' => '该类别必须是一个耗材类别。',
    'does_not_exist' => '耗材不存在。',

    'create' => [
        'error' => '耗材未被创建，请重试。',
        'success' => '耗材创建成功。',
    ],

    'update' => [
        'error' => '耗材未被更新，请重试。',
        'success' => '耗材更新成功。',
    ],

    'delete' => [
        'confirm' => '您确定希望删除此耗材？',
        'error' => '删除耗材失败，请重试',
        'success' => '成功删除此耗材。',
        'bulk_success' => '消耗品删除成功。|:count 个消耗品已成功删除。',
        'partial_success' => ':count 个消耗品已成功删除，但其他消耗品无法删除。详情见下方。|:count 个消耗品已成功删除，但其他消耗品无法删除。详情见下方。',
    ],

    'checkout' => [
        'error' => '耗材领取失败，请重试',
        'success' => '耗材领取成功',
        'user_does_not_exist' => '无效用户，请重试。',
        'unavailable' => '没有足够的耗材可被签出。请检查剩余数量。 ',
    ],

    'consumed' => [
        'success' => '发放记录已标记为已消耗。',
        'bulk_success' => ':count 条发放记录已标记为已消耗。|:count 条发放记录已标记为已消耗。',
        'already' => '该发放记录已标记为已消耗。',
        'error' => '无法将发放记录标记为已消耗，请重试。',
        'no_rows_selected' => '未选择任何有效的发放记录。',
    ],

    'checkin' => [
        'error' => '耗材归还失败，请重试',
        'success' => '耗材归还成功。',
        'user_does_not_exist' => '无效用户，请重试。',
    ],

    'restore' => [
        'error' => '消耗品未恢复，请重试',
        'success' => '消耗品已成功恢复。',
    ],

    'bulk' => [
        'no_consumables_selected' => '未选择任何消耗品。',
        'no_action_selected' => '请从下拉菜单中选择一个操作。',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => '由于您没有更新权限，已跳过 :count 个消耗品。',

        'update' => [
            'success' => ':count 个消耗品已更新。|:count 个消耗品已更新。',
            'none_updated' => '选定的消耗品无法更新。详情见下方错误。|所选消耗品均无法更新。详情见下方错误。',
        ],

        'restore' => [
            'success' => ':count 个消耗品已恢复。|:count 个消耗品已恢复。',
        ],
    ],
];
