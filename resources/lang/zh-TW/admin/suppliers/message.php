<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => '供應商不存在',


    'create' => array(
        'error'   => '新增供應商失敗，請重試',
        'success' => '新增供應商成功。'
    ),

    'update' => array(
        'error'   => '更新供應商失敗，請重試',
        'success' => '更新供應商成功。'
    ),

    'delete' => array(
        'confirm'   => '您確定要刪除此供應商嗎？',
        'error'   => '刪除供應商失敗，請重試',
        'success' => '刪除供應商成功。',
        'assoc_assets'	 => '至少還有 :asset_count 個樣板與此供應商關聯，目前不能被刪除，請檢查後重試。 ',
        'assoc_licenses'	 => '至少還有 :licenses_count 個授權與此供應商關聯，目前不能被刪除，請檢查後重試。 ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
