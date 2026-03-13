<?php

return [

    'update' => [
        'error'                 => '更新過程中發生問題。',
        'success'               => '更新設定成功。',
    ],
    'backup' => [
        'delete_confirm'        => '您確定要刪除此備份檔嗎？此動作無法復原。',
        'file_deleted'          => '刪除備份檔成功。',
        'generated'             => '成功新增一個新的備份檔。',
        'file_not_found'        => '在伺服器上找不到備份檔',
        'restore_warning'       => '是的，還原它。我了解這將覆蓋資料庫中目前的任何現有數據。這也會登出所有目前使用者(包括您)。',
        'restore_confirm'       => '請您確認是否要從 :filename 還原資料庫？'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => '清除過程中發生錯誤。',
        'validation_failed'     => '你的清除確認不正確，請在文字輸入欄位輸入＂DELETE＂。',
        'success'               => '已成功清除刪除記錄。',
    ],
    'mail' => [
        'sending' => '正在發送測試郵件...',
        'success' => '郵件已傳送!',
        'error' => '郵件無法發送',
        'additional' => '沒有提供額外的錯誤訊息。請檢查你的電子郵件設定和應用程式日誌。'
    ],
    'ldap' => [
        'testing' => '正在測試 LDAP 連線、繫結和查詢...',
        '500' => '500 伺服器錯誤。請檢查伺服器的日誌以取得更多資訊。',
        'error' => '發生了一些錯誤 :(',
        'sync_success' => '根據你的設定，從 LDAP 伺服器回傳的 10 個使用者樣本：',
        'testing_authentication' => 'LDAP 授權測試中...',
        'authentication_success' => '用戶成功透過 LDAP 驗證'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => '正在傳送 :app 測試訊息...',
        'success' => 'Your :webhook_name Integration works!',
        'success_pt1' => '成功！請檢查 ',
        'success_pt2' => ' 頻道中的測試訊息，並確定在下面點選儲存以儲存你的設定。',
        '500' => '500 伺服器錯誤。',
        'error' => '發生了一些錯誤。:app 回應：:error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => '發生了一些錯誤。 :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
