<?php

return [

    'field' => [
        'invalid' => '欄位不存在。',
        'already_added' => '已新增欄位。',
        'none_selected' => '未選取欄位',

        'create' => [
            'error' => '新增欄位失敗，請重試。',
            'success' => '新增欄位成功。',
            'assoc_success' => '新增欄位到欄位集成功',
        ],

        'update' => [
            'error' => '更新欄位失敗，請重試。',
            'success' => '更新欄位成功。',
        ],

        'delete' => [
            'confirm' => '您確定要刪除此欄位？',
            'error' => '刪除欄位時發生問題。請再試一次。',
            'success' => '刪除欄位成功。',
            'in_use' => '欄位還在使用中',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => '欄位集不存在',

        'create' => [
            'error' => '新增欄位集失敗，請重試。',
            'success' => '新增欄位集成功。',
        ],

        'update' => [
            'error' => '更新欄位集失敗，請重試。',
            'success' => '更新欄位集成功。',
        ],

        'delete' => [
            'confirm' => '您確定要刪除此欄位集？',
            'error' => '刪除欄位集時發生問題。請再試一次。',
            'success' => '刪除欄位集成功。',
            'in_use' => '欄位集還在使用中',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => '驗證預設字段集數值錯誤',

    ],

];
