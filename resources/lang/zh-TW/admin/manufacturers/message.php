<?php

return [

    'support_url_help' => '您可以在 URL 中使用變數 <code>{LOCALE}</code>、<code>{SERIAL}</code>、<code>{MODEL_NUMBER}</code> 和 <code>{MODEL_NAME}</code>，以便在檢視資產時自動填入這些值 - 例如 https://checkcoverage.apple.com/{LOCALE}/{SERIAL}。',
    'does_not_exist' => '製造商不存在',
    'assoc_users' => '至少還有一個資產與此製造商關聯，目前不能被删除，請確認後重試。 ',

    'create' => [
        'error' => '新增製造商失敗，請重試。',
        'success' => '新增製造商成功。',
    ],

    'update' => [
        'error' => '更新製造商失敗，請重試。',
        'success' => '更新製造商成功。',
    ],

    'restore' => [
        'error' => '製造商恢復失敗，請重試。',
        'success' => '製造商恢復成功。',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此製造商嗎？',
        'error' => '刪除製造商失敗，請重試。',
        'success' => '製造商已成功刪除。',
        'bulk_success' => '製造商已成功刪除。',
        'partial_success' => '製造商已成功刪除。請參閱下方的附加資訊。| :count 個製造商已成功刪除。請參閱下方的附加資訊。',
    ],

];
