<?php

return [

    'update' => [
        'error' => '更新過程中發生問題。',
        'success' => '更新設定成功。',
    ],
    'backup' => [
        'delete_confirm' => '您確定要刪除此備份檔嗎？此動作無法復原。',
        'file_deleted' => '刪除備份檔成功。',
        'generated' => '成功新增一個新的備份檔。',
        'file_not_found' => '在伺服器上找不到備份檔',
        'restore_warning' => '是的，還原它。我了解這將覆蓋資料庫中目前的任何現有數據。這也會登出所有目前使用者(包括您)。',
        'restore_confirm' => '請您確認是否要從 :filename 還原資料庫？',
    ],
    'restore' => [
        'success' => '您的系統備份已還原。請重新登入。',
    ],
    'purge' => [
        'error' => '清除過程中發生錯誤。',
        'validation_failed' => '你的清除確認不正確，請在文字輸入欄位輸入＂DELETE＂。',
        'success' => '已成功清除刪除記錄。',
    ],
    'mail' => [
        'sending' => '正在發送測試郵件...',
        'success' => '郵件已傳送!',
        'error' => '郵件無法發送',
        'additional' => '沒有提供額外的錯誤訊息。請檢查你的電子郵件設定和應用程式日誌。',
    ],
    'ldap' => [
        'testing' => '正在測試 LDAP 連線、繫結和查詢...',
        '500' => '500 伺服器錯誤。請檢查伺服器的日誌以取得更多資訊。',
        'error' => '發生了一些錯誤 :(',
        'sync_success' => '根據你的設定，從 LDAP 伺服器回傳的 10 個使用者樣本：',
        'testing_authentication' => 'LDAP 授權測試中...',
        'authentication_success' => '用戶成功透過 LDAP 驗證',
    ],
    'labels' => [
        'null_template' => '找不到標籤範本。請選擇一個範本。',
    ],
    'webhook' => [
        'sending' => '正在傳送 :app 測試訊息...',
        'success' => '您的 :webhook_name 整合正常運作！',
        'success_pt1' => '成功！請檢查 ',
        'success_pt2' => ' 頻道中的測試訊息，並確定在下面點選儲存以儲存你的設定。',
        '500' => '500 伺服器錯誤。',
        'error' => '發生了一些錯誤。:app 回應：:error_message',
        'error_redirect' => '錯誤：301/302 :endpoint 回傳重新導向。基於安全考量，我們不追蹤重新導向。請使用實際的端點。',
        'error_misc' => '發生了一些錯誤。 :( ',
        'webhook_fail' => ' webhook 通知失敗：請確認 URL 是否仍然有效。',
        'webhook_channel_not_found' => ' 找不到 webhook 頻道。',
        'ms_teams_deprecation' => '所選的 Microsoft Teams webhook URL 將於 2025 年 12 月 31 日停用。請改用工作流程 URL。Microsoft 建立工作流程的說明文件可在<a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">此處</a>找到。',
    ],
    'location_scoping' => [
        'not_saved' => '您的設定未儲存。',
        'mismatch' => '資料庫中有 1 個項目需要您處理，才能啟用位置範圍設定。|資料庫中有 :count 個項目需要您處理，才能啟用位置範圍設定。',
    ],
    'oauth' => [
        'token_revoked' => '個人存取 Token 已成功撤銷。',
        'token_unrevoked' => '個人存取 Token 已成功恢復。',
        'token_not_found' => '找不到該個人存取 Token。',
        'token_revoke_error' => '撤銷 Token 時發生錯誤。',
        'token_unrevoke_error' => '恢復 Token 時發生錯誤。',
        'client_created' => 'OAuth 用戶端已成功建立。',
        'client_updated' => 'OAuth 用戶端已成功更新。',
        'client_deleted' => 'OAuth 用戶端已成功刪除。',
        'client_revoked' => 'OAuth 用戶端已成功撤銷。',
        'client_unrevoked' => 'OAuth 用戶端已成功恢復。',
        'client_not_found' => '找不到該 OAuth 用戶端。',
        'token_deleted' => 'Token 已成功撤銷。',
        'client_delete_denied' => '您沒有刪除此用戶端的權限。',
        'client_edit_denied' => '您沒有編輯此用戶端的權限。',
        'token_delete_denied' => '您沒有撤銷此 Token 的權限。',
    ],
];
