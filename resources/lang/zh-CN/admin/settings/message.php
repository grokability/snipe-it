<?php

return [

    'update' => [
        'error'                 => '更新过程中出现了问题。',
        'success'               => '设置配置信息更新成功。',
    ],
    'backup' => [
        'delete_confirm'        => '你确定你想要删除该备份文件? 此操作无法撤消。 ',
        'file_deleted'          => '备份文件已成功删除。 ',
        'generated'             => '成功地创建了一个新的备份文件。',
        'file_not_found'        => '在服务器上找不到备份文件。',
        'restore_warning'       => '是的，还原它。我确认这将覆盖当前数据库中的所有数据。 这也将注销所有现有用户 (包括您)。',
        'restore_confirm'       => '您确定要从 :filename还原您的数据库吗？'
    ],
    'restore' => [
        'success'               => '您的系统备份已恢复。请重新登录。'
    ],
    'purge' => [
        'error'     => '清除过程中出现了错误。 ',
        'validation_failed'     => '你的清除确认信息不正确，请在输入框中输入“DELETE”。',
        'success'               => '删除记录已被成功的清除。',
    ],
    'mail' => [
        'sending' => '正在发送测试邮件...',
        'success' => '邮件已发送！',
        'error' => '邮件无法发送。',
        'additional' => '没有提供额外的错误信息。请检查您的邮件设置和应用日志。'
    ],
    'ldap' => [
        'testing' => '测试 LDAP 连接，绑定和查询 ...',
        '500' => '500 服务器错误。请检查您的服务器日志以获取更多信息。',
        'error' => '出错了:(',
        'sync_success' => '基于您的设置，从LDAP服务器返回的10个用户样本：',
        'testing_authentication' => '测试 LDAP 身份验证...',
        'authentication_success' => '用户已成功通过LDAP认证！'
    ],
    'labels' => [
        'null_template' => '未找到标签模板。请选择一个模板。',
        ],
    'webhook' => [
        'sending' => '正在发送 :app 测试消息...',
        'success' => '您的 :webhook_name 集成工作正常！',
        'success_pt1' => '成功！请检查 ',
        'success_pt2' => ' 测试消息的频道，并且一定要点击下面的<b>保存</b>来存储您的设置。',
        '500' => '500 服务器错误。',
        'error' => '出错了。:app 返回 :error_message',
        'error_redirect' => '错误：301/302 :endpoint 返回重定向。出于安全原因，我们不跟随重定向。请使用实际端点。',
        'error_misc' => '出错了:( ',
        'webhook_fail' => ' webhook 通知失败：请检查以确保URL仍然有效。',
        'webhook_channel_not_found' => ' 未找到 webhook 频道。',
        'ms_teams_deprecation' => '您当前选用的 Microsoft Teams Webhook URL 将于2025年12月31日弃用。请改用工作流 URL。有关创建工作流的微软官方文档，请见<a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">此处。</a>',
    ],
    'location_scoping' => [
        'not_saved' => '您的设置未保存。',
        'mismatch' => '在启用位置范围限定功能之前，数据库中有1个待处理项目需要您关注。|在启用位置范围限定功能之前，数据库中有:count个待处理项目需要您关注。',
    ],
];
