<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    | The following language lines are used in the user permissions system.
    | Each permission has a 'name' and a 'note' that describes
    | the permission in detail.
    |
    | DO NOT edit the keys (left-hand side) of each permission as these are
    | used throughout the system for translations.
    |---------------------------------------------------------------------------
    */

    "superuser" => [
        'name' => '超级用户',
        'note'       => '决定该用户是否拥有完整的管理访问权限。此设置将覆盖全系统中所有其他特定或受限的权限。 ',
    ],
    'admin' => [
        'name' => '管理权限',
        'note'       => '决定用户是否拥有除系统管理设置外的系统大部分访问权限。此类用户可管理用户、位置、类别等，但若启用了完整多公司支持，他们将受其限制。',
    ],

    'import' => [
        'name' => 'CSV 导入',
        'note'       => '这将允许用户执行导入操作，即使他们在其他地方被拒绝了对用户、资产等的访问权限。',
    ],

    'reports' => [
        'name' => '报告访问',
        'note'       => '确定用户是否有权访问应用程序的报表部分。',
    ],

    'assets' =>
        [
            'name' => '资产',
            'note' => '授予访问应用资产部分的权限。',
    ],

    'assetsview' => [
        'name' => '查看资产',
    ],

    'assetscreate' => [
        'name' => '新增资产',
    ],

    'assetsedit' => [
        'name' => '编辑资产',
    ],

    'assetsdelete' => [
        'name' => '删除资产',
    ],

    'assetscheckin' => [
        'name' => '签入',
        'note' => '将当前已签出的资产归还至库存。',
    ],

    'assetscheckout' => [
        'name' => '签出',
        'note' => '办理资产签出，以完成库存分配。',
    ],

    'assetsaudit' => [
        'name' => '盘点资产',
        'note' => '允许用户将资产标记为实盘。',
    ],

    'assetsviewrequestable' => [
        'name' => '查看可请求资源',
        'note' => '允许用户查看被标记为可请求的资产。',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => '查看加密的自定义字段',
        'note' => '允许用户查看和修改资产上的加密自定义字段。',
    ],

    'accessories'   => [
        'name' => '配件',
        'note'       => '授予访问应用配件模块的权限。',
    ],

    'accessoriesview' => [
        'name' => '查看配件',
    ],
    'accessoriescreate' => [
        'name' => '新增附件',
    ],
    'accessoriesedit' => [
        'name' => '编辑配件',
    ],
    'accessoriesdelete' => [
        'name' => '删除配件',
    ],
    'accessoriescheckout' => [
        'name' => '签出配件',
        'note' => '通过签出分配在库存中的配件。',
    ],
    'accessoriescheckin' => [
        'name' => '归还配件',
        'note' => '将当前已签出的配件归还至库存。',
    ],
    'accessoriesfiles' => [
        'name' => '管理配件文件',
        'note' => '允许用户上传、下载和删除与配件相关的文件。',
    ],
    'consumables'   => [
        'name' => '耗材',
        'note'       => '授予访问应用消耗品部分的权限。',
    ],
    'consumablesview' => [
        'name' => '查看耗材',
    ],
    'consumablescreate' => [
        'name' => '新增消耗品',
    ],
    'consumablesedit' => [
        'name' => '编辑耗材',
    ],
    'consumablesdelete' => [
        'name' => '删除耗材',
    ],
    'consumablescheckout' => [
        'name' => '签出耗材',
        'note' => '通过签出流程分配库存中的消耗品。',
    ],
    'consumablesfiles' => [
        'name' => '管理耗材文件',
        'note' => '允许用户上传、下载和删除与消耗品相关的文件。',
    ],
    'licenses'   => [
        'name' => '许可证',
        'note'       => '授予访问应用许可证部分的权限。',
    ],
    'licensesview' => [
        'name' => '查看许可证',
    ],
    'licensescreate' => [
        'name' => '新增许可证',
    ],
    'licensesedit' => [
        'name' => '编辑许可证',
    ],
    'licensesdelete' => [
        'name' => '删除许可证',
    ],
    'licensescheckout' => [
        'name' => '分配许可证',
        'note' => '允许用户给资产或用户分配许可证。',
        ],
    'licensescheckin' => [
        'name' => '取消分配许可证',
        'note' => '允许用户取消分配给资产或用户的许可证。',
    ],
    'licensesfiles' => [
        'name' => '管理许可文件',
        'note' => '允许用户上传、下载和删除与许可证相关的文件。',
    ],
    'licenseskeys' => [
        'name' => '管理许可证密钥',
        'note' => '允许用户查看与许可证相关联的产品密钥。',
    ],
    'components'   => [
        'name' => '组件',
        'note'       => '授予访问应用组件部分的权限。',
    ],
    'componentsview' => [
        'name' => '查看组件',
    ],
    'componentscreate' => [
        'name' => '新增组件',
    ],
    'componentsedit' => [
        'name' => '编辑组件',
    ],
    'componentsdelete' => [
        'name' => '删除组件',
    ],
    'componentsfiles' => [
        'name' => '管理组件文件',
        'note' => '允许用户上传、下载和删除与组件相关的文件。',
    ],
    'componentscheckout' => [
        'name' => '签出组件',
        'note' => '通过签出流程分配库存中的组件。',
    ],
    'componentscheckin' => [
        'name' => '归还组件',
        'note' => '将当前已签出的组件归还至库存。',
    ],
    'kits'   => [
        'name' => '预定义的 Kits',
        'note'       => '授予访问应用预定义工具部分的权限。',
    ],
    'kitsview' => [
        'name' => '查看预定义套件',
    ],
    'kitscreate' => [
        'name' => '新增预定义套件',
    ],
    'kitsedit' => [
        'name' => '编辑预定义套件',
    ],
    'kitsdelete' => [
        'name' => '删除预定义套件',
    ],
    'users'   => [
        'name' => '用户',
        'note'       => '授予应用用户部分的访问权限。',
    ],
    'usersview' => [
        'name' => '查看用户',
    ],
    'userscreate' => [
        'name' => '创建新用户',
    ],
    'usersedit' => [
        'name' => '编辑用户',
    ],
    'usersdelete' => [
        'name' => '删除用户',
    ],
    'models'   => [
        'name' => '资产型号',
        'note'       => '授予访问应用资产型号部分的权限。',
    ],
    'modelsview' => [
        'name' => '查看型号',
    ],

    'modelscreate' => [
        'name' => '新增资产型号',
    ],
    'modelsedit' => [
        'name' => '编辑资产型号',
    ],
    'modelsdelete' => [
        'name' => '删除资产型号',
    ],
    'categories'   => [
        'name' => '分类',
        'note'       => '授予访问分类资产部分的权限。',
    ],
    'categoriesview' => [
        'name' => '查看分类',
    ],
    'categoriescreate' => [
        'name' => '新增分类',
    ],
    'categoriesedit' => [
        'name' => '编辑分类',
    ],
    'categoriesdelete' => [
        'name' => '删除分类',
    ],
    'departments'   => [
        'name' => '部门',
        'note'       => '授予访问应用部门部分的权限。',
    ],
    'departmentsview' => [
        'name' => '查看部门',
    ],
    'departmentscreate' => [
        'name' => '新增部门',
    ],
    'departmentsedit' => [
        'name' => '编辑部门',
    ],
    'departmentsdelete' => [
        'name' => '删除部门',
    ],
    'locations'   => [
        'name' => '地理位置',
        'note'       => '授予访问应用位置部分的权限。',
    ],
    'locationsview' => [
        'name' => '查看位置',
    ],
    'locationscreate' => [
        'name' => '新增位置',
    ],
    'locationsedit' => [
        'name' => '编辑位置',
    ],
    'locationsdelete' => [
        'name' => '删除位置',
    ],
    'status-labels'   => [
        'name' => '状态标签',
        'note'       => '授予访问应用状态标签部分的权限。',
    ],
    'statuslabelsview' => [
        'name' => '查看状态标签',
    ],
    'statuslabelscreate' => [
        'name' => '创建新状态标签',
    ],
    'statuslabelsedit' => [
        'name' => '编辑状态标签',
    ],
    'statuslabelsdelete' => [
        'name' => '删除状态标签',
    ],
    'custom-fields'   => [
        'name' => '自定义字段',
        'note'       => '授予访问应用自定义字段部分的权限。',
    ],
    'customfieldsview' => [
        'name' => '查看自定义字段',
    ],
    'customfieldscreate' => [
        'name' => '创建新自定义字段',
    ],
    'customfieldsedit' => [
        'name' => '编辑自定义字段',
    ],
    'customfieldsdelete' => [
        'name' => '删除自定义字段',
    ],
    'suppliers'   => [
        'name' => '供应商',
        'note'       => '授予访问应用供应商部分的权限。',
    ],
    'suppliersview' => [
        'name' => '查看供应商',
    ],
    'supplierscreate' => [
        'name' => '新增供应商',
    ],
    'suppliersedit' => [
        'name' => '编辑供应商',
    ],
    'suppliersdelete' => [
        'name' => '删除供应商',
    ],
    'manufacturers'   => [
        'name' => '制造商',
        'note'       => '授予访问应用制造商部分的权限。',
    ],
    'manufacturersview' => [
        'name' => '查看制造商',
    ],
    'manufacturerscreate' => [
        'name' => '创建新的制造商',
    ],
    'manufacturersedit' => [
        'name' => '编辑制造商',
    ],
    'manufacturersdelete' => [
        'name' => '删除制造商',
    ],
    'companies'   => [
        'name' => '公司',
        'note'       => '授予访问应用公司部分的权限。',
    ],
    'companiesview' => [
        'name' => '查看公司',
    ],
    'companiescreate' => [
        'name' => '创建新公司',
    ],
    'companiesedit' => [
        'name' => '编辑公司',
    ],
    'companiesdelete' => [
        'name' => '删除公司',
    ],
    'user-self-accounts' => [
        'name' => '用户自己账户',
        'note'       => '授予非管理员用户管理自己用户帐户某些方面的能力。',
    ],
    'selftwo-factor' => [
        'name' => '管理两步验证',
        'note'       => '允许用户为自己的账户启用、禁用和管理双重身份验证。',
    ],
    'selfapi' => [
        'name' => '管理 API 令牌',
        'note'       => '允许用户创建、查看和撤销其自身的API令牌。用户令牌将拥有与创建者相同的权限。',
    ],
    'selfedit-location' => [
        'name' => '编辑位置',
        'note'       => '允许用户编辑与其个人账户关联的位置信息。',
    ],
    'selfcheckout-assets' => [
        'name' => '自我签出资产',
        'note'       => '允许用户在没有管理员干预的情况下查看资产。',
    ],
    'selfview-purchase-cost' => [
        'name' => '查看购买成本',
        'note'       => '允许用户在其账户视图中查看物品购买成本。',
    ],

    'depreciations' => [
        'name' => '折旧管理',
        'note'       => '允许用户管理和查看资产折旧详情。',
    ],
    'depreciationsview' => [
        'name' => '查看详细折旧信息',
    ],
    'depreciationsedit' => [
        'name' => '编辑折旧设置',
    ],
    'depreciationsdelete' => [
        'name' => '删除折旧记录',
    ],
    'depreciationscreate' => [
        'name' => '新增折旧记录',
    ],

    'grant_all' => '为 :area 授予所有权限',
    'deny_all' => '拒绝 :area 的所有权限',
    'inherit_all' => '继承来自权限组的 :area 的所有权限',
    'grant' => '授予:area 权限',
    'deny' => '拒绝 :area 的权限',
    'inherit' => '继承来自权限组的 :area 的权限',
    'use_groups' => '为简化管理，我们强烈建议使用权限组进行权限分配，而非逐一设置单个权限。'

);
