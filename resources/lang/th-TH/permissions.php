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
        'name' => 'Super User',
        'note'       => 'กำหนดว่าผู้ใช้มีสิทธิ์เข้าถึงทุกส่วนของระบบผู้ดูแลระบบหรือไม่ การตั้งค่านี้จะแทนที่สิทธิ์ที่เฉพาะเจาะจงและจำกัดกว่าทั้งหมดในระบบ ',
    ],
    'admin' => [
        'name' => 'เข้าถึงส่วนจัดการระบบ',
        'note'       => 'กำหนดว่าผู้ใช้รายใดสามารถเข้าถึงส่วนต่างๆ ของระบบได้เกือบทั้งหมด ยกเว้นการตั้งค่าผู้ดูแลระบบ ผู้ใช้เหล่านี้จะสามารถจัดการผู้ใช้ สถานที่ หมวดหมู่ ฯลฯ ได้ แต่จะถูกจำกัดด้วยการสนับสนุนหลายบริษัทแบบเต็มรูปแบบ หากเปิดใช้งานไว้',
    ],

    'import' => [
        'name' => 'นำเข้าไฟล์ CSV',
        'note'       => 'สิ่งนี้จะช่วยให้ผู้ใช้สามารถนำเข้าข้อมูลได้ แม้ว่าการเข้าถึงผู้ใช้ ทรัพย์สิน ฯลฯ จะถูกปฏิเสธในส่วนอื่นๆ ก็ตาม',
    ],

    'reports' => [
        'name' => 'รายงานการเข้าถึง',
        'note'       => 'ตรวจสอบว่าผู้ใช้มีสิทธิ์เข้าถึงส่วนรายงานของแอปพลิเคชันหรือไม่',
    ],

    'assets' =>
        [
            'name' => 'สินทรัพย์',
            'note' => 'อนุญาตให้เข้าถึงส่วนสินทรัพย์ของแอปพลิเคชันได้',
    ],

    'assetsview' => [
        'name' => 'ดูสินทรัพย์',
    ],

    'assetscreate' => [
        'name' => 'สร้างสินทรัพย์ใหม่',
    ],

    'assetsedit' => [
        'name' => 'แก้ไขสินทรัพย์',
    ],

    'assetsdelete' => [
        'name' => 'ลบสินทรัพย์',
    ],

    'assetscheckin' => [
        'name' => 'ส่งคืน',
        'note' => 'ตรวจสอบว่าสินทรัพย์ที่ส่งกลับเข้าคลังนั้น ได้ดำเนินการเบิกจ่ายแล้ว',
    ],

    'assetscheckout' => [
        'name' => 'เบิกจ่าย',
        'note' => 'บันทึกรายการสินทรัพย์ในคลังสินค้าโดยการเบิกจ่ายสินทรัพย์เหล่านั้น',
    ],

    'assetsaudit' => [
        'name' => 'ตรวจสอบสินทรัพย์',
        'note' => 'อนุญาตให้ผู้ใช้ทำเครื่องหมายสินทรัพย์ว่าได้รับการตรวจสอบสินค้าคงคลังแล้ว',
    ],

    'assetsviewrequestable' => [
        'name' => 'ดูสินทรัพย์ที่สามารถขอได้',
        'note' => 'อนุญาตให้ผู้ใช้ดูสินทรัพย์ที่ถูกทำเครื่องหมายว่าสามารถร้องขอได้',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'ดูฟิลด์กำหนดเองที่เข้ารหัส',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories'   => [
        'name' => 'อุปกรณ์',
        'note'       => 'อนุญาตให้เข้าถึงส่วนอุปกรณ์เสริมของแอปพลิเคชันได้',
    ],

    'accessoriesview' => [
        'name' => 'ดูอุปกรณ์เสริม',
    ],
    'accessoriescreate' => [
        'name' => 'สร้างอุปกรณ์เสริมใหม่',
    ],
    'accessoriesedit' => [
        'name' => 'แก้ไขอุปกรณ์เสริม',
    ],
    'accessoriesdelete' => [
        'name' => 'ลบอุปกรณ์เสริม',
    ],
    'accessoriescheckout' => [
        'name' => 'เบิกจ่ายอุปกรณ์เสริม',
        'note' => 'บันทึกอุปกรณ์เสริมในคลังสินค้าโดยการเบิกจ่าย',
    ],
    'accessoriescheckin' => [
        'name' => 'ส่งคืนอุปกรณ์เสริม',
        'note' => 'ตรวจสอบว่าอุปกรณ์เสริมที่ถูกยืมออกไปแล้ว ได้ดำเนินการเบิกจ่ายแล้ว',
    ],
    'accessoriesfiles' => [
        'name' => 'จัดการไฟล์อุปกรณ์เสริม',
        'note' => 'อนุญาตให้ผู้ใช้สามารถอัปโหลด ดาวน์โหลด และลบไฟล์ที่เกี่ยวข้องกับอุปกรณ์เสริมได้',
    ],
    'consumables'   => [
        'name' => 'การใช้งาน',
        'note'       => 'อนุญาตให้เข้าถึงส่วนวัสดุสิ้นเปลืองของแอปพลิเคชันได้',
    ],
    'consumablesview' => [
        'name' => 'ดูวัสดุสิ้นเปลือง',
    ],
    'consumablescreate' => [
        'name' => 'สร้างวัสดุสิ้นเปลืองใหม่',
    ],
    'consumablesedit' => [
        'name' => 'แก้ไขวัสดุสิ้นเปลือง',
    ],
    'consumablesdelete' => [
        'name' => 'ลบวัสดุสิ้นเปลือง',
    ],
    'consumablescheckout' => [
        'name' => 'เบิกจ่ายวัสดุสิ้นเปลือง',
        'note' => 'บันทึกรายการวัสดุสิ้นเปลืองในคลังสินค้าโดยการเบิกจ่าย',
    ],
    'consumablesfiles' => [
        'name' => 'จัดการไฟล์วัสดุสิ้นเปลือง',
        'note' => 'อนุญาตให้ผู้ใช้สามารถอัปโหลด ดาวน์โหลด และลบไฟล์ที่เกี่ยวข้องกับวัสดุสิ้นเปลืองได้',
    ],
    'licenses'   => [
        'name' => 'ลิขสิทธิ์',
        'note'       => 'Grants access to the Licenses section of the application.',
    ],
    'licensesview' => [
        'name' => 'View Licenses',
    ],
    'licensescreate' => [
        'name' => 'Create New Licenses',
    ],
    'licensesedit' => [
        'name' => 'Edit Licenses',
    ],
    'licensesdelete' => [
        'name' => 'Delete Licenses',
    ],
    'licensescheckout' => [
        'name' => 'Assign Licenses',
        'note' => 'Allows the user to assign licenses to assets or users.',
        ],
    'licensescheckin' => [
        'name' => 'Unassign Licenses',
        'note' => 'Allows the user to unassign licenses from assets or users.',
    ],
    'licensesfiles' => [
        'name' => 'Manage License Files',
        'note' => 'Allows the user to upload, download, and delete files associated with licenses.',
    ],
    'licenseskeys' => [
        'name' => 'Manage License Keys',
        'note' => 'Allows the user to view product keys associated with licenses.',
    ],
    'components'   => [
        'name' => 'ส่วนประกอบ',
        'note'       => 'Grants access to the Components section of the application.',
    ],
    'componentsview' => [
        'name' => 'View Components',
    ],
    'componentscreate' => [
        'name' => 'Create New Components',
    ],
    'componentsedit' => [
        'name' => 'Edit Components',
    ],
    'componentsdelete' => [
        'name' => 'Delete Components',
    ],
    'componentsfiles' => [
        'name' => 'Manage Component Files',
        'note' => 'Allows the user to upload, download, and delete files associated with components.',
    ],
    'componentscheckout' => [
        'name' => 'Check Out Components',
        'note' => 'Assign components in inventory by checking them out.',
    ],
    'componentscheckin' => [
        'name' => 'Check In Components',
        'note' => 'Check components back into inventory that are currently checked out.',
    ],
    'kits'   => [
        'name' => 'Predefined Kits',
        'note'       => 'Grants access to the Predefined Kits section of the application.',
    ],
    'kitsview' => [
        'name' => 'View Predefined Kits',
    ],
    'kitscreate' => [
        'name' => 'Create New Predefined Kits',
    ],
    'kitsedit' => [
        'name' => 'Edit Predefined Kits',
    ],
    'kitsdelete' => [
        'name' => 'Delete Predefined Kits',
    ],
    'users'   => [
        'name' => 'ผู้ใช้',
        'note'       => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'ดูผู้ใช้งาน',
    ],
    'userscreate' => [
        'name' => 'Create New Users',
    ],
    'usersedit' => [
        'name' => 'Edit Users',
    ],
    'usersdelete' => [
        'name' => 'Delete Users',
    ],
    'models'   => [
        'name' => 'Models',
        'note'       => 'Grants access to the Models section of the application.',
    ],
    'modelsview' => [
        'name' => 'ดูโมเดล',
    ],

    'modelscreate' => [
        'name' => 'Create New Models',
    ],
    'modelsedit' => [
        'name' => 'Edit Models',
    ],
    'modelsdelete' => [
        'name' => 'Delete Models',
    ],
    'categories'   => [
        'name' => 'ประเภท',
        'note'       => 'Grants access to the Categories section of the application.',
    ],
    'categoriesview' => [
        'name' => 'View Categories',
    ],
    'categoriescreate' => [
        'name' => 'Create New Categories',
    ],
    'categoriesedit' => [
        'name' => 'Edit Categories',
    ],
    'categoriesdelete' => [
        'name' => 'Delete Categories',
    ],
    'departments'   => [
        'name' => 'หน่วยงาน',
        'note'       => 'Grants access to the Departments section of the application.',
    ],
    'departmentsview' => [
        'name' => 'View Departments',
    ],
    'departmentscreate' => [
        'name' => 'Create New Departments',
    ],
    'departmentsedit' => [
        'name' => 'Edit Departments',
    ],
    'departmentsdelete' => [
        'name' => 'Delete Departments',
    ],
    'locations'   => [
        'name' => 'สถานที่',
        'note'       => 'อนุญาตให้เข้าถึงส่วนตำแหน่งที่ตั้งของแอปพลิเคชันได้',
    ],
    'locationsview' => [
        'name' => 'ดูที่ตั้ง',
    ],
    'locationscreate' => [
        'name' => 'สร้างที่ตั้งใหม่',
    ],
    'locationsedit' => [
        'name' => 'แก้ไขที่ตั้ง',
    ],
    'locationsdelete' => [
        'name' => 'ลบที่ตั้ง',
    ],
    'status-labels'   => [
        'name' => 'ป้ายสถานะ',
        'note'       => 'Grants access to the Status Labels section of the application used by Assets.',
    ],
    'statuslabelsview' => [
        'name' => 'View Status Labels',
    ],
    'statuslabelscreate' => [
        'name' => 'Create New Status Labels',
    ],
    'statuslabelsedit' => [
        'name' => 'Edit Status Labels',
    ],
    'statuslabelsdelete' => [
        'name' => 'Delete Status Labels',
    ],
    'custom-fields'   => [
        'name' => 'ฟิลด์ที่กำหนดเอง',
        'note'       => 'Grants access to the Custom Fields section of the application used by Assets.',
    ],
    'customfieldsview' => [
        'name' => 'View Custom Fields',
    ],
    'customfieldscreate' => [
        'name' => 'Create New Custom Fields',
    ],
    'customfieldsedit' => [
        'name' => 'Edit Custom Fields',
    ],
    'customfieldsdelete' => [
        'name' => 'Delete Custom Fields',
    ],
    'suppliers'   => [
        'name' => 'ซัพพลายเออร์',
        'note'       => 'Grants access to the Suppliers section of the application.',
    ],
    'suppliersview' => [
        'name' => 'View Suppliers',
    ],
    'supplierscreate' => [
        'name' => 'Create New Suppliers',
    ],
    'suppliersedit' => [
        'name' => 'Edit Suppliers',
    ],
    'suppliersdelete' => [
        'name' => 'Delete Suppliers',
    ],
    'manufacturers'   => [
        'name' => 'ผู้ผลิต',
        'note'       => 'Grants access to the Manufacturers section of the application.',
    ],
    'manufacturersview' => [
        'name' => 'View Manufacturers',
    ],
    'manufacturerscreate' => [
        'name' => 'Create New Manufacturers',
    ],
    'manufacturersedit' => [
        'name' => 'Edit Manufacturers',
    ],
    'manufacturersdelete' => [
        'name' => 'Delete Manufacturers',
    ],
    'companies'   => [
        'name' => 'บริษัท',
        'note'       => 'Grants access to the Companies section of the application.',
    ],
    'companiesview' => [
        'name' => 'View Companies',
    ],
    'companiescreate' => [
        'name' => 'Create New Companies',
    ],
    'companiesedit' => [
        'name' => 'Edit Companies',
    ],
    'companiesdelete' => [
        'name' => 'Delete Companies',
    ],
    'user-self-accounts' => [
        'name' => 'User Self Accounts',
        'note'       => 'Grants non-admin users the ability to manage certain aspects of their own user accounts.',
    ],
    'selftwo-factor' => [
        'name' => 'Manage Two-Factor Authentication',
        'note'       => 'Allows users to enable, disable, and manage two-factor authentication for their own accounts.',
    ],
    'selfapi' => [
        'name' => 'Manage API Tokens',
        'note'       => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => 'Edit Location',
        'note'       => 'Allows users to edit the location associated with their own user account.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Self Check Out Assets',
        'note'       => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'View Purchase Cost',
        'note'       => 'Allows users to view the purchase cost of items in their account view.',
    ],

    'depreciations' => [
        'name' => 'Depreciation Management',
        'note'       => 'Allows users to manage and view asset depreciation details.',
    ],
    'depreciationsview' => [
        'name' => 'View Depreciation Details',
    ],
    'depreciationsedit' => [
        'name' => 'Edit Depreciation Settings',
    ],
    'depreciationsdelete' => [
        'name' => 'Delete Depreciation Records',
    ],
    'depreciationscreate' => [
        'name' => 'Create Depreciation Records',
    ],

    'grant_all' => 'Grant all permissions for :area',
    'deny_all' => 'Deny all permissions for :area',
    'inherit_all' => 'Inherit all permissions for :area from permission groups',
    'grant' => 'Grant Permission for :area',
    'deny' => 'Deny Permission for :area',
    'inherit' => 'Inherit Permission for :area from permission groups',
    'use_groups' => 'We strongly suggest using Permission Groups instead of assigning individual permissions for easier management.'

);
