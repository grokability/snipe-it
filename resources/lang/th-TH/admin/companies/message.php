<?php

return [
    'does_not_exist' => 'ไม่มี บริษัท',
    'deleted' => 'บริษัทที่ถูกลบ',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'บริษัท ไม่ได้สร้างโปรดลองอีกครั้ง',
        'success' => 'บริษัท สร้างสำเร็จแล้ว',
    ],
    'update' => [
        'error' => 'บริษัท ไม่ได้รับการปรับปรุงโปรดลองอีกครั้ง',
        'success' => 'อัปเดต บริษัท สำเร็จแล้ว',
    ],
    'delete' => [
        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบ บริษัท นี้',
        'error' => 'เกิดปัญหาในการลบ บริษัท กรุณาลองอีกครั้ง.',
        'success' => 'บริษัท ถูกลบเรียบร้อยแล้ว',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
