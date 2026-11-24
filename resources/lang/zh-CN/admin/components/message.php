<?php

return [

    'does_not_exist' => '组件不存在。',

    'create' => [
        'error' => '新增组件失败，请重试。',
        'success' => '新增组件成功。',
    ],

    'update' => [
        'error' => '更新组件失败，请重试。',
        'success' => '更新成功。',
    ],

    'restore' => [
        'error' => '组件未恢复，请重试',
        'success' => '组件已成功恢复。',
    ],

    'delete' => [
        'confirm' => '你确定要删除这个组件吗？',
        'error' => '删除组件出错，请重试。',
        'success' => '删除组件成功。',
        'error_qty' => '此类型的某些组件仍然被签出。请归还它们，然后重试。',
    ],

    'checkout' => [
        'error' => '签出组件失败，请重试。',
        'success' => '签出组件成功。',
        'user_does_not_exist' => '无效用户，请重试。',
        'unavailable' => '还没有足够的组件: :remaining 件剩余, :requested 件需求 ',
    ],

    'bulk' => [
        'no_components_selected' => '未选择任何组件。',
        'no_action_selected' => '请从下拉菜单中选择一个操作。',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => '由于您没有更新权限，已跳过 :count 个组件。',

        'update' => [
            'success' => ':count 个组件已更新。|:count 个组件已更新。',
            'none_updated' => '选定的组件无法更新。详情见下方错误。|所选组件均无法更新。详情见下方错误。',
        ],

        'delete' => [
            'success' => '已删除 :count 个组件。|已删除 :count 个组件。',
            'partial_success' => '已删除 :count 个组件。其余项的错误见下方。|已删除 :count 个组件。其余项的错误见下方。',
        ],

        'restore' => [
            'success' => '已恢复 :count 个组件。|已恢复 :count 个组件。',
        ],

        'checkin_selected' => [
            'no_rows_selected' => '未选择任何已签出的组件。',
            'success' => '在 :count 条签出记录中共签入 :unit_count 个单位。|在 :count 条签出记录中共签入 :unit_count 个单位。',
        ],
    ],

    'checkin' => [
        'error' => '归还组件失败，请重试。',
        'success' => '归还组件成功。',
        'user_does_not_exist' => '无效用户，请重试。',
    ],

];
