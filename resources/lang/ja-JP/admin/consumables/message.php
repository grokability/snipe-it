<?php

return [

    'invalid_category_type' => '消耗品カテゴリを選択してください。',
    'does_not_exist' => '消耗品は存在しません。',

    'create' => [
        'error' => '消耗品は作成できませんでした。もう一度試して下さい。',
        'success' => '消耗品の作成に成功しました。',
    ],

    'update' => [
        'error' => '消耗品の更新に失敗しました。もう一度試して下さい。',
        'success' => '消耗品の更新に成功しました。',
    ],

    'delete' => [
        'confirm' => 'この消耗品を削除しますか？',
        'error' => '消耗品の削除に失敗しました。もう一度試して下さい。',
        'success' => '消耗品は削除されました。',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '消耗品はチェックアウトできませんでした。もう一度試して下さい。',
        'success' => '消耗品はチェックアウトに成功しました。',
        'user_does_not_exist' => '利用者が正しくありません。もう一度試して下さい。',
        'unavailable' => 'このチェックアウトに十分な消耗品がありません。残りの数量を確認してください。 ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => '消耗品はチェックインされませんでした。もう一度試して下さい。',
        'success' => '消耗品はチェックインされました。',
        'user_does_not_exist' => '利用者が正しくありません。もう一度試して下さい。',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'フィールドが選択されていないため、更新されませんでした。',
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
