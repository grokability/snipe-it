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
        'note'       => 'Determines whether the user has full access to all aspects of the admin. This setting overrides ALL more specific and restrictive permissions throughout the system. ',
    ],
    'admin' => [
        'name' => '管理权限',
        'note'       => 'Determines whether the user has access to most aspects of the system EXCEPT the System Admin Settings. These users will be able to manage users, locations, categories, etc, but ARE constrained by Full Multiple Company Support if it is enabled.',
    ],

    'import' => [
        'name' => 'CSV 导入',
        'note'       => 'This will allow users to import even if access to users, assets, etc is denied elsewhere.',
    ],

    'reports' => [
        'name' => '报告访问',
        'note'       => 'Determines whether the user has access to the Reports section of the application.',
    ],

    'assets' =>
        [
            'name' => '资产',
            'note' => '授予访问应用程序资产部分的权限。',
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
        'note' => 'Check assets back into inventory that are currently checked out.',
    ],

    'assetscheckout' => [
        'name' => '签出',
        'note' => '办理资产签出，以完成库存分配。',
    ],

    'assetsaudit' => [
        'name' => '审计资产',
        'note' => '允许用户将资产标记为实盘。',
    ],

    'assetsviewrequestable' => [
        'name' => '查看可请求资源',
        'note' => 'Allows the user to view assets that are marked as requestable.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => '查看加密的自定义字段',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories'   => [
        'name' => '配件',
        'note'       => 'Grants access to the Accessories section of the application.',
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
        'note' => 'Check accessories back into inventory that are currently checked out.',
    ],
    'accessoriesfiles' => [
        'name' => '管理配件文件',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories.',
    ],
    'consumables'   => [
        'name' => '耗材',
        'note'       => 'Grants access to the Consumables section of the application.',
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
        'note' => 'Assign consumables in inventory by checking them out.',
    ],
    'consumablesfiles' => [
        'name' => '管理耗材文件',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables.',
    ],
    'licenses'   => [
        'name' => '许可证',
        'note'       => 'Grants access to the Licenses section of the application.',
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
        'note'       => '授予访问应用程序组件部分的权限。',
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
        'note' => 'Allows the user to upload, download, and delete files associated with components.',
    ],
    'componentscheckout' => [
        'name' => '签出组件',
        'note' => 'Assign components in inventory by checking them out.',
    ],
    'componentscheckin' => [
        'name' => '归还组件',
        'note' => 'Check components back into inventory that are currently checked out.',
    ],
    'kits'   => [
        'name' => '预定义的 Kits',
        'note'       => 'Grants access to the Predefined Kits section of the application.',
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
        'note'       => '授予应用程序用户部分的访问权限。',
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
        'note'       => '授予访问应用程序资产型号部分的权限。',
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
        'note'       => 'Grants access to the Categories section of the application.',
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
        'note'       => 'Grants access to the Departments section of the application.',
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
        'note'       => 'Grants access to the Locations section of the application.',
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
        'note'       => 'Grants access to the Status Labels section of the application used by Assets.',
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
        'note'       => 'Grants access to the Custom Fields section of the application used by Assets.',
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
        'name' => 'Delete Custom Fields',
    ],
    'suppliers'   => [
        'name' => '供应商',
        'note'       => 'Grants access to the Suppliers section of the application.',
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
        'note'       => 'Grants access to the Manufacturers section of the application.',
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
        'note'       => 'Grants access to the Companies section of the application.',
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
        'note'       => 'Allows users to enable, disable, and manage two-factor authentication for their own accounts.',
    ],
    'selfapi' => [
        'name' => '管理 API 令牌',
        'note'       => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => '编辑位置',
        'note'       => 'Allows users to edit the location associated with their own user account.',
    ],
    'selfcheckout-assets' => [
        'name' => '自我签出资产',
        'note'       => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => '查看购买成本',
        'note'       => 'Allows users to view the purchase cost of items in their account view.',
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
    'inherit_all' => 'Inherit all permissions for :area from permission groups',
    'grant' => '授予:area 权限',
    'deny' => '拒绝 :area 的权限',
    'inherit' => 'Inherit Permission for :area from permission groups',
    'use_groups' => 'We strongly suggest using Permission Groups instead of assigning individual permissions for easier management.'

);
