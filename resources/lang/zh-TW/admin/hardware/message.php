<?php

return [

    'undeployable' => '以下資產無法被部署且已經從 借出: :資產標籤 中移除',
    'does_not_exist' => '資產不存在',
    'does_not_exist_var' => '找不到資產標籤為 :asset_tag 的資產。',
    'no_tag' => '未提供資產標籤。',
    'does_not_exist_or_not_requestable' => '該資產不存在或無法申請。',
    'assoc_users' => '此資產目前已借給某個使用者，不能被刪除，請檢查資產狀態，然後再嘗試刪除。',
    'warning_audit_date_mismatch' => '此資產的下次稽核日期（:next_audit_date）早於最後稽核日期（:last_audit_date）。請更新下次稽核日期。',
    'labels_generated' => '標籤已成功產生。',
    'error_generating_labels' => '產生標籤時發生錯誤。',
    'no_assets_selected' => '未選取任何資產。',

    'create' => [
        'error' => '新增資產失敗，請重試。',
        'success' => '新增資產成功。',
        'success_linked' => '資產標籤 :tag 已成功建立。<strong><a href=":link" style="color: white;">點擊這裡查看</a></strong>。',
        'multi_success_linked' => '資產標籤 :links 已成功建立。|:count 個資產已成功建立。:links。',
        'partial_failure' => '一個資產無法建立。原因：:failures|:count 個資產無法建立。原因：:failures',
        'target_not_found' => [
            'user' => '找不到指定的使用者。',
            'asset' => '找不到指定的資產。',
            'location' => '找不到指定的位置。',
        ],
    ],

    'update' => [
        'error' => '更新資產失敗，請重試。',
        'success' => '更新資產成功。',
        'encrypted_warning' => '資產更新成功，但由於權限不足，加密自訂欄位未被更新',
        'nothing_updated' => '沒有欄位被選擇，因此沒有更新任何內容。',
        'no_assets_selected' => '沒有資產被選取，因此沒有更新任何內容。',
        'assets_do_not_exist_or_are_invalid' => '選取的資產無法更新。',
    ],

    'restore' => [
        'error' => '恢復資產失敗，請重試。',
        'success' => '恢復資產成功。',
        'bulk_success' => '資產成功還原。',
        'nothing_updated' => '未選擇任何資產，因此未進行任何還原。',
    ],

    'audit' => [
        'error' => '資產稽核失敗：:error ',
        'success' => '資產稽核成功登錄。',
    ],

    'deletefile' => [
        'error' => '刪除檔案失敗，請重試。',
        'success' => '刪除檔案成功。',
    ],

    'upload' => [
        'error' => '上傳檔案失敗，請重試。',
        'success' => '上傳檔案成功。',
        'nofiles' => '您尚未選擇要上傳的檔案，或上傳的檔案太大。',
        'invalidfiles' => '一個或多個檔案太大或屬於不被允許的檔案類型。允許上傳的檔案類型：png, gif, jpg, doc, docx, pdf, txt。',
    ],

    'import' => [
        'import_button' => '執行匯入',
        'error' => '某些項目沒有被正確匯入。',
        'errorDetail' => '以下項目由於錯誤未被匯入。',
        'success' => '您的檔案已被匯入。',
        'file_delete_success' => '您的檔案已成功刪除。',
        'file_delete_error' => '您的檔案無法被刪除。',
        'file_missing' => '選取的檔案遺失',
        'file_already_deleted' => '選取的檔案已被刪除',
        'header_row_has_malformed_characters' => '標頭列中的一個或多個屬性包含異常的 UTF-8 字元',
        'content_row_has_malformed_characters' => '內容的第一列中的一個或多個屬性包含異常的 UTF-8 字元',
        'transliterate_failure' => '由於輸入中包含無效字元，從 :encoding 轉換至 UTF-8 失敗',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此資產嗎？',
        'error' => '刪除資產時發生問題，請重試。',
        'assigned_to_error' => '{1}資產標籤：:asset_tag 目前已被借出。請在刪除前先繳回此裝置。|[2,*]資產標籤：:asset_tag 目前已被借出。請在刪除前先繳回這些裝置。',
        'nothing_updated' => '沒有資產被選擇，因此沒有更新任何內容。',
        'success' => '刪除資產成功。',
    ],

    'checkout' => [
        'error' => '借出資產失敗，請重試。',
        'success' => '借出資產成功。',
        'user_does_not_exist' => '無效使用者，請重試。',
        'not_available' => '此資產無法借出',
        'no_assets_selected' => '你必須至少選擇一項資產。',
    ],

    'multi-checkout' => [
        'error' => '資產借出失敗，請重試。|資產借出失敗，請重試。',
        'success' => '資產借出成功。|資產借出成功。',
    ],

    'multi-checkin' => [
        'error' => '資產繳回失敗，請重試。|資產繳回失敗，請重試。',
        'success' => '資產繳回成功。|資產繳回成功。',
        'no_assets_selected' => '你必須至少選擇一項資產。',
    ],

    'checkin' => [
        'error' => '繳回資產失敗，請重試。',
        'success' => '繳回資產成功。',
        'user_does_not_exist' => '無效使用者，請重試。',
        'already_checked_in' => '資產已繳回。',
        'force_checkin_orphaned_success' => '無效的分配已成功清除。',
        'force_checkin_not_orphaned' => '項目目前不在無效的分配狀態。',
        'force_checkin_error' => '無法清除無效的分配。',

    ],

    'requests' => [
        'error' => '申請失敗，請重試。',
        'success' => '申請已成功送出。',
        'canceled' => '申請已成功取消。',
        'cancel' => '取消這項物品的申請',
    ],

];
