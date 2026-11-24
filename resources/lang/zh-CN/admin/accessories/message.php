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
        'bulk_success' => '配件删除成功。|:count 个配件已成功删除。',
        'partial_success' => ':count 个配件已成功删除，但其他配件无法删除。详情见下方。|:count 个配件已成功删除，但其他配件无法删除。详情见下方。',
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
        'error' => '配件未恢复，请重试',
        'success' => '配件已成功恢复。',
    ],

    'bulk' => [
        'no_accessories_selected' => '未选择任何配件。',
        'no_action_selected' => '请从下拉菜单中选择一个操作。',
        'no_fields_updated' => '没有字段被更改，因此没有更新任何内容。',
        'skipped_unauthorized' => '由于您没有更新权限，已跳过 :count 个配件。',

        'update' => [
            'success' => ':count 个配件已更新。|:count 个配件已更新。',
            'none_updated' => '选定的配件无法更新。详情见下方错误。|所选配件均无法更新。详情见下方错误。',
        ],

        'restore' => [
            'success' => ':count 个配件已恢复。|:count 个配件已恢复。',
        ],

        'checkin_selected' => [
            'no_rows_selected' => '未选择任何已签出的配件。',
            'success' => ':count 条配件签出记录已签入。|:count 条配件签出记录已签入。',
        ],

        'checkout' => [
            'nothing_selectable' => 'None of the selected accessories can be checked out. Archived accessories and accessories with no remaining stock are excluded.',
            'excluded_notice' => 'The following accessories were excluded from this checkout because they are archived or have no remaining stock:',
            'skipped_archived' => ':name was skipped because it is archived.',
            'skipped_unavailable' => ':name was skipped because no units are currently available.',
            'skipped_company_mismatch' => ':name was skipped because it belongs to a different company (:item_company) than the selected target.',
            'summary' => ':checked_out of :total accessories were checked out.',
        ],
    ],
];
