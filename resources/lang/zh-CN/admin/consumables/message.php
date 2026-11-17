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
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '耗材领取失败，请重试',
        'success' => '耗材领取成功',
        'user_does_not_exist' => '无效用户，请重试。',
        'unavailable' => '没有足够的耗材可被签出。请检查剩余数量。 ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => '耗材归还失败，请重试',
        'success' => '耗材归还成功。',
        'user_does_not_exist' => '无效用户，请重试。',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
