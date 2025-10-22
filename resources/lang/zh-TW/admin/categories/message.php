<?php

return array(

    'does_not_exist' => '類別不存在',
    'assoc_models'	 => '至少還有一個樣板與此類別關聯，目前不能被刪除，請檢查後重試。 ',
    'assoc_items'	 => '至少還有一個 :asset_type 與此類別關聯，目前不能被刪除，請您檢查 :asset_type 後重試。 ',

    'create' => array(
        'error'   => '新增類別失敗，請重試。',
        'success' => '新增類別成功。'
    ),

    'update' => array(
        'error'   => '更新類別失敗，請重試。',
        'success' => '更新類別成功。',
        'cannot_change_category_type'   => '類別類型一旦建立就不可更改',
    ),

    'delete' => array(
        'confirm'                => '您確定要刪除此類別嗎？',
        'error'                  => '刪除類別時發生問題。請再試一次。',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
