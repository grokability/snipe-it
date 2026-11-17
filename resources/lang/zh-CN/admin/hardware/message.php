<?php

return [

    'undeployable' => '以下资源无法部署并且已经从签出中移除：:asset_tags',
    'does_not_exist' => '资产不存在',
    'does_not_exist_var' => '找不到标签为 :asset_tag 的资产',
    'no_tag' => '未提供资产标签。',
    'does_not_exist_or_not_requestable' => '该资产不存在或不可申领。',
    'assoc_users' => '这个资产目前已经签出给某个用户，不能被删除，请检查资产信息，然后再尝试删除。 ',
    'warning_audit_date_mismatch' => '此资产的下一个盘点日期 (:next_audit_date) 早于上一个盘点日期 (:last_audit_date)。请更新下一个盘点日期。',
    'labels_generated' => '标签已成功生成。',
    'error_generating_labels' => '生成标签时出错。',
    'no_assets_selected' => '没有选择资产。',

    'create' => [
        'error' => '资产创建失败，请重试。:(',
        'success' => '资产创建成功。 :)',
        'success_no_checkout' => '资产创建成功，但由于您没有资产签出权限，因此未执行签出。',
        'checkout_skipped_no_permission' => '资产已创建，但由于您没有资产签出权限，因此未将其签出到请求的目标对象。',
        'success_linked' => '带有 :tag 标签的资产已成功创建。<strong><a href=":link" style="color: white;">点击此处查看</a></strong>。',
        'multi_success_linked' => '带有标签 :links 的资产已成功创建。| :count 个资产已成功创建。 :links。',
        'partial_failure' => '无法创建资产。原因：:failures|:count 个资产无法创建。原因：:failures',
        'target_not_found' => [
            'user' => '找不到被分配的用户。',
            'asset' => '找不到被分配的资产。',
            'location' => '找不到被分配的位置。',
        ],
    ],

    'update' => [
        'error' => '资产更新失败，请重试。',
        'success' => '资产更新成功。',
        'encrypted_warning' => '资产更新成功，但加密的自定义字段不是由于权限',
        'nothing_updated' => '一个字段也没选，所以不会更新。',
        'no_assets_selected' => '没有选择资产，所以没有任何内容被更新。',
        'assets_do_not_exist_or_are_invalid' => '无法更新选定的资产。',
    ],

    'bulk_update' => [
        'success' => '资产更新成功。|:count 个资产更新成功。',
        'partial' => ':successful 个资产更新成功，:failed 个失败。详情请参阅结果数组。',
        'error' => '没有更新资产。详情请查看结果数组。',
    ],

    'restore' => [
        'error' => '资产未被恢复，请重试。',
        'success' => '资产恢复成功。',
        'bulk_success' => '资产已成功恢复。',
        'nothing_updated' => '没有选择任何资产，所以没有恢复。',
    ],

    'audit' => [
        'error' => '资产盘点失败：:error ',
        'success' => '资产审计已成功记录。',
    ],

    'deletefile' => [
        'error' => '文件删除失败，请重试',
        'success' => '文件已成功删除。',
    ],

    'upload' => [
        'error' => '文件上传失败，请重试。',
        'success' => '文件已上传成功。',
        'nofiles' => '尚未选择要上传的文件，或上传的文件过大。',
        'invalidfiles' => '一个或多个文件过大或者属于不被允许的文件类型。允许上传的文件类型有PNG，GIF，JPG，DOC，DOCX，PDF和TXT。',
    ],

    'import' => [
        'import_button' => '流程导入',
        'error' => '某些项目没有正确导入。',
        'errorDetail' => '以下项由于错误未被导入',
        'success' => '您的文件已被导入',
        'file_delete_success' => '您的文件已成功删除',
        'file_delete_error' => '该文件无法被删除',
        'file_missing' => '所选文件丢失',
        'file_already_deleted' => '选择的文件已被删除',
        'file_missing_on_disk' => '本次导入对应的文件已不在磁盘上。该文件可能已在 Snipe-IT 之外被删除。请删除此条目并重新上传该文件后重试。',
        'file_empty' => '此文件没有数据行。没有从它导入。',
        'already_processing' => '此导入正在由另一个用户处理。请等待它完成后再重试。',
        'header_row_missing' => '该文件没有可识别的表头行。请删除此条目并重新上传文件重试。',
        'header_row_has_malformed_characters' => '标题行中的一个或多个属性包含格式错误的 UTF-8 字符',
        'content_row_has_malformed_characters' => '第一行内容中的一个或多个属性包含格式错误的 UTF-8 字符',
        'transliterate_failure' => '从 :encoding 到 UTF-8 翻译失败，因为输入的字符无效',
        'bulk_delete' => [
            'button' => '删除选中的 (:count)',
            'confirm_title' => '删除选中的导入文件？',
            'confirm_body' => '您即将永久删除 :count 个导入文件。此操作无法撤销。',
            'confirm_button' => '刪除',
            'success' => '导入文件已成功删除。|:count 个导入文件已成功删除。',
            'skipped' => '已跳过 :count 个文件，因为您没有删除它们的权限。',
            'select_all' => '选择此页面上的所有文件',
            'select_row' => '选择 :file 进行批量删除',
        ],
        'row_count' => '{0} 此文件中没有数据行|{1} 要导入 :count 行数据|[2,*] 要导入 :count 行数据',
        'summary' => [
            'created' => '创建',
            'updated' => '已更新',
            'skipped' => '跳过重复项',
            'errored' => '已经出错',
            'no_changes' => '导入已完成，但未创建或更新任何内容。每一行均已被跳过，这通常是因为对应记录已存在。请检查下方的计数，如果这不符合您的预期，请调整 CSV 文件或导入类型。',
        ],
        'type_required' => '请在继续之前选择导入类型。',
        'processing' => '正在处理您的导入。在此过程完成之前，请不要关闭页面。',
        'backup_running' => '正在导入前执行备份。文件较大时可能需要一些时间，请稍候。',
        'backup_label' => '导入前备份',
        'backup_complete' => '备份完成',
        'import_label' => '导入',
        'required_fields_missing' => '以下必填字段尚未映射：:fields',
        'history' => [
            'missing_asset_tag_identity' => '(缺少资产标签)',
            'missing_asset_tag_message' => '行已跳过：未提供资产标签。',
            'asset_not_found_message' => '具有此标签的资产不存在。请先导入资产，然后重新运行历史记录导入。',
            'target_not_matched_message' => '没有与 ":name" 匹配的 :target_type。对于用户，请在步骤 1 中切换匹配选项或先创建该用户。对于位置，请确保 CSV 中的位置名称与现有位置完全匹配。',
            'invalid_target_type_message' => '目标类型 ":value" 无法识别。请使用 "user" 或 "location"，或将该列留空以默认设为 user。',
        ],
        'wizard' => [
            'step_type' => '选择类型',
            'step_map' => '映射字段',
            'step_preview' => '预览',
            'back' => '后退',
            'next' => '下一页',
            'preview_button' => '预览',
            'process' => '处理导入',
            'preview_intro' => '正在预览应用映射后的前 :count 行。如果在导入前需要修改映射属性，请使用“后退”按钮。',
        ],
    ],

    'delete' => [
        'confirm' => '你确定要删除这个资产吗？',
        'error' => '删除资产的过程中出现了一点儿问题，请重试。',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag 目前已签出。在删除此设备之前请先归还。 [2,*] Asset Tags: :asset_tag 目前已签出。在删除此设备之前请先归还。',
        'nothing_updated' => '没有选择任何资产，所以没有删除任何资产。',
        'success' => '资产成功被删除。',
    ],

    'checkout' => [
        'error' => '资产未被签出，请重试',
        'success' => '资产签出成功。',
        'user_does_not_exist' => '无效用户，请重试。',
        'not_available' => '此资产无法签出！',
        'no_assets_selected' => '您必须在这个列表中选择至少一项资产',
    ],

    'multi-checkout' => [
        'error' => '资产未签出，请重试 |资产未签出，请重试',
        'success' => '资产签出成功。 |资产签出成功。',
    ],

    'multi-checkin' => [
        'error' => '资产未签入，请重试|资产未签入，请重试',
        'success' => '资产已成功签入。|资产已成功签入。',
        'no_assets_selected' => '您必须在这个列表中选择至少一项资产',
    ],

    'multi-audit' => [
        'success' => ':count 项资产已成功盘点。|:count 项资产已成功盘点。',
        'partial_error' => '已盘点 :success 项资产，失败 :failed 项。请检查下方错误并重试。|已盘点 :success 项资产，失败 :failed 项。请检查下方错误并重试。',
        'no_assets_selected' => '您必须在这个列表中选择至少一项资产',
    ],

    'checkin' => [
        'error' => '资产还没有归还，请重试。',
        'success' => '资产归还成功。',
        'updated_without_checkin' => '资产已是签入状态，但您设置的表单字段（状态、位置、名称、备注）仍已应用。',
        'user_does_not_exist' => '无效用户，请重试。',
        'already_checked_in' => '该资产已经归还。',
        'force_checkin_orphaned_success' => '无效分配已成功清除。',
        'force_checkin_not_orphaned' => '该项目未处于无效分配状态。',
        'force_checkin_error' => '无法清除无效分配。',

    ],

    'requests' => [
        'error' => '申请失败，请重试。',
        'success' => '申请提交成功。',
        'canceled' => '申请已取消。',
        'cancel' => '取消申请此物品',
        'duplicate' => '您已对该物品提交了有效的申请。',
        'no_active' => '您没有可针对此物品取消的有效申请。',
        'insufficient_stock' => '现有库存不足以满足此申请。请先补货。',
        'confirm_cancel_by_admin' => "是否取消 :user 对 :item 的申请？",
        'no_selection' => '没有选择要完成的行。',
        'row_stale' => '申请编号 #:id 不再处于待处理状态，已跳过。',
        'row_qty_invalid' => '申请编号 #:id 的数量无效，已跳过。',
        'row_user_missing' => '申请编号 #:id 没有有效的目标用户，已跳过。',
        'row_asset_missing' => '申请编号 #:id 没有有效的目标资产，已跳过。',
        'row_asset_not_requesters' => '申请编号 #:id 已跳过：所选资产不属于申请人。',
        'row_asset_not_available' => '申请编号 #:id 已跳过：所选资产不是该型号的可用单元。',
        'row_asset_taken' => '申请编号 #:id 已跳过：所选资产在提交前已被分配给其他人。',
        'row_company_mismatch' => '申请编号 #:id 已跳过：:user 无法接收来自该公司的物品。',
        'row_over_allocated' => '申请编号 #:id 已跳过：提交时现有库存不足。',
        'no_target_assets_for_user' => ':user 没有可安装到的资产。',
        'no_available_units' => '该型号没有可供分发的可用单元。',
        'bulk_summary' => '已满足 :total 个申请中的 :fulfilled 个。',
        'bulk_fulfill_notification_intro' => '履行时，以下设置将应用于每个勾选的申请：',
    ],

];
