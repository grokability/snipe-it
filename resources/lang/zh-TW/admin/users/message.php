<?php

return [

    'accepted' => '您已成功接受此項目。',
    'declined' => '您已成功拒絕此項目。',
    'bulk_manager_warn' => '您的使用者已成功更新，但主管條目未保存，因為您選擇的主管也在要編輯的使用者列表中，使用者不能是自己的主管。 請再次選擇您的使用者並排除主管。',
    'user_exists' => '使用者已存在！',
    'cannot_delete' => '使用者不存在或您沒有刪除的權限。',
    'user_not_found' => '使用者不存在或您沒有檢視的權限。',
    'user_login_required' => '登入欄位是必需的',
    'user_has_no_assets_assigned' => '目前沒有資產分配給此使用者。',
    'user_password_required' => '密碼欄位是必需的',
    'insufficient_permissions' => '權限不足',
    'user_deleted_warning' => '此使用者已被刪除。您必須先還原此使用者才能進行編輯或分配新的資產。',
    'ldap_not_configured' => 'LDAP 整合尚未設定',
    'password_resets_sent' => '已向選定的已啟動且擁有有效電子郵件地址的使用者傳送了密碼重設連結。',
    'not_activated' => '此使用者無法登入，因此無法透過地址郵件確認接收資產。',
    'password_reset_sent' => '密碼重置連結已傳送至 :email',
    'user_has_no_email' => '該使用者的個人資料尚未填寫電子郵件。',
    'log_record_not_found' => '找不到此使用者對應的日誌記錄。',

    'success' => [
        'create' => '新增使用者成功。',
        'update' => '更新使用者成功。',
        'update_bulk' => '使用者更新成功 ！',
        'delete' => '刪除使用者成功。',
        'ban' => '禁止使用者成功。',
        'unban' => '解禁使用者成功。',
        'suspend' => '停用使用者成功。',
        'unsuspend' => '解除停用使用者成功。',
        'restored' => '恢復使用者成功。',
        'import' => '匯入使用者成功。',
        'acceptance_reminder_sent' => '已傳送 :count 個待接受項目的提醒。|已傳送 :count 個待接受項目的提醒。',
    ],

    'error' => [
        'create' => '新增使用者失敗，請重試。',
        'update' => '更新使用者失敗，請重試。',
        'delete' => '刪除使用者失敗，請重試。',
        'delete_has_assets' => '此使用者已分配物件，無法刪除。',
        'delete_has_assets_var' => '此使用者仍有資產尚未繳回，請先繳回再刪除。|此使用者仍有 :count 項資產尚未繳回，請先繳回再刪除。',
        'delete_has_licenses_var' => '此使用者仍有授權座位尚未繳回，請先繳回再刪除。|此使用者仍有 :count 個授權座位尚未繳回，請先繳回再刪除。',
        'delete_has_accessories_var' => '此使用者仍有配件尚未繳回，請先繳回再刪除。|此使用者仍有 :count 個配件尚未繳回，請先繳回再刪除。',
        'delete_has_locations_var' => '此使用者仍管理一個位置，請先選擇其他主管。|此使用者仍管理 :count 個位置，請先選擇其他主管。',
        'delete_has_users_var' => '此使用者仍管理另一位使用者，請先為該使用者選擇其他主管。|此使用者仍管理 :count 位使用者，請先為他們選擇其他主管。',
        'unsuspend' => '解除停用使用者失敗，請重試。',
        'import' => '匯入使用者失敗，請重試。',
        'asset_already_accepted' => '此項目已被接受。',
        'accept_or_decline' => '您必須選擇接受或拒絕該資產。',
        'cannot_delete_yourself' => '如果您刪除了自己，我們會感到非常遺憾，請重新考慮。',
        'incorrect_user_accepted' => '您正嘗試接受的資產未分配給您',
        'ldap_could_not_connect' => '無法連接到 LDAP 伺服器，請檢查 LDAP 設定文件中的相關設定。<br>LDAP 伺服器錯誤訊息：',
        'ldap_could_not_bind' => '無法綁定 LDAP 伺服器，請檢查 LDAP 設定文件中的相關設定。<br>LDAP 伺服器錯誤訊息：',
        'ldap_could_not_search' => '查詢 LDAP 伺服器失敗，請檢查 LDAP 設定文件中的相關設定。<br>LDAP 伺服器錯誤訊息：',
        'ldap_could_not_get_entries' => ' LDAP 伺服器取得資訊條目失敗，請檢查 LDAP 設定文件中的相關設定。<br>LDAP 伺服器錯誤訊息：',
        'password_ldap' => '此帳戶的密碼由 LDAP/AD 管理。若要更改您的密碼，請聯繫您的 IT 部門。 ',
        'multi_company_items_assigned' => '此使用者已分配屬於不同公司的項目。請先繳回或編輯其所屬公司。',
        'no_pending_acceptances' => '此使用者沒有任何待接受的項目需要提醒。',
    ],

    'deletefile' => [
        'error' => '刪除檔案失敗，請重試',
        'success' => '刪除檔案成功。',
    ],

    'upload' => [
        'error' => '上傳檔案失敗，請重試',
        'success' => '上傳檔案成功。',
        'nofiles' => '尚未選擇要上傳的檔案',
        'invalidfiles' => '一個或多個檔案太大或屬於不被允許的檔案類型。允許上傳的檔案類型：png, gif, jpg, doc, docx, pdf, txt。',
    ],

    'inventorynotification' => [
        'error' => '該用戶未設定email',
        'success' => '已就當前資產通知此用戶',
    ],
];
