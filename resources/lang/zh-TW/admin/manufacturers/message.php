<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => '製造商不存在',
    'assoc_users'	 => '至少還有一個資產與此製造商關聯，目前不能被删除，請確認後重試。 ',

    'create' => array(
        'error'   => '新增製造商失敗，請重試。',
        'success' => '新增製造商成功。'
    ),

    'update' => array(
        'error'   => '更新製造商失敗，請重試。',
        'success' => '更新製造商成功。'
    ),

    'restore' => array(
        'error'   => '製造商恢復失敗，請重試。',
        'success' => '製造商恢復成功。'
    ),

    'delete' => array(
        'confirm' => '您確定要刪除此製造商嗎？',
        'error'   => '刪除製造商失敗，請重試。',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
