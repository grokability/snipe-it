<?php

return array(

    'does_not_exist' => '授權不存在，或者您沒有權限檢視。',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> '您正在授權的資產不存在。',
    'owner_doesnt_match_asset' => '您正在授權的資產已被其他人佔用，請重新選擇。',
    'assoc_users'	 => '此授權已分配給某個使用者，目前不能被刪除，請檢查後重試。',
    'select_asset_or_person' => '您必須選擇資產或用戶，但不能同時選擇兩者。',
    'not_found' => '找不到授權',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => '新增授權失敗，請重試。',
        'success' => '新增授權成功。'
    ),

    'deletefile' => array(
        'error'   => '刪除檔案失敗，請重試。',
        'success' => '刪除檔案成功。',
    ),

    'upload' => array(
        'error'   => '上傳檔案失敗，請重試。',
        'success' => '上傳檔案成功。',
        'nofiles' => '您尚未選擇要上傳的檔案，或上傳的檔案太大。',
        'invalidfiles' => '一個或多個檔案太大，或者是不允許的檔案類型。允許的檔案類型有 png、 gif、 jpg、 jpeg、 doc、 docx、 pdf、 txt、 zip、 rar、 rtf、 xml 和 lic。',
    ),

    'update' => array(
        'error'   => '更新授權失敗，請重試。',
        'success' => '更新授權成功。'
    ),

    'delete' => array(
        'confirm'   => '您確定要刪除此授權嗎？',
        'error'   => '刪除授權時發生問題，請重試。',
        'success' => '刪除授權成功。'
    ),

    'checkout' => array(
        'error'   => '借出授權時發生問題，請重試。',
        'success' => '借出授權成功。',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => '繳回授權時發生問題，請重試。',
        'not_reassignable' => 'Seat has been used',
        'success' => '繳回授權成功。'
    ),

);
