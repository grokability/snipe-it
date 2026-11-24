<?php

return [

    // -----------------------------------------------------------------
    // Generic errors
    // -----------------------------------------------------------------
    'not_authenticated' => '未通过身份验证',
    'unauthorized' => '未授权',
    'scope_denied' => '此令牌没有此操作所需的 :scope 范围',
    'scope_handshake_required' => '此令牌不携带任何 MCP 范围。请重新授权连接器，并在调用 MCP 工具前至少授予一个 mcp:* 范围。',
    'scope_session_description' => '打开 MCP 会话并发现可用工具。不授予调用任何工具的权限。调用工具需要能力范围（read、write 等）。',
    'scope_read_description' => '查看资产、用户、许可证和其他库存数据',
    'scope_write_description' => '创建或编辑库存项目及其属性',
    'scope_delete_description' => '软删除项目并恢复已删除的项目',
    'scope_checkout_description' => '将资产、配件、组件、消耗品和许可证签出给用户、位置或资产',
    'scope_checkin_description' => '签入资产、配件、组件和许可证',
    'scope_superadmin_description' => '管理权限组。创建/更新/删除组以及查看组列表时需要。',
    'id_or_name_required' => '需要提供 id 或 name',
    'id_username_or_email_required' => '请提供 id、用户名或电子邮件',
    'provide_user_or_asset' => '请提供 assigned_to（用户 ID）或 asset_id',

    // -----------------------------------------------------------------
    // "Not found" errors
    // -----------------------------------------------------------------
    'object_not_found' => '未找到指定的 :type',
    'asset_not_found' => '未找到资产',
    'user_not_found' => '未找到用户',
    'accessory_not_found' => '未找到配件',
    'accessory_checkout_not_found' => '未找到配件签出记录',
    'component_not_found' => '未找到组件',
    'component_checkout_not_found' => '未找到组件签出记录',
    'consumable_not_found' => '未找到消耗品',
    'license_not_found' => '找不到许可证',
    'license_seat_not_found' => '未找到许可证席位',
    'department_not_found' => '未找到部门',
    'company_not_found' => '未找到公司',
    'category_not_found' => '未找到分类',
    'manufacturer_not_found' => '未找到制造商',
    'supplier_not_found' => '未找到供应商',
    'status_label_not_found' => '未找到状态标签',
    'location_not_found' => '未找到位置',
    'asset_model_not_found' => '未找到资产型号',
    'depreciation_not_found' => '未找到折旧',
    'group_not_found' => '未找到分组',
    'checkout_target_not_found' => '未找到指定的 :type',

    // -----------------------------------------------------------------
    // Business rule errors
    // -----------------------------------------------------------------
    'asset_not_available' => '资产 :asset_tag 不可签出',
    'asset_not_checked_out' => '资产 :asset_tag 当前未签出',
    'asset_not_deleted' => '资产未被删除',
    'user_not_deleted' => '用户未被删除',
    'user_cannot_delete_self' => '不能删除自己的账户',
    'user_has_items' => '用户有已分配项目，无法删除。请先签入所有项目。',
    'cannot_edit_auth_fields' => '您没有权限编辑此用户的身份验证字段（用户名、电子邮件、密码、激活状态）',
    'username_taken' => '用户名 :username 已被占用。请选择其他用户名。',
    'password_reset_sent' => '密码重置链接已发送至 :email',
    'password_reset_user_inactive' => '用户 :username 未激活，无法接收密码重置电子邮件',
    'password_reset_no_email' => '用户 :username 没有登记电子邮件地址',
    'password_reset_ldap_user' => '用户 :username 由 LDAP 管理，无法使用密码重置',
    'password_reset_send_failed' => '发送密码重置电子邮件失败：:error',
    'invalid_permissions_format' => '权限必须是有效的 JSON 对象',
    'invalid_permission_key' => '无效的权限键：:key',
    'invalid_permission_value' => ':key 的权限值必须为 1（授予）或 -1（拒绝）',
    'superadmin_required_for_groups' => '只有超级管理员可以分配权限组',
    'no_available_seats' => '此许可证没有可用席位',
    'no_free_seat' => '此许可证没有找到空闲席位',
    'seat_not_checked_out' => '此席位当前未签出',
    'no_units_available' => '此配件没有可签出的单位',
    'no_units_remaining' => '没有剩余单位',
    'accessory_has_checkouts' => '配件有已签出的单位，无法删除。请先签入。',
    'component_has_checkouts' => '组件有已签出的单位，无法删除。请先签入。',
    'consumable_has_checkouts' => '消耗品有已签出的项目，无法删除',
    'license_has_seats_assigned' => '许可证有已分配的席位，无法删除。请先签入所有席位。',
    'department_has_users' => '部门有已分配的用户，无法删除。请先重新分配所有用户。',
    'location_has_child_locations' => '位置有子位置，无法删除',
    'location_has_users' => '位置有已分配的用户，无法删除',
    'status_label_has_assets' => '状态标签有已分配的资产，无法删除',
    'model_has_assets' => '资产型号有资产，无法删除',
    'bulk_confirm_count_required' => '按筛选条件删除时需要 confirm_count。请先使用 dry_run=true 预览批次，然后使用确切的 total_matched 值作为 confirm_count 重新调用。',
    'bulk_confirm_count_mismatch' => 'confirm_count :expected 与实际行数 :actual 不匹配。筛选结果可能已发生变化。请使用 dry_run=true 重新运行并重试。',
    'bulk_batch_has_undeletable' => '批次包含 :count 行无法删除的数据（已签出、有子项等）。传入 skip_undeletable=true 以丢弃它们并删除其余项，或移除阻碍项后重试。',
    'bulk_dry_run_summary' => '预览：匹配 :total 行，提交时将删除 :deletable 行。未做任何更改。',
    'bulk_delete_completed' => '批量删除完成。已移除 :count 行。',
    'bulk_delete_failed' => '批量删除失败并已回滚。原因：:failures',

    // -----------------------------------------------------------------
    // Operation failures
    // -----------------------------------------------------------------
    'create_failed' => '创建失败：:error',
    'update_failed' => '更新失败：:error',
    'update_no_fields' => 'No updatable fields were supplied',
    'delete_failed' => '删除失败',
    'delete_failed_error' => '删除失败：:error',
    'checkin_failed' => '签入失败',
    'checkin_failed_error' => '签入失败：:error',
    'checkout_failed' => '签出失败',
    'audit_failed' => '盘点失败：:error',
    'note_save_failed' => '保存备注失败',

    // -----------------------------------------------------------------
    // Asset messages
    // -----------------------------------------------------------------
    'asset_found' => '找到资产 :asset_tag',
    'asset_created' => '资产 :asset_tag 创建成功',
    'asset_updated' => '资产 :asset_tag 更新成功',
    'asset_deleted' => '资产 :asset_tag 删除成功',
    'asset_restored' => '资产 :asset_tag 恢复成功',
    'asset_checked_out' => '资产 :asset_tag 签出成功',
    'asset_checked_in' => '资产 :asset_tag 签入成功',
    'asset_audited' => '已记录资产 :asset_tag 的盘点',
    'note_added_to_asset' => '已向资产 :asset_tag 添加备注',
    'note_added_successfully' => '备注添加成功',

    // -----------------------------------------------------------------
    // User messages
    // -----------------------------------------------------------------
    'current_user' => '当前用户：:username',
    'user_found' => '找到用户 :username',
    'user_created' => '用户 :username 创建成功',
    'user_updated' => '用户 :username 更新成功',
    'user_deleted' => '用户 :username 删除成功',
    'user_restored' => '用户 :username 恢复成功',
    'two_factor_reset' => '已重置 :username 的两步验证',
    'user_assets_found' => '为用户 :username 找到 :count 个资产',
    'user_permissions_found' => '用户 :username 有 :count 项有效权限',
    'profile_updated' => '个人资料更新成功',

    // -----------------------------------------------------------------
    // Accessory messages
    // -----------------------------------------------------------------
    'accessory_created' => '配件 :name 创建成功',
    'accessory_updated' => '配件 :name 更新成功',
    'accessory_deleted' => '配件 :name 删除成功',
    'accessory_checked_out' => '配件 :name 签出成功',
    'accessory_checked_in' => '配件 :name 签入成功',

    // -----------------------------------------------------------------
    // Component messages
    // -----------------------------------------------------------------
    'component_created' => '组件 :name 创建成功',
    'component_updated' => '组件 :name 更新成功',
    'component_deleted' => '组件 :name 删除成功',
    'component_checked_out' => '组件 :name 已签出给资产 :asset_tag',
    'component_checked_in' => '组件 :name 签入成功',

    // -----------------------------------------------------------------
    // Consumable messages
    // -----------------------------------------------------------------
    'consumable_found' => '找到消耗品 :name',
    'consumable_created' => '消耗品 :name 创建成功',
    'consumable_updated' => '消耗品 :name 更新成功',
    'consumable_deleted' => '消耗品 :name 删除成功',
    'consumable_checked_out' => '消耗品 :name 已签出给 :username',

    // -----------------------------------------------------------------
    // License messages
    // -----------------------------------------------------------------
    'license_found' => '找到许可证 :name',
    'license_created' => '许可证 :name 创建成功',
    'license_updated' => '许可证 :name 更新成功',
    'license_deleted' => '许可证 :name 删除成功',
    'license_seat_checked_out_user' => '许可证席位已签出给用户 :username',
    'license_seat_checked_out_asset' => '许可证席位已签出给资产 :asset_tag',
    'license_seat_checked_in' => '许可证席位 :id 签入成功',

    // -----------------------------------------------------------------
    // Department messages
    // -----------------------------------------------------------------
    'department_created' => '部门 :name 创建成功',
    'department_updated' => '部门 :name 更新成功',
    'department_deleted' => '部门 :name 删除成功',

    // -----------------------------------------------------------------
    // Company messages
    // -----------------------------------------------------------------
    'company_found' => '找到公司 :name',
    'company_created' => '公司 :name 创建成功',
    'company_updated' => '公司 :name 更新成功',
    'company_deleted' => '公司 :name 删除成功',

    // -----------------------------------------------------------------
    // Category messages
    // -----------------------------------------------------------------
    'category_found' => '找到分类 :name',
    'category_created' => '分类 :name 创建成功',
    'category_updated' => '分类 :name 更新成功',
    'category_deleted' => '分类 :name 删除成功',
    'category_delete_failed' => '无法删除分类：:error',

    // -----------------------------------------------------------------
    // Manufacturer messages
    // -----------------------------------------------------------------
    'manufacturer_found' => '找到制造商 :name',
    'manufacturer_created' => '制造商 :name 创建成功',
    'manufacturer_updated' => '制造商 :name 更新成功',
    'manufacturer_deleted' => '制造商 :name 删除成功',

    // -----------------------------------------------------------------
    // Supplier messages
    // -----------------------------------------------------------------
    'supplier_found' => '找到供应商 :name',
    'supplier_created' => '供应商 :name 创建成功',
    'supplier_updated' => '供应商 :name 更新成功',
    'supplier_deleted' => '供应商 :name 删除成功',

    // -----------------------------------------------------------------
    // Status label messages
    // -----------------------------------------------------------------
    'status_label_found' => '找到状态标签 :name',
    'status_label_created' => '状态标签 :name 创建成功',
    'status_label_updated' => '状态标签 :name 更新成功',
    'status_label_deleted' => '状态标签 :name 删除成功',

    // -----------------------------------------------------------------
    // Location messages
    // -----------------------------------------------------------------
    'location_found' => '找到位置 :name',
    'location_created' => '位置 :name 创建成功',
    'location_updated' => '位置 :name 更新成功',
    'location_deleted' => '位置 :name 删除成功',

    // -----------------------------------------------------------------
    // Asset model messages
    // -----------------------------------------------------------------
    'asset_model_found' => '找到资产型号 :name',
    'asset_model_created' => '资产型号 :name 创建成功',
    'asset_model_updated' => '资产型号 :name 更新成功',
    'asset_model_deleted' => '资产型号 :name 删除成功',

    // -----------------------------------------------------------------
    // Depreciation messages
    // -----------------------------------------------------------------
    'depreciation_found' => '找到折旧 :name',
    'depreciation_created' => '折旧 :name 创建成功',
    'depreciation_updated' => '折旧 :name 更新成功',
    'depreciation_deleted' => '折旧 :name 删除成功',

    // -----------------------------------------------------------------
    // Group messages
    // -----------------------------------------------------------------
    'group_found' => '找到分组 :name',
    'group_created' => '分组 :name 创建成功',
    'group_updated' => '分组 :name 更新成功',
    'group_deleted' => '分组 :name 删除成功',

    // -----------------------------------------------------------------
    // Maintenance messages
    // -----------------------------------------------------------------
    'maintenance_created' => '维护 :name 创建成功',
    'maintenance_already_completed' => 'Maintenance :name was already completed on :completed_at',

    // -----------------------------------------------------------------
    // List messages
    // -----------------------------------------------------------------
    'list_assets' => '找到 :total 个资产，返回 :count 个',
    'list_users' => '找到 :total 个用户，返回 :count 个',
    'list_consumables' => '找到 :total 个消耗品，返回 :count 个',
    'list_licenses' => '找到 :total 个许可证，返回 :count 个',
    'list_companies' => '找到 :total 个公司，返回 :count 个',
    'list_categories' => '找到 :total 个分类，返回 :count 个',
    'list_manufacturers' => '找到 :total 个制造商，返回 :count 个',
    'list_suppliers' => '找到 :total 个供应商，返回 :count 个',
    'list_status_labels' => '找到 :total 个状态标签，返回 :count 个',
    'list_locations' => '找到 :total 个位置，返回 :count 个',
    'list_asset_models' => '找到 :total 个资产型号，返回 :count 个',
    'list_depreciations' => '找到 :total 个折旧，返回 :count 个',
    'list_groups' => '找到 :total 个分组，返回 :count 个',
    'list_maintenances' => '找到 :total 条维护记录，返回 :count 条',
    'list_activity' => '找到 :total 条活动日志条目，返回 :count 条',
    'list_accessories' => '找到 :total 个配件，返回 :count 个',
    'list_maintenance_types' => '找到 :total 个维护类型，返回 :count 个',
    'maintenance_type_created' => '维护类型 :name 已创建。',
    'find_similar_models_identifier_required' => '请提供 model_id 或 model_name 以标识源资产型号。',
    'find_similar_models_summary' => '已对 :total 个与 :source 相似的候选项评分，返回前 :count 个。',

    // -----------------------------------------------------------------
    // Upload tools
    // -----------------------------------------------------------------
    'id_or_asset_tag_required' => '需要 id 或 asset_tag 来标识资产。',
    'upload_image_decode_failed' => '无法解码提供的 image_base64。请确认该字符串是有效的 base64。',
    'upload_image_tempfile_failed' => '无法将上传的图片暂存为服务器上的临时文件。',
    'upload_image_save_failed' => '附加图片失败。原因：:error',
    'upload_image_success' => '图片已附加到资产 :asset_tag。',
    'list_asset_notes' => '找到资产 :asset_tag 的 :total 条备注，返回 :count 条',
    'list_uploads' => '找到 :type 的 :total 个已上传文件，返回 :count 个',
    'list_history' => '找到 :type 的 :total 条历史记录，返回 :count 条',

    // -----------------------------------------------------------------
    // Offboard user tool
    // -----------------------------------------------------------------
    'offboard_identifier_required' => '请提供用户名，或名字加姓氏，以标识要办理离职的用户。',
    'offboard_ambiguous_user' => '有多个用户与提供的姓名匹配。请向管理员确认要办理离职的用户，并使用其用户名重新调用此工具。',
    'offboard_dry_run_message' => '仅预览。尚未做任何更改。请使用 dry_run=false 重新调用此工具以执行。',
    'offboard_dry_run_summary' => ':username 的离职预览：将释放 :assets 个资产、:accessories 个配件、:seats 个许可证席位。',
    'offboard_completed_message' => ':username 的离职办理已完成。用户账户已被软删除。',
    'offboard_completed_summary' => '已为 :username 完成离职办理：释放了 :assets 个资产、:accessories 个配件、:seats 个许可证席位，用户已软删除。',
    'offboard_failed' => '离职办理失败并已回滚。原因：:failures',
    'offboard_cross_company_blocked' => '无法为 :username 办理离职，因为该用户属于您无权访问的一个或多个公司。必须由超级管理员为该用户办理离职，以避免跨公司分配成为孤立项。',
    'fk_not_found' => '未找到匹配 :field = ":value" 的记录。请传入正确的名称，或使用 :field 的 _id 变体并传入数字 ID。',
    'fk_ambiguous' => '有多条记录匹配 :field = ":value"。请传入数字 :id_field 以消除歧义。',

    // -----------------------------------------------------------------
    // Transfer user assets tool
    // -----------------------------------------------------------------
    'transfer_identifier_required' => '请为 :side 用户提供用户名，或名字加姓氏。',
    'transfer_user_not_found' => '没有用户匹配为 :side 用户提供的标识。',
    'transfer_ambiguous_user' => '有多个用户匹配 :side 标识。请向管理员确认要使用的用户，并使用其用户名重新调用此工具。',
    'transfer_same_user' => '源用户和目标用户必须不同。',
    'transfer_dry_run_message' => '仅预览。尚未做任何更改。请使用 dry_run=false 重新调用此工具以执行。',
    'transfer_dry_run_summary' => ':from 到 :to 的转移预览：将移动 :assets 个资产、:accessories 个配件、:seats 个许可证席位。',
    'transfer_completed_message' => '已从 :from 转移到 :to。',
    'transfer_completed_summary' => '已从 :from 转移到 :to：移动了 :assets 个资产、:accessories 个配件、:seats 个许可证席位。',
    'transfer_failed' => '转移失败并已回滚。原因：:failures',

];
