<?php

return array(

    'accepted'                  => '你已成功接受此项资产。',
    'declined'                  => '你已拒绝此项资产。',
    'bulk_manager_warn'	        => '您的用户已成功更新，但经理信息未保存，因为您选择的经理也在本次编辑的用户列表中，用户不能是自己的经理。请重新选择您的用户，但这次请排除经理。',
    'user_exists'               => '用户已经存在!',
    'user_not_found'            => '用户不存在或您没有权限查看。',
    'user_login_required'       => '登陆字段是必须的',
    'user_has_no_assets_assigned' => '目前没有分配给用户的资产。',
    'user_password_required'    => '密码为必填项',
    'insufficient_permissions'  => '权限不足',
    'user_deleted_warning'      => '用户已经被删除，您需要恢复这个用户才能对其进行编辑或者分配新资产。',
    'ldap_not_configured'        => '本次安装过程中未启用LDAP集成。',
    'password_resets_sent'      => '密码重置链接已发送给符合以下条件的已选用户：已激活且邮箱地址有效。',
    'password_reset_sent'       => '密码重置链接已发送至 :email!',
    'user_has_no_email'         => '此用户的个人资料中没有电子邮件地址。',
    'log_record_not_found'        => '找不到该用户匹配的日志记录。',


    'success' => array(
        'create'    => '用户创建成功',
        'update'    => '用户更新成功。',
        'update_bulk'    => '用户更新成功。',
        'delete'    => '用户已经被删除',
        'ban'       => '用户禁止成功',
        'unban'     => '用户成功解禁',
        'suspend'   => '用户已经成功停用',
        'unsuspend' => '用户解除停用',
        'restored'  => '用户成功被恢复。',
        'import'    => '导入用户成功',
    ),

    'error' => array(
        'create' => '创建用户过程中出现了一些问题，请重试。',
        'update' => '更新用户过程中出现了一些问题，请重试。',
        'delete' => '删除用户过程中出现了一点儿问题，请重试。',
        'delete_has_assets' => '此用户具有分配的项目，无法删除。',
        'delete_has_assets_var' => '此用户仍然有分配的资产，请先归还。|此用户仍然有分配 :count 个资产，请先归还资产。',
        'delete_has_licenses_var' => '此用户仍然有一个已分配的许可证席位，请先归还。|此用户仍然已经分配了 :count 个许可证席位。请先归还它们。',
        'delete_has_accessories_var' => '此用户仍分配有一件配件，请先归还。|此用户仍然被分配有 :count 件配件，请先归还。',
        'delete_has_locations_var' => '此用户仍然管理着一个位置，请先选择另一个经理。 |此用户仍然管理着 :count 个位置。请先选择另一个经理。',
        'delete_has_users_var' => '此用户仍在管理另一个用户，请先为该用户选择另一个经理。 |此用户仍然管理着 :count 个用户。请先为他们选择另一个经理。',
        'unsuspend' => '恢复停用用户的过程中出现了一点儿问题，请重试。',
        'import'    => '导入用户出现问题。请再试一次。',
        'asset_already_accepted' => '资产已被接受',
        'accept_or_decline' => '你必须选择接受或者拒绝该资产。',
        'cannot_delete_yourself' => '如果您删除自己，我们会觉得很难过，请您重新考虑。',
        'incorrect_user_accepted' => '您正尝试接受的资产未被分配与您',
        'ldap_could_not_connect' => '无法连接到LDAP服务器，请检查LDAP配置文件中的相关设置。<br>LDAP服务器错误信息:',
        'ldap_could_not_bind' => '无法绑定到LDAP服务器，请检查LDAP配置文件中的相关设置。<br>LDAP服务器错误信息: ',
        'ldap_could_not_search' => '查询LDAP服务器失败，请检查LDAP配置文件中的相关设置。<br>LDAP服务器错误信息:',
        'ldap_could_not_get_entries' => '从LDAP服务器获取信息条目失败，请检查LDAP配置文件中的相关设置。<br>LDAP服务器错误信息:',
        'password_ldap' => '此帐户的密码由LDAP / Active Directory管理。请联系您的IT部门更改您的密码。',
        'multi_company_items_assigned' => '该用户被分配的物品属于另一家公司。请归还它们或编辑它们所在的公司。'
    ),

    'deletefile' => array(
        'error'   => '文件删除失败，请重试',
        'success' => '文件已成功删除。',
    ),

    'upload' => array(
        'error'   => '文件上传失败，请重试。',
        'success' => '文件已上传成功。',
        'nofiles' => '您没有选择要上传的文件',
        'invalidfiles' => '一个或多个文件过大或文件类型不被允许。允许上传的文件类型有PNG，GIF，JPG，DOC，DOCX，PDF和TXT。',
    ),

    'inventorynotification' => array(
        'error'   => '此用户没有设置电子邮件。',
        'success' => '已通知用户其当前库存。'
    )
);