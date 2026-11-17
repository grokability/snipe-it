<?php

return [

    'does_not_exist' => '配件 [:id] 不存在',
    'not_found' => '沒有找到該配件',
    'assoc_users' => '使用者目前已借出 :count 組配件。請在繳回配件後重試。 ',

    'create' => [
        'error' => '新增配件失敗，請重試。',
        'success' => '新增配件成功。',
    ],

    'update' => [
        'error' => '更新配件失敗，請重試。',
        'success' => '更新配件成功。',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此配件嗎？',
        'error' => '刪除配件時發生問題。請再試一次。',
        'success' => '刪除配件成功。',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '配件借出失敗。請再試一次。',
        'success' => '借出配件成功。',
        'unavailable' => '配件不足無法借出, 檢查可用數量.',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
        'checkout_qty' => [
            'lte' => '目前此類型只有一個可用的配件，您正在試圖借出 :checkout_qty 個。 請修改借出數量或是該配件的庫存量後再試一次。|有 :num_currently_restotal 個可用配件，您正在試圖借出 :checkout_qty 個。請修改借出數量或是該配件的庫存量後再試一次。',
        ],

    ],

    'checkin' => [
        'error' => '配件繳回失敗。請再試一次。',
        'success' => '繳回配件成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '沒有欄位被更改，因此沒有更新任何內容。',
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
