<?php

return [

    'does_not_exist' => '構成部品は存在しません',

    'create' => [
        'error' => '構成部品が作成できませんでした。もう一度やり直して下さい。',
        'success' => '構成部品が作成されました。',
    ],

    'update' => [
        'error' => '構成部品がが更新できませんでした。もう一度やり直して下さい。',
        'success' => '構成部品が作成されました。',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'この構成部品を削除してもよろしいですか?',
        'error' => '構成部品を削除する際に問題が発生しました。もう一度やり直して下さい。',
        'success' => '構成部品は削除されました。',
        'error_qty' => 'このタイプの一部のコンポーネントはまだチェックアウトされています。チェックインしてもう一度お試しください。',
    ],

    'checkout' => [
        'error' => '構成部品はチェックアウトされませんでした。もう一度、やり直して下さい。',
        'success' => '構成部品は正常にチェックアウトされました。',
        'user_does_not_exist' => '利用者が正しくありません。もう一度試して下さい。',
        'unavailable' => '残りのコンポーネントが不足しています: :remaining, :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'フィールドが選択されていないため、更新されませんでした。',
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
        'error' => '構成部品はチェックインされませんでした。もう一度、やり直して下さい。',
        'success' => '構成部品は正常にチェックインされました。',
        'user_does_not_exist' => '利用者が正しくありません。もう一度試して下さい。',
    ],

];
