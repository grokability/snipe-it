<?php

return [

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

    'superuser' => [
        'name' => 'Super User',
        'note' => 'กำหนดว่าผู้ใช้มีสิทธิ์เข้าถึงทุกส่วนของระบบผู้ดูแลระบบหรือไม่ การตั้งค่านี้จะแทนที่สิทธิ์ที่เฉพาะเจาะจงและจำกัดกว่าทั้งหมดในระบบ ',
    ],
    'admin' => [
        'name' => 'เข้าถึงส่วนจัดการระบบ',
        'note' => 'กำหนดว่าผู้ใช้รายใดสามารถเข้าถึงส่วนต่างๆ ของระบบได้เกือบทั้งหมด ยกเว้นการตั้งค่าผู้ดูแลระบบ ผู้ใช้เหล่านี้จะสามารถจัดการผู้ใช้ สถานที่ หมวดหมู่ ฯลฯ ได้ แต่จะถูกจำกัดด้วยการสนับสนุนหลายบริษัทแบบเต็มรูปแบบ หากเปิดใช้งานไว้',
    ],

    'import' => [
        'name' => 'นำเข้าไฟล์ CSV',
        'note' => 'สิ่งนี้จะช่วยให้ผู้ใช้สามารถนำเข้าข้อมูลได้ แม้ว่าการเข้าถึงผู้ใช้ ทรัพย์สิน ฯลฯ จะถูกปฏิเสธในส่วนอื่นๆ ก็ตาม',
    ],

    'reports' => [
        'name' => 'รายงานการเข้าถึง',
        'note' => 'ตรวจสอบว่าผู้ใช้มีสิทธิ์เข้าถึงส่วนรายงานของแอปพลิเคชันหรือไม่',
    ],

    'assets' => [
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
        'note' => 'อนุญาตให้ผู้ใช้ดู และแก้ไขฟิลด์กำหนดเองที่เข้ารหัสไว้ในสินทรัพย์ได้',
    ],

    'accessories' => [
        'name' => 'อุปกรณ์',
        'note' => 'อนุญาตให้เข้าถึงส่วนอุปกรณ์เสริมของแอปพลิเคชันได้',
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
        'note' => 'Allows the user to upload, download, and delete files associated with accessories. (This only makes sense with view privileges or higher.)',
    ],

    'assetsfiles' => [
        'name' => 'Manage Asset Files',
        'note' => 'Allows the user to upload, download, and delete files associated with assets. (This only makes sense with view privileges or higher.)',
    ],

    'usersfiles' => [
        'name' => 'Manage User Files',
        'note' => 'Allows the user to upload, download, and delete files associated with users. (This only makes sense with view privileges or higher.)',
    ],

    'modelsfiles' => [
        'name' => 'Manage Model Files',
        'note' => 'Allows the user to upload, download, and delete files associated with asset models on both the model view and the asset view screens. (This only makes sense with view privileges or higher.)',
    ],

    'departmentsfiles' => [
        'name' => 'Manage Department Files',
        'note' => 'Allows the user to upload, download, and delete files associated with departments. (This only makes sense with view privileges or higher.)',
    ],

    'suppliersfiles' => [
        'name' => 'Manage Supplier Files',
        'note' => 'Allows the user to upload, download, and delete files associated with suppliers. (This only makes sense with view privileges or higher.)',
    ],

    'locationsfiles' => [
        'name' => 'Manage Location Files',
        'note' => 'Allows the user to upload, download, and delete files associated with locations.(This only makes sense with view privileges or higher.)',
    ],

    'companiesfiles' => [
        'name' => 'Manage Company Files',
        'note' => 'Allows the user to upload, download, and delete files associated with companies. (This only makes sense with view privileges or higher.)',
    ],

    'consumablesfiles' => [
        'name' => 'จัดการไฟล์วัสดุสิ้นเปลือง',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables. (This only makes sense with view privileges or higher.)',
    ],

    'consumables' => [
        'name' => 'วัสดุสิ้นเปลือง',
        'note' => 'อนุญาตให้เข้าถึงส่วนวัสดุสิ้นเปลืองของแอปพลิเคชันได้',
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

    'licenses' => [
        'name' => 'ลิขสิทธิ์',
        'note' => 'Grants access to the Licenses section of the application.',
    ],
    'licensesview' => [
        'name' => 'ดูข้อมูล License',
    ],
    'licensescreate' => [
        'name' => 'สร้าง License ใหม่',
    ],
    'licensesedit' => [
        'name' => 'แก้ไข License',
    ],
    'licensesdelete' => [
        'name' => 'ลบ License',
    ],
    'licensescheckout' => [
        'name' => 'แจกจ่าย License',
        'note' => 'Allows the user to assign licenses to assets or users.',
    ],
    'licensescheckin' => [
        'name' => 'เรียกคืน License',
        'note' => 'Allows the user to unassign licenses from assets or users.',
    ],
    'licensesfiles' => [
        'name' => 'จัดการไฟล์ License',
        'note' => 'อนุญาตให้ผู้ใช้อัพโหลด, ดาวน์โหลด และลบไฟล์ที่เกี่ยวข้องกับ License',
    ],
    'componentsfiles' => [
        'name' => 'จัดการไฟล์ส่วนประกอบ',
        'note' => 'อนุญาตให้ผู้ใช้อัพโหลด, ดาวน์โหลด และลบไฟล์ที่เกี่ยวข้องกับส่วนประกอบ',
    ],

    'licenseskeys' => [
        'name' => 'จัดการ License Keys',
        'note' => 'Allows the user to view product keys associated with licenses.',
    ],
    'components' => [
        'name' => 'ส่วนประกอบ',
        'note' => 'Grants access to the Components section of the application.',
    ],
    'componentsview' => [
        'name' => 'ดูข้อมูลส่วนประกอบ',
    ],
    'componentscreate' => [
        'name' => 'สร้างส่วนประกอบใหม่',
    ],
    'componentsedit' => [
        'name' => 'แก้ไขส่วนประกอบ',
    ],
    'componentsdelete' => [
        'name' => 'ลบส่วนประกอบ',
    ],

    'componentscheckout' => [
        'name' => 'Check Out Components',
        'note' => 'Assign components in inventory by checking them out.',
    ],
    'componentscheckin' => [
        'name' => 'Check In Components',
        'note' => 'Check components back into inventory that are currently checked out.',
    ],
    'kits' => [
        'name' => 'Predefined Kits',
        'note' => 'Grants access to the Predefined Kits section of the application.',
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
    'users' => [
        'name' => 'ผู้ใช้',
        'note' => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'ดูผู้ใช้งาน',
    ],
    'userscreate' => [
        'name' => 'สร้างผู้ใช้งานใหม่',
    ],
    'usersedit' => [
        'name' => 'แก้ไขผู้ใช้งาน',
    ],
    'usersdelete' => [
        'name' => 'ลบผู้ใช้งาน',
    ],
    'models' => [
        'name' => 'รุ่น',
        'note' => 'Grants access to the Models section of the application.',
    ],
    'modelsview' => [
        'name' => 'ดูโมเดล',
    ],

    'modelscreate' => [
        'name' => 'สร้างข้อมูลรุ่นใหม่',
    ],
    'modelsedit' => [
        'name' => 'แก้ไขข้อมูลรุ่น',
    ],
    'modelsdelete' => [
        'name' => 'ลบข้อมูลรุ่น',
    ],
    'categories' => [
        'name' => 'ประเภท',
        'note' => 'Grants access to the Categories section of the application.',
    ],
    'categoriesview' => [
        'name' => 'ดูข้อมูลหมวดหมู่',
    ],
    'categoriescreate' => [
        'name' => 'สร้างหมวดหมู่ใหม่',
    ],
    'categoriesedit' => [
        'name' => 'แก้ไขหมวดหมู่',
    ],
    'categoriesdelete' => [
        'name' => 'ลบหมวดหมู่',
    ],
    'departments' => [
        'name' => 'หน่วยงาน',
        'note' => 'Grants access to the Departments section of the application.',
    ],
    'departmentsview' => [
        'name' => 'ดูข้อมูลแผนก',
    ],
    'departmentscreate' => [
        'name' => 'สร้างแผนกใหม่',
    ],
    'departmentsedit' => [
        'name' => 'แก้ไขแผนก',
    ],
    'departmentsdelete' => [
        'name' => 'ลบแผนก',
    ],
    'locations' => [
        'name' => 'สถานที่',
        'note' => 'อนุญาตให้เข้าถึงส่วนตำแหน่งที่ตั้งของแอปพลิเคชันได้',
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
    'status-labels' => [
        'name' => 'ป้ายสถานะ',
        'note' => 'Grants access to the Status Labels section of the application used by Assets.',
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
    'custom-fields' => [
        'name' => 'ฟิลด์ที่กำหนดเอง',
        'note' => 'Grants access to the Custom Fields section of the application used by Assets.',
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
    'suppliers' => [
        'name' => 'ซัพพลายเออร์',
        'note' => 'Grants access to the Suppliers section of the application.',
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
    'manufacturers' => [
        'name' => 'ผู้ผลิต',
        'note' => 'Grants access to the Manufacturers section of the application.',
    ],
    'manufacturersview' => [
        'name' => 'ดูข้อมูลผู้ผลิต',
    ],
    'manufacturerscreate' => [
        'name' => 'สร้างข้อมูลผู้ผลิต',
    ],
    'manufacturersedit' => [
        'name' => 'แก้ไขข้อมูลผู้ผลิต',
    ],
    'manufacturersdelete' => [
        'name' => 'ลบข้อมูลผู้ผลิต',
    ],
    'companies' => [
        'name' => 'บริษัท',
        'note' => 'Grants access to the Companies section of the application.',
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
        'note' => 'Grants non-admin users the ability to manage certain aspects of their own user accounts.',
    ],
    'selftwo-factor' => [
        'name' => 'จัดการระบบพิสูจน์ตัวตนแบบสองปัจจัย',
        'note' => 'อนุญาตให้ผู้ใช้ เปิด/ปิด และจัดการระบบพิสูจน์ตัวตนแบบสองปัจจัยของบัญชีตนเอง',
    ],
    'selfapi' => [
        'name' => 'จัดการ API tokens',
        'note' => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => 'แก้ไขสถานที่',
        'note' => 'อนุญาตให้แก้ไขสถานที่ของตนเองได้',
    ],
    'selfcheckout-assets' => [
        'name' => 'Self Check Out Assets',
        'note' => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'ดูข้อมูลราคา',
        'note' => 'Allows users to view the purchase cost of items in their account view.',
    ],

    'depreciations' => [
        'name' => 'จัดการข้อมูลการเสื่อมราคา',
        'note' => 'อนุญาตให้ผู้ใช้จัดการและดูข้อมูลรายละเอียดการเสื่อมราคาของสินทรัพย์ได้',
    ],
    'depreciationsview' => [
        'name' => 'ดูข้อมูลการเสื่อมราคา',
    ],
    'depreciationsedit' => [
        'name' => 'แก้ไขข้อมูลการเสื่อมราคา',
    ],
    'depreciationsdelete' => [
        'name' => 'ลบข้อมูลการเสื่อมราคา',
    ],
    'depreciationscreate' => [
        'name' => 'สร้างข้อมูลการเสื่อมราคา',
    ],

    'grant_all' => 'ให้สิทธิ์ทุกอย่างสำหรับ :area',
    'deny_all' => 'ลบสิทธิ์ทุกอย่างสำหรับ :area',
    'inherit_all' => 'สืบทอดสิทธิ์ทั้งหมดสำหรับ :area จากสิทธิ์ของกลุ่ม',
    'grant' => 'ให้สิทธิ์สำหรับ :area',
    'deny' => 'ลบสิทธิ์สำหรับ :area',
    'inherit' => 'สืบทอดสิทธิ์สำหรับ :area จากสิทธิ์ของกลุ่ม',
    'use_groups' => 'เราขอแนะนำให้ใช้สิทธิ์ของกลุ่ม แทนการกำหนดสิทธิ์รายบุคคลเพื่อให้ง่ายต่อการจัดการ',

];
