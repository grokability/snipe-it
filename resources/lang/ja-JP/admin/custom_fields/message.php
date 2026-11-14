<?php

return [

    'field' => [
        'invalid' => 'フィールドがありません。',
        'already_added' => 'フィールドはすでに追加されています',
        'none_selected' => 'フィールドが選択されていません。',

        'create' => [
            'error' => 'フォルダーは作成されませんでした。もう一度やり直してください。',
            'success' => 'フィールドを正常に作成しました。',
            'assoc_success' => 'フィールドはフィールドセットに追加されました',
        ],

        'update' => [
            'error' => 'フィールドは更新されませんでした。もう一度やり直してください。',
            'success' => 'フィールドを正常に更新しました。',
        ],

        'delete' => [
            'confirm' => 'このフィールドを削除してもよろしいですか？',
            'error' => 'フィールドを削除する際に問題が発生しました。もう一度、やり直して下さい。',
            'success' => 'フィールドは削除されました。',
            'in_use' => 'フィールドは使用中です。',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'フィールドセットが見つかりません',

        'create' => [
            'error' => 'フィールドセットは作成されませんでした。もう一度やり直してください。',
            'success' => 'フィールドセットを正常に作成しました。',
        ],

        'update' => [
            'error' => 'フィールドセットは更新されませんでした。もう一度やり直してください。',
            'success' => 'フィールドセットを正常に更新しました。',
        ],

        'delete' => [
            'confirm' => 'このフィールドセットを削除してもよろしいですか？',
            'error' => 'フィールドセットを削除する際に問題が発生しました。もう一度、やり直して下さい。',
            'success' => 'フィールドセットは削除されました。',
            'in_use' => 'フィールドセットは使用中です。',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'フィールドセットを複製しました。',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'デフォルトのフィールドセット値の検証中にエラーが発生しました。',

    ],

];
