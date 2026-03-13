<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Хөрөнгө байхгүй байна.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Энэ хөрөнгийг одоогоор хэрэглэгчид шалгаж, устгах боломжгүй байна. Эхлээд хөрөнгийг шалгаж үзээд дараа нь устга.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Акт үүсгээгүй байна, дахин оролдоно уу. :(',
        'success' 		=> 'Хөрөнгө амжилттай болсон. :)',
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
        'error'   			=> 'Хөрөнгийн шинэчлэлт хийгдээгүй тул дахин оролдоно уу',
        'success' 			=> 'Акт амжилттай шинэчлэгдсэн.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Ямар ч талбар сонгогдоогүй тул шинэчлэгдээгүй байна.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Хөрөнгө сэргээгээгүй байна, дахин оролдоно уу',
        'success' 		=> 'Хөрөнгийн амжилттай сэргээгдэв.',
        'bulk_success' 		=> 'Хөрөнгийн амжилттай сэргээгдэв.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Хөрөнгийн аудит амжилттай нэвтэрсэн байна.',
    ],


    'deletefile' => [
        'error'   => 'Файлыг устгаагүй байна. Дахин оролдоно уу.',
        'success' => 'Файл амжилттай устгагдсан.',
    ],

    'upload' => [
        'error'   => 'Файлд байршуулаагүй файл. Дахин оролдоно уу.',
        'success' => 'Файлууд амжилттай байршуулсан.',
        'nofiles' => 'Та байршуулах ямар ч файл сонгоогүй, эсвэл байршуулах гэж буй файл хэт том байна',
        'invalidfiles' => 'Таны файлуудын нэг юмуу хэд нь хэтэрхий том юмуу эсвэл файлын төрлийг зөвшөөрдөггүй. Зөвшөөрөгдсөн filetypes нь png, gif, jpg, doc, docx, pdf, болон txt байна.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Зарим зүйлс зөв импорт хийгдээгүй байна.',
        'errorDetail'           => 'Дараах зүйлсийг алдааны улмаас импортолсонгүй.',
        'success'               => 'Таны файл импортлогдсон байна',
        'file_delete_success'   => 'Таны файл амжилттай болсон байна',
        'file_delete_error'      => 'Файл устгагдах боломжгүй байна',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Та энэ хөрөнгийг устгахыг хүсч байна уу?',
        'error'   		=> 'Хөрөнгийг устгах асуудал гарлаа. Дахин оролдоно уу.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Ямар ч хөрөнгө сонгогдоогүй тул юу ч устгаагүй.',
        'success' 		=> 'Хөрөнгийг амжилттай устгасан байна.',
    ],

    'checkout' => [
        'error'   		=> 'Хөрөнгийг шалгаагүй байна, дахин оролдоно уу',
        'success' 		=> 'Акт амжилттай шалгасан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
        'not_available' => 'Энэ хөрөнгийг татаж авахад бэлэн биш байна!',
        'no_assets_selected' => 'Жагсаалтаас доод тал нь нэг хөрөнгийг сонгоно уу',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Хөрөнгө оруулаагүй байна, дахин оролдоно уу',
        'success' 		=> 'Хөрөнгө амжилттай шалгагдсан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
        'already_checked_in'  => 'Энэ аккаунтыг аль хэдийн шалгасан байна.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
