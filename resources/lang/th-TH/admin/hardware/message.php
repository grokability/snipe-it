<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'ไม่มีสินทรัพย์',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'สินทรัพย์นั้นไม่มีอยู่หรือไม่สามารถร้องขอได้',
    'assoc_users' => 'ขณะนี้มีการตรวจสอบเนื้อหานี้แก่ผู้ใช้และไม่สามารถลบออกได้ โปรดตรวจสอบเนื้อหาเป็นครั้งแรกจากนั้นลองลบอีกครั้ง',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error' => 'ไม่ได้สร้างเนื้อหาโปรดลองอีกครั้ง :(',
        'success' => 'สร้างเนื้อหาสำเร็จแล้ว :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
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
        'error' => 'ไม่ได้อัปเดตเนื้อหาโปรดลองอีกครั้ง',
        'success' => 'อัปเดตเนื้อหาสำเร็จแล้ว',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated' => 'ไม่มีการเลือกเขตข้อมูลดังนั้นไม่มีการอัปเดตอะไรเลย',
        'no_assets_selected' => 'ไม่มีการเลือกรายการสินทรัพย์ จึงไม่มีการอัพเดท',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'ไม่ได้กู้คืนเนื้อหาโปรดลองอีกครั้ง',
        'success' => 'กู้คืนเนื้อหาเรียบร้อยแล้ว',
        'bulk_success' => 'กู้คืนเนื้อหาเรียบร้อยแล้ว',
        'nothing_updated' => 'No assets were selected, so nothing was restored.',
    ],

    'audit' => [
        'error' => 'Asset audit unsuccessful: :error ',
        'success' => 'บันทึกการตรวจสอบสินทรัพย์สำเร็จแล้ว',
    ],

    'deletefile' => [
        'error' => 'ไฟล์ไม่ถูกลบ กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ถูกลบเรียบร้อยแล้ว',
    ],

    'upload' => [
        'error' => 'ไฟล์ไม่ได้อัปโหลด กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ที่อัปโหลดเรียบร้อยแล้ว',
        'nofiles' => 'คุณไม่ได้เลือกไฟล์ใด ๆ สำหรับการอัปโหลดหรือไฟล์ที่คุณกำลังพยายามอัปโหลดมีขนาดใหญ่เกินไป',
        'invalidfiles' => 'ไฟล์ของคุณอย่างน้อยหนึ่งไฟล์มีขนาดใหญ่เกินไปหรือเป็นไฟล์ที่ไม่ได้รับอนุญาต ไฟล์ที่อนุญาตคือ png, gif, jpg, doc, docx, pdf และ txt',
    ],

    'import' => [
        'import_button' => 'Process Import',
        'error' => 'บางรายการไม่สามารถนำเข้าได้อย่างถูกต้อง',
        'errorDetail' => 'รายการต่อไปนี้ไม่ได้นำเข้าเนื่องจากมีข้อผิดพลาด',
        'success' => 'ไฟล์ของคุณถูกนำเข้าแล้ว',
        'file_delete_success' => 'ไฟล์ของคุณถูกลบเรียบร้อยแล้ว',
        'file_delete_error' => 'ไม่สามารถลบไฟล์ได้',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'ลบ',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'สร้างแล้ว',
            'updated' => 'อัปเดตแล้ว',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'นำเข้า',
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
            'step_preview' => 'พรีวิว',
            'back' => 'ย้อนกลับ',
            'next' => 'ถัด​ไป',
            'preview_button' => 'พรีวิว',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบเนื้อหานี้',
        'error' => 'เกิดปัญหาในการลบเนื้อหา กรุณาลองอีกครั้ง.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'ไม่มีการเลือกเนื้อหาใด ๆ ดังนั้นจึงไม่มีสิ่งใดถูกลบ',
        'success' => 'เนื้อหาถูกลบเรียบร้อยแล้ว',
    ],

    'checkout' => [
        'error' => 'ไม่ได้ตรวจสอบเนื้อหาโปรดลองอีกครั้ง',
        'success' => 'ตรวจสอบสินทรัพย์เรียบร้อยแล้ว',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
        'not_available' => 'เนื้อหาดังกล่าวไม่สามารถใช้ได้สำหรับเช็คเอาท์!',
        'no_assets_selected' => 'คุณต้องเลือกอย่างน้อยหนึ่งสินทรัพย์จากรายการ',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'คุณต้องเลือกอย่างน้อยหนึ่งสินทรัพย์จากรายการ',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'คุณต้องเลือกอย่างน้อยหนึ่งสินทรัพย์จากรายการ',
    ],

    'checkin' => [
        'error' => 'ไม่ได้เช็คอินเนื้อหาโปรดลองอีกครั้ง',
        'success' => 'ตรวจสอบเนื้อหาเรียบร้อยแล้ว',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
        'already_checked_in' => 'มีการตรวจสอบเนื้อหาดังกล่าวแล้ว',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'ยกเลิกการแจ้งความต้องการสิ่งนี้',
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
