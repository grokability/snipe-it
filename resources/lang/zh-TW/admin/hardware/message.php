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
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
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

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
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
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => '標頭列中的一個或多個屬性包含異常的 UTF-8 字元',
        'content_row_has_malformed_characters' => '內容的第一列中的一個或多個屬性包含異常的 UTF-8 字元',
        'transliterate_failure' => '由於輸入中包含無效字元，從 :encoding 轉換至 UTF-8 失敗',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => '刪除',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => '新增於',
            'updated' => '已更新',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => '匯入',
        'required_fields_missing' => 'The following required fields are not mapped: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(missing asset tag)',
            'missing_asset_tag_message' => 'Row skipped: no asset tag provided.',
            'asset_not_found_message' => 'Asset with this tag does not exist. Import assets first, then re-run the history import.',
            'target_not_matched_message' => 'No :target_type matched ":name". For users, toggle the match-by options in step 1 or create the user first. For locations, make sure the CSV location name matches an existing location exactly.',
            'invalid_target_type_message' => 'Target Type ":value" is not recognized. Use "user" or "location", or leave the column blank to default to user.',
        ],
        'wizard' => [
            'step_type' => 'Choose type',
            'step_map' => 'Map fields',
            'step_preview' => '預覽',
            'back' => '返回',
            'next' => '下一頁',
            'preview_button' => '預覽',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
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

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => '你必須至少選擇一項資產。',
    ],

    'checkin' => [
        'error' => '繳回資產失敗，請重試。',
        'success' => '繳回資產成功。',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
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
        'duplicate' => 'You already have an active request for this item.',
        'no_active' => 'You have no active request to cancel for this item.',
        'insufficient_stock' => 'Not enough on hand to fulfill this request. Replenish first.',
        'confirm_cancel_by_admin' => "Cancel :user's request for :item?",
        'no_selection' => 'No rows were selected to fulfill.',
        'row_stale' => 'Request #:id is no longer pending and was skipped.',
        'row_qty_invalid' => 'Request #:id has an invalid quantity and was skipped.',
        'row_user_missing' => 'Request #:id has no valid target user and was skipped.',
        'row_asset_missing' => 'Request #:id has no valid target asset and was skipped.',
        'row_asset_not_requesters' => 'Request #:id skipped: selected asset does not belong to the requester.',
        'row_asset_not_available' => 'Request #:id skipped: selected asset is not an available unit of this model.',
        'row_asset_taken' => 'Request #:id skipped: selected asset was assigned to someone else before submit.',
        'row_company_mismatch' => 'Request #:id skipped: :user cannot receive items from this company.',
        'row_over_allocated' => 'Request #:id skipped: not enough on hand at the moment of submit.',
        'no_target_assets_for_user' => ':user has no assets to install into.',
        'no_available_units' => 'No available units of this model to hand out.',
        'bulk_summary' => 'Fulfilled :fulfilled of :total requests.',
        'bulk_fulfill_notification_intro' => 'The following will apply to each ticked request when fulfilled:',
    ],

];
