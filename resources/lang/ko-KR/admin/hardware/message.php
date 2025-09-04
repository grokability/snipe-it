<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => '자산이 존재하지 않습니다.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => '이 자산은 현재 사용자에게 반출 중이어서 삭제 할 수 없습니다. 먼저 자산을 확인해 보고 다시 삭제를 시도해 주세요. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> '자산이 생성되지 않았습니다. 다시 시도해 주세요. :(',
        'success' 		=> '자산이 생성되었습니다. :)',
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
        'error'   			=> '자산이 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' 			=> '자산이 갱신되었습니다.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  '선택된 항목이 없어서, 갱신 되지 않습니다.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> '자산이 복원되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '자산이 복원되었습니다.',
        'bulk_success' 		=> '자산이 복원되었습니다.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> '자산 감사가 성공적으로 기록되었습니다.',
    ],


    'deletefile' => [
        'error'   => '파일이 삭제되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일이 삭제되었습니다.',
    ],

    'upload' => [
        'error'   => '파일(들)이 업로드 되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일(들)이 업로드 되었습니다.',
        'nofiles' => '업로드 하기 위한 파일이 선택되지 않았거나, 업로드 할 파일이 너무 큽니다.',
        'invalidfiles' => '하나 이상의 파일이 너무 크거나 허용되지 않는  형식입니다. 허용되는 형식은 png, gif, jpg, doc, docx, pdf, txt 입니다.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => '몇몇 품목들을 정확하게 읽어오지 못했습니다.',
        'errorDetail'           => '다음 품목들은 오류로 읽어오지 못했습니다.',
        'success'               => '파일에서 읽어오기가 완료되었습니다',
        'file_delete_success'   => '파일 삭제가 완료되었습니다',
        'file_delete_error'      => '파일을 삭제할 수 없습니다',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> '이 자산을 삭제하시겠습니까?',
        'error'   		=> '그룹을 삭제하는 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => '선택된 자산이 없기에, 삭제되지 않습니다.',
        'success' 		=> '자산이 삭제되었습니다.',
    ],

    'checkout' => [
        'error'   		=> '자산이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '자산이 반출되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'not_available' => '그 자산은 반출 할 수 없습니다!',
        'no_assets_selected' => '목록에서 자산을 하나 이상 선택해야 합니다.',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> '자산이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '자산이 반입되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'already_checked_in'  => '그 자산은 이미 반입되었습니다.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
