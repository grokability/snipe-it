<?php

return [

    'undeployable' => '다음 자산은 배치할 수 없어 반출에서 제외되었습니다: :asset_tags',
    'does_not_exist' => '자산이 존재하지 않습니다.',
    'does_not_exist_var' => '자산 태그 :asset_tag(을)를 찾을 수 없습니다.',
    'no_tag' => '자산 태그가 제공되지 않았습니다.',
    'does_not_exist_or_not_requestable' => '해당 자산이 존재하지 않거나 요청 가능하지 않습니다.',
    'assoc_users' => '이 자산은 현재 사용자에게 반출 중이어서 삭제 할 수 없습니다. 먼저 자산을 확인해 보고 다시 삭제를 시도해 주세요. ',
    'warning_audit_date_mismatch' => '이 자산의 다음 감사 날짜(:next_audit_date)가 마지막 감사 날짜(:last_audit_date)보다 이전입니다. 다음 감사 날짜를 업데이트하세요.',
    'labels_generated' => '라벨이 성공적으로 생성되었습니다.',
    'error_generating_labels' => '라벨 생성 중 오류가 발생했습니다.',
    'no_assets_selected' => '선택된 자산이 없습니다.',

    'create' => [
        'error' => '자산이 생성되지 않았습니다. 다시 시도해 주세요. :(',
        'success' => '자산이 생성되었습니다. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => '자산 태그 :tag(으)로 자산이 성공적으로 생성되었습니다. <strong><a href=":link" style="color: white;">여기를 클릭하여 보기</a></strong>.',
        'multi_success_linked' => '태그 :links 자산이 성공적으로 생성되었습니다.|:count개 자산이 성공적으로 생성되었습니다. :links.',
        'partial_failure' => '자산을 생성할 수 없습니다. 사유: :failures|:count개 자산을 생성할 수 없습니다. 사유: :failures',
        'target_not_found' => [
            'user' => '할당된 사용자를 찾을 수 없습니다.',
            'asset' => '할당된 자산을 찾을 수 없습니다.',
            'location' => '할당된 위치를 찾을 수 없습니다.',
        ],
    ],

    'update' => [
        'error' => '자산이 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '자산이 갱신되었습니다.',
        'encrypted_warning' => '자산이 성공적으로 업데이트되었으나, 권한 부족으로 암호화된 사용자 정의 필드는 업데이트되지 않았습니다',
        'nothing_updated' => '선택된 항목이 없어서, 갱신 되지 않습니다.',
        'no_assets_selected' => '선택된 자산이 없어 업데이트되지 않았습니다.',
        'assets_do_not_exist_or_are_invalid' => '선택한 자산을 업데이트할 수 없습니다.',
    ],

    'bulk_update' => [
        'success' => '자산이 성공적으로 업데이트되었습니다.|:count개의 자산이 성공적으로 업데이트되었습니다.',
        'partial' => ':success개의 자산이 성공적으로 업데이트되고 :failed개가 실패했습니다. 자세한 내용은 결과 배열을 확인하세요.',
        'error' => '업데이트된 자산이 없습니다. 자세한 내용은 결과 배열을 확인하세요.',
    ],

    'restore' => [
        'error' => '자산이 복원되지 않았습니다. 다시 시도해 주세요.',
        'success' => '자산이 복원되었습니다.',
        'bulk_success' => '자산이 복원되었습니다.',
        'nothing_updated' => '선택된 자산이 없어 복원되지 않았습니다.',
    ],

    'audit' => [
        'error' => '자산 감사 실패: :error ',
        'success' => '자산 감사가 성공적으로 기록되었습니다.',
    ],

    'deletefile' => [
        'error' => '파일이 삭제되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일이 삭제되었습니다.',
    ],

    'upload' => [
        'error' => '파일(들)이 업로드 되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일(들)이 업로드 되었습니다.',
        'nofiles' => '업로드 하기 위한 파일이 선택되지 않았거나, 업로드 할 파일이 너무 큽니다.',
        'invalidfiles' => '하나 이상의 파일이 너무 크거나 허용되지 않는  형식입니다. 허용되는 형식은 png, gif, jpg, doc, docx, pdf, txt 입니다.',
    ],

    'import' => [
        'import_button' => '가져오기 처리',
        'error' => '몇몇 품목들을 정확하게 읽어오지 못했습니다.',
        'errorDetail' => '다음 품목들은 오류로 읽어오지 못했습니다.',
        'success' => '파일에서 읽어오기가 완료되었습니다',
        'file_delete_success' => '파일 삭제가 완료되었습니다',
        'file_delete_error' => '파일을 삭제할 수 없습니다',
        'file_missing' => '선택한 파일이 없습니다',
        'file_already_deleted' => '선택한 파일이 이미 삭제되었습니다',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => '헤더 행의 하나 이상의 속성에 잘못된 형식의 UTF-8 문자가 포함되어 있습니다',
        'content_row_has_malformed_characters' => '콘텐츠 첫 번째 행의 하나 이상의 속성에 잘못된 형식의 UTF-8 문자가 포함되어 있습니다',
        'transliterate_failure' => ':encoding에서 UTF-8로 변환하는 데 실패했습니다. 입력에 잘못된 문자가 있습니다',
        'bulk_delete' => [
            'button' => '선택 항목 삭제 (:count)',
            'confirm_title' => '선택한 가져오기 파일을 삭제하시겠습니까?',
            'confirm_body' => ':count개의 가져오기 파일을 영구적으로 삭제하려고 합니다. 이 작업은 취소할 수 없습니다.',
            'confirm_button' => '삭제',
            'success' => '가져오기 파일이 성공적으로 삭제되었습니다.|:count개의 가져오기 파일이 성공적으로 삭제되었습니다.',
            'skipped' => '삭제 권한이 없어 :count개의 파일을 건너뛰었습니다.',
            'select_all' => '이 페이지의 모든 파일 선택',
            'select_row' => ':file 일괄 삭제 선택',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => '생성일',
            'updated' => '업데이트됨',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => '불러오기',
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
            'step_preview' => '미리보기',
            'back' => '이전',
            'next' => '다음',
            'preview_button' => '미리보기',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => '이 자산을 삭제하시겠습니까?',
        'error' => '그룹을 삭제하는 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'assigned_to_error' => '{1}자산 태그: :asset_tag이(가) 현재 반출 중입니다. 삭제 전 이 장치를 반입하세요.|[2,*]자산 태그: :asset_tag이(가) 현재 반출 중입니다. 삭제 전 이 장치들을 반입하세요.',
        'nothing_updated' => '선택된 자산이 없기에, 삭제되지 않습니다.',
        'success' => '자산이 삭제되었습니다.',
    ],

    'checkout' => [
        'error' => '자산이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' => '자산이 반출되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'not_available' => '그 자산은 반출 할 수 없습니다!',
        'no_assets_selected' => '목록에서 자산을 하나 이상 선택해야 합니다.',
    ],

    'multi-checkout' => [
        'error' => '자산이 반출되지 않았습니다. 다시 시도하세요|자산이 반출되지 않았습니다. 다시 시도하세요',
        'success' => '자산이 성공적으로 반출되었습니다.|자산이 성공적으로 반출되었습니다.',
    ],

    'multi-checkin' => [
        'error' => '자산이 반입되지 않았습니다. 다시 시도하세요|자산이 반입되지 않았습니다. 다시 시도하세요',
        'success' => '자산이 성공적으로 반입되었습니다.|자산이 성공적으로 반입되었습니다.',
        'no_assets_selected' => '목록에서 자산을 하나 이상 선택해야 합니다.',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => '목록에서 자산을 하나 이상 선택해야 합니다.',
    ],

    'checkin' => [
        'error' => '자산이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' => '자산이 반입되었습니다.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'already_checked_in' => '그 자산은 이미 반입되었습니다.',
        'force_checkin_orphaned_success' => '잘못된 할당이 성공적으로 해제되었습니다.',
        'force_checkin_not_orphaned' => '항목이 잘못된 할당 상태가 아닙니다.',
        'force_checkin_error' => '잘못된 할당을 해제할 수 없습니다.',

    ],

    'requests' => [
        'error' => '요청이 성공하지 못했습니다. 다시 시도하세요.',
        'success' => '요청이 성공적으로 제출되었습니다.',
        'canceled' => '요청이 성공적으로 취소되었습니다.',
        'cancel' => '이 항목 요청 취소',
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
