<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'ไม่มีสินทรัพย์',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'สินทรัพย์นั้นไม่มีอยู่หรือไม่สามารถร้องขอได้',
    'assoc_users'	 	 => 'ขณะนี้มีการตรวจสอบเนื้อหานี้แก่ผู้ใช้และไม่สามารถลบออกได้ โปรดตรวจสอบเนื้อหาเป็นครั้งแรกจากนั้นลองลบอีกครั้ง',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'ไม่ได้สร้างเนื้อหาโปรดลองอีกครั้ง :(',
        'success' 		=> 'สร้างเนื้อหาสำเร็จแล้ว :)',
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
        'error'   			=> 'ไม่ได้อัปเดตเนื้อหาโปรดลองอีกครั้ง',
        'success' 			=> 'อัปเดตเนื้อหาสำเร็จแล้ว',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'ไม่มีการเลือกเขตข้อมูลดังนั้นไม่มีการอัปเดตอะไรเลย',
        'no_assets_selected'  =>  'ไม่มีการเลือกรายการสินทรัพย์ จึงไม่มีการอัพเดท',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'ไม่ได้กู้คืนเนื้อหาโปรดลองอีกครั้ง',
        'success' 		=> 'กู้คืนเนื้อหาเรียบร้อยแล้ว',
        'bulk_success' 		=> 'กู้คืนเนื้อหาเรียบร้อยแล้ว',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'บันทึกการตรวจสอบสินทรัพย์สำเร็จแล้ว',
    ],


    'deletefile' => [
        'error'   => 'ไฟล์ไม่ถูกลบ กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ถูกลบเรียบร้อยแล้ว',
    ],

    'upload' => [
        'error'   => 'ไฟล์ไม่ได้อัปโหลด กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ที่อัปโหลดเรียบร้อยแล้ว',
        'nofiles' => 'คุณไม่ได้เลือกไฟล์ใด ๆ สำหรับการอัปโหลดหรือไฟล์ที่คุณกำลังพยายามอัปโหลดมีขนาดใหญ่เกินไป',
        'invalidfiles' => 'ไฟล์ของคุณอย่างน้อยหนึ่งไฟล์มีขนาดใหญ่เกินไปหรือเป็นไฟล์ที่ไม่ได้รับอนุญาต ไฟล์ที่อนุญาตคือ png, gif, jpg, doc, docx, pdf และ txt',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'บางรายการไม่สามารถนำเข้าได้อย่างถูกต้อง',
        'errorDetail'           => 'รายการต่อไปนี้ไม่ได้นำเข้าเนื่องจากมีข้อผิดพลาด',
        'success'               => 'ไฟล์ของคุณถูกนำเข้าแล้ว',
        'file_delete_success'   => 'ไฟล์ของคุณถูกลบเรียบร้อยแล้ว',
        'file_delete_error'      => 'ไม่สามารถลบไฟล์ได้',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'คุณแน่ใจหรือไม่ว่าต้องการลบเนื้อหานี้',
        'error'   		=> 'เกิดปัญหาในการลบเนื้อหา กรุณาลองอีกครั้ง.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'ไม่มีการเลือกเนื้อหาใด ๆ ดังนั้นจึงไม่มีสิ่งใดถูกลบ',
        'success' 		=> 'เนื้อหาถูกลบเรียบร้อยแล้ว',
    ],

    'checkout' => [
        'error'   		=> 'ไม่ได้ตรวจสอบเนื้อหาโปรดลองอีกครั้ง',
        'success' 		=> 'ตรวจสอบสินทรัพย์เรียบร้อยแล้ว',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
        'not_available' => 'เนื้อหาดังกล่าวไม่สามารถใช้ได้สำหรับเช็คเอาท์!',
        'no_assets_selected' => 'คุณต้องเลือกอย่างน้อยหนึ่งสินทรัพย์จากรายการ',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'ไม่ได้เช็คอินเนื้อหาโปรดลองอีกครั้ง',
        'success' 		=> 'ตรวจสอบเนื้อหาเรียบร้อยแล้ว',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
        'already_checked_in'  => 'มีการตรวจสอบเนื้อหาดังกล่าวแล้ว',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
