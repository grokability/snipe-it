<?php

return [

    'does_not_exist' => '配件[:id] 不存在。',
    'not_found' => '找不到该配件。',
    'assoc_users' => '配件目前状态：可用数量不足，请检查改配件再重试。',

    'create' => [
        'error' => '配件添加失败，请重试。',
        'success' => '配件添加成功。',
    ],

    'update' => [
        'error' => '配件更新失败，请重试。',
        'success' => '配件更新成功。',
    ],

    'delete' => [
        'confirm' => '你确定要删除此配件？',
        'error' => '删除配件出错，请重试。',
        'success' => '删除配件成功',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '配件不能被预订，请重试。',
        'success' => '配件成功签出。',
        'unavailable' => '配件不可被签出。检查可用数量',
        'user_does_not_exist' => '无效用户，请重试。',
        'checkout_qty' => [
            'lte' => '目前只有一个可用的此类型的配件，您正在试图签出 :checkout_qty 个。 请调整签出数量或该配件的总库存，然后重试。|有 :num_currently_restotal 个可用配件，您正在尝试签出 :checkout_qty 个。 请调整借出数量或该配件的总库存，然后重试。',
        ],

    ],

    'checkin' => [
        'error' => '配件没有归还，请重试。',
        'success' => '配件归还成功。',
        'user_does_not_exist' => '无效用户，请重试。',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
