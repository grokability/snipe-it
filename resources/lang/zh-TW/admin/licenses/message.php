<?php

return [

    'does_not_exist' => '授權不存在，或者您沒有權限檢視。',
    'user_does_not_exist' => '使用者不存在或您沒有檢視權限。',
    'asset_does_not_exist' => '您正在授權的資產不存在。',
    'owner_doesnt_match_asset' => '您正在授權的資產已被其他人佔用，請重新選擇。',
    'assoc_users' => '此授權已分配給某個使用者，目前不能被刪除，請檢查後重試。',
    'select_asset_or_person' => '您必須選擇資產或用戶，但不能同時選擇兩者。',
    'not_found' => '找不到授權',
    'seats_available' => ':seat_count 個名額可用',

    'create' => [
        'error' => '新增授權失敗，請重試。',
        'success' => '新增授權成功。',
    ],

    'deletefile' => [
        'error' => '刪除檔案失敗，請重試。',
        'success' => '刪除檔案成功。',
    ],

    'upload' => [
        'error' => '上傳檔案失敗，請重試。',
        'success' => '上傳檔案成功。',
        'nofiles' => '您尚未選擇要上傳的檔案，或上傳的檔案太大。',
        'invalidfiles' => '一個或多個檔案太大，或者是不允許的檔案類型。允許的檔案類型有 png、 gif、 jpg、 jpeg、 doc、 docx、 pdf、 txt、 zip、 rar、 rtf、 xml 和 lic。',
    ],

    'update' => [
        'error' => '更新授權失敗，請重試。',
        'success' => '更新授權成功。',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此授權嗎？',
        'error' => '刪除授權時發生問題，請重試。',
        'success' => '刪除授權成功。',
        'bulk_success' => '已成功刪除所選的授權。',
        'partial_success' => '授權已成功刪除。請參閱下方的附加資訊。| :count 個授權已成功刪除。請參閱下方的附加資訊。',
        'bulk_checkout_warning' => ':license_name 有目前正在借出的名額，無法刪除。請先將所有名額繳回後再進行刪除。',
    ],

    'checkout' => [
        'error' => '借出授權時發生問題，請重試。',
        'success' => '借出授權成功。',
        'not_enough_seats' => '可借出的授權名額不足',
        'mismatch' => '所提供的授權名額與授權不符',
        'unavailable' => '此名額目前無法借出。',
        'license_is_inactive' => '此授權已過期或已終止。',
    ],

    'checkin' => [
        'error' => '繳回授權時發生問題，請重試。',
        'not_reassignable' => '名額已被使用',
        'success' => '繳回授權成功。',
    ],

];
