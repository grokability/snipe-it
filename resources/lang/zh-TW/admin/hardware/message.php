<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => '資產不存在',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => '該資產不存在或無法申請。',
    'assoc_users'	 	 => '此資產目前已借給某個使用者，不能被刪除，請檢查資產狀態，然後再嘗試刪除。',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> '新增資產失敗，請重試。',
        'success' 		=> '新增資產成功。',
        'success_linked' => 'Asset with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> '更新資產失敗，請重試。',
        'success' 			=> '更新資產成功。',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  '沒有欄位被選擇，因此沒有更新任何內容。',
        'no_assets_selected'  =>  '沒有資產被選取，因此沒有更新任何內容。',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> '恢復資產失敗，請重試。',
        'success' 		=> '恢復資產成功。',
        'bulk_success' 		=> '資產成功還原。',
        'nothing_updated'   => '未選擇任何資產，因此未進行任何還原。', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> '資產稽核成功登錄。',
    ],


    'deletefile' => [
        'error'   => '刪除檔案失敗，請重試。',
        'success' => '刪除檔案成功。',
    ],

    'upload' => [
        'error'   => '上傳檔案失敗，請重試。',
        'success' => '上傳檔案成功。',
        'nofiles' => '您尚未選擇要上傳的檔案，或上傳的檔案太大。',
        'invalidfiles' => '一個或多個檔案太大或屬於不被允許的檔案類型。允許上傳的檔案類型：png, gif, jpg, doc, docx, pdf, txt。',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => '某些項目沒有被正確匯入。',
        'errorDetail'           => '以下項目由於錯誤未被匯入。',
        'success'               => '您的檔案已被匯入。',
        'file_delete_success'   => '您的檔案已成功刪除。',
        'file_delete_error'      => '您的檔案無法被刪除。',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => '標頭列中的一個或多個屬性包含異常的 UTF-8 字元',
        'content_row_has_malformed_characters' => '內容的第一列中的一個或多個屬性包含異常的 UTF-8 字元',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> '您確定要刪除此資產嗎？',
        'error'   		=> '刪除資產時發生問題，請重試。',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => '沒有資產被選擇，因此沒有更新任何內容。',
        'success' 		=> '刪除資產成功。',
    ],

    'checkout' => [
        'error'   		=> '借出資產失敗，請重試。',
        'success' 		=> '借出資產成功。',
        'user_does_not_exist' => '無效使用者，請重試。',
        'not_available' => '此資產無法借出',
        'no_assets_selected' => '你必須至少選擇一項資產。',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> '繳回資產失敗，請重試。',
        'success' 		=> '繳回資產成功。',
        'user_does_not_exist' => '無效使用者，請重試。',
        'already_checked_in'  => '資產已繳回。',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => '取消這項物品的申請',
    ],

];
