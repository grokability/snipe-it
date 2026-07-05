<?php

return [

    'does_not_exist' => '地點不存在.',
    'assoc_users' => '此位置目前無法刪除，因為它是至少一個項目或使用者的記錄位置、有資產分配至此，或是另一個位置的上層位置。請更新您的記錄，移除對此位置的參照後再試一次。',
    'assoc_assets' => '至少還有一個資產與此位置關聯，目前不能被删除，請檢查後重試。 ',
    'assoc_child_loc' => '至少還有一個子項目與此位置關聯，目前不能被删除，請檢查後重試。 ',
    'assigned_assets' => '已分配資產',
    'current_location' => '目前位置',
    'deleted_warning' => '此位置已被刪除。請先還原後再進行任何變更。',

    'create' => [
        'error' => '新增位置失敗，請重試。',
        'success' => '新增位置成功。',
    ],

    'update' => [
        'error' => '更新位置失敗，請重試。',
        'success' => '成功更新地點.',
    ],

    'restore' => [
        'error' => '位置還原失敗，請重試。',
        'success' => '位置還原成功。',
    ],

    'delete' => [
        'confirm' => '您確定要刪除此位置嗎？',
        'error' => '刪除位置時發生問題，請重試。',
        'success' => '刪除位置成功。',
    ],

];
