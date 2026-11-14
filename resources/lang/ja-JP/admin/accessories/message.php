<?php

return [

    'does_not_exist' => '付属品[:id]は存在しません。',
    'not_found' => 'その付属品は見つかりませんでした。',
    'assoc_users' => 'この付属品は、ユーザーに :count 個貸し出されています。もう一度、付属品を返却して下さい。 ',

    'create' => [
        'error' => '付属品は作成されていません。もう一度やり直してください。',
        'success' => '付属品は正常に作成されました。',
    ],

    'update' => [
        'error' => '付属品は更新されませんでした。もう一度、やり直して下さい。',
        'success' => '付属品は正常に更新されました。',
    ],

    'delete' => [
        'confirm' => 'この付属品を本当に削除してもよろしいですか？',
        'error' => 'この付属品を削除する際に問題が発生しました。もう一度、やり直して下さい。',
        'success' => 'この付属品は正常に削除されました。',
        'bulk_success' => 'アクセサリは正常に削除されました。|:count アクセサリは正常に削除されました。',
        'partial_success' => ':count個のアクセサリーは正常に削除されましたが、それ以外のものは削除できませんでした。詳細は以下をご覧ください。|:count個のアクセサリーは正常に削除されましたが、それ以外のものは削除できませんでした。詳細は以下をご覧ください。',
    ],

    'checkout' => [
        'error' => '付属品がチェックされませんでした。もう一度、やり直して下さい。',
        'success' => '付属品のチェックが終了しました。',
        'unavailable' => '付属品はチェックアウト中のため利用できません。',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
        'checkout_qty' => [
            'lte' => '現在、このタイプのアクセサリーは1つしかありません。:checkout_qty をチェックアウトしようとしています。 このアクセサリの数量または総在庫数を調整して再度お試しください。 :number_currently_remaining の利用可能なアクセサリーがあり、:checkout_qty をチェックアウトしようとしています。 このアクセサリの数量または総在庫数を調整して再度お試しください。',
        ],

    ],

    'checkin' => [
        'error' => '付属品がチェックされませんでした。もう一度、やり直して下さい。',
        'success' => '付属品のチェックが終了しました。',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'フィールドが選択されていないため、更新されませんでした。',
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
