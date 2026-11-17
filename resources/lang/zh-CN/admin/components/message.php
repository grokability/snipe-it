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
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
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
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => '归还组件失败，请重试。',
        'success' => '归还组件成功。',
        'user_does_not_exist' => '无效用户，请重试。',
    ],

];
