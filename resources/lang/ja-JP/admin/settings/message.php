<?php

return [

    'update' => [
        'error'                 => '更新時にエラーが発生しました。 ',
        'success'               => '更新に成功しました。',
    ],
    'backup' => [
        'delete_confirm'        => 'このバックアップファイルを削除してもよろしいですか？この操作は、もとに戻すことは出来ません。 ',
        'file_deleted'          => 'バックアップファイルの削除に成功しました。 ',
        'generated'             => '新しいバックアップファイルが作成されました。',
        'file_not_found'        => 'そのバックアップファイルをサーバー上に見つけることが出来ませんでした。',
        'restore_warning'       => '復元を行います。現在データベースにある既存のデータを上書きします。 これにより、既存のすべてのユーザー(あなたを含む) もログアウトします。',
        'restore_confirm'       => ':filename からデータベースを復元してもよろしいですか？'
    ],
    'restore' => [
        'success'               => 'システムバックアップが復元されました。もう一度ログインしてください。'
    ],
    'purge' => [
        'error'     => 'パージ中にエラーが発生しました。 ',
        'validation_failed'     => 'パージの確定方法が正しくありません。入力してください、単語「削除」確認ボックス。',
        'success'               => 'パージによりレコードは削除されました',
    ],
    'mail' => [
        'sending' => 'テストメールを送信しています...',
        'success' => 'メール送信完了',
        'error' => 'メールが送信できません',
        'additional' => '追加のエラーメッセージはありません。メール設定とアプリのログを確認してください。'
    ],
    'ldap' => [
        'testing' => 'LDAP接続のテスト中…バインディングとクエリを行っています…',
        '500' => '500 Server Error. 詳しくは、サーバーのログをご確認ください。',
        'error' => '問題が発生しました。',
        'sync_success' => '設定に基づいてLDAPサーバーから返された10人のユーザーのサンプル:',
        'testing_authentication' => 'LDAP認証のテスト中...',
        'authentication_success' => 'LDAPによるユーザー認証に成功しました！'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => ':app テストメッセージを送信しています...',
        'success' => 'あなたの:webhook_name連携は動作します！',
        'success_pt1' => 'チェックに成功 ',
        'success_pt2' => ' テストメッセージのチャンネルで、設定を保存するには以下の「SAVE」をクリックしてください。',
        '500' => '500 Server Error.',
        'error' => '問題が発生しました。:app 応答: :error_message',
        'error_redirect' => 'エラー: 301/302 :endpoint はリダイレクトを返します。セキュリティ上の理由から、リダイレクトには従いません。実際のエンドポイントを使用してください。',
        'error_misc' => '問題が発生しました。:( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => '設定は保存されませんでした。',
        'mismatch' => '位置情報を有効にする前に対応が必要な項目が、データベースに 1 件あります。|位置情報を有効にする前に対応が必要な項目が、データベースに :count 件あります。',
    ],
];
