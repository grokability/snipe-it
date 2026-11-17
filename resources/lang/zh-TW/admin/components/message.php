<?php

return [

    'does_not_exist' => '組件不存在',

    'create' => [
        'error' => '新增組件失敗，請重試。',
        'success' => '新增組件成功。',
    ],

    'update' => [
        'error' => '更新組件失敗，請重試。',
        'success' => '更新組件成功。',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此組件嗎？',
        'error' => '刪除組件時發生問題。請再試一次。',
        'success' => '刪除組件成功。',
        'error_qty' => '此類型的部分組件仍在借出中。請先繳回後再試一次。',
    ],

    'checkout' => [
        'error' => '組件借出失敗。請再試一次。',
        'success' => '借出組件成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
        'unavailable' => '組件剩餘不足：剩餘 :remaining ，申請 :requested',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '沒有欄位被更改，因此沒有更新任何內容。',
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
        'error' => '組件繳回失敗。請再試一次。',
        'success' => '繳回組件成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
    ],

];
