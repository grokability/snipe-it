<?php

return array(

    'does_not_exist' => '许可证不存在或您没有查看权限。',
    'user_does_not_exist' => '用户不存在或您没有查看权限。',
    'asset_does_not_exist' 	=> '您正在授权的资产不存在。',
    'owner_doesnt_match_asset' => '您尝试与此许可证关联的资产不属于在“分配给”下拉菜单中选择的人。',
    'assoc_users'	 => '此许可证已经分配给某个用户，目前不能被删除，请检查资产信息，然后再尝试删除。',
    'select_asset_or_person' => '您必须选择资产或用户，但不能同时选择两者。',
    'not_found' => '找不到许可证',
    'seats_available' => ':see_count 个席位可用',


    'create' => array(
        'error'   => '许可证没有被创建，请重试。',
        'success' => '许可证创建成功'
    ),

    'deletefile' => array(
        'error'   => '该文件无法删除，请重试。',
        'success' => '文件成功删除。',
    ),

    'upload' => array(
        'error'   => '文件上传失败，请重试。',
        'success' => '文件上传成功。',
        'nofiles' => '尚未选择要上传的文件，或上传的文件过大。',
        'invalidfiles' => '您的一个或多个文件过大或文件类型不被允许。允许的文件类型为 png、gif、jpg、jpeg、doc、docx、pdf、txt、zip、rar、rtf、xml 和 lic。',
    ),

    'update' => array(
        'error'   => '许可证更新失败，请重试。',
        'success' => '许可证更新成功。'
    ),

    'delete' => array(
        'confirm'   => '确定删除这条许可信息？',
        'error'   => '删除许可信息的过程中出现了一些问题，请重试。',
        'success' => '许可信息已经被成功删除。'
    ),

    'checkout' => array(
        'error'   => '签出许可证的过程中出现了一些问题，请重试。',
        'success' => '许可证已经成功签出',
        'not_enough_seats' => '没有足够的许可证席位可供签出',
        'mismatch' => '提供的许可证席位与许可证不匹配',
        'unavailable' => '这个席位不能签出。',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => '归还许可证的过程中出现了一些问题，请重试。',
        'not_reassignable' => '席位已被使用',
        'success' => '许可证已经成功归还。'
    ),

);
