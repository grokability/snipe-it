<?php

return array(

    'does_not_exist' => '地點不存在.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => '至少還有一個資產與此位置關聯，目前不能被删除，請檢查後重試。 ',
    'assoc_child_loc'	 => '至少還有一個子項目與此位置關聯，目前不能被删除，請檢查後重試。 ',
    'assigned_assets' => '已分配資產',
    'current_location' => '目前位置',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => '新增位置失敗，請重試。',
        'success' => '新增位置成功。'
    ),

    'update' => array(
        'error'   => '更新位置失敗，請重試。',
        'success' => '成功更新地點.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> '您確定要刪除此位置嗎？',
        'error'   => '刪除位置時發生問題，請重試。',
        'success' => '刪除位置成功。'
    )

);
