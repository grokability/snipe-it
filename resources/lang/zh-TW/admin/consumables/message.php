<?php

return [

    'invalid_category_type' => '此類別必須是耗材類別。',
    'does_not_exist' => '耗材不存在。',

    'create' => [
        'error' => '新增耗材失敗，請重試。',
        'success' => '新增耗材成功。',
    ],

    'update' => [
        'error' => '更新耗材失敗，請重試。',
        'success' => '更新耗材成功。',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此耗材嗎？',
        'error' => '刪除耗材時發生問題。請再試一次。',
        'success' => '刪除耗材成功。',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '耗材領取失敗，請重試。',
        'success' => '耗材領取成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
        'unavailable' => '借出的耗材不足。請檢查剩餘數量。',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => '耗材登記失敗，請重試。',
        'success' => '耗材登記成功',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '沒有欄位被更改，因此沒有更新任何內容。',
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
