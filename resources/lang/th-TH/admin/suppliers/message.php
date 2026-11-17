<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'ไม่มีซัพพลายเออร์',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'ซัพพลายเออร์ยังไม่ถูกสร้าง กรุณราลองใหม่อีกครั้ง',
        'success' => 'สร้างซัพพลายเออร์เสร็จสมบูรณ์แล้ว',
    ],

    'update' => [
        'error' => 'ไม่ได้อัพเดทซัพพลายเออร์ กรุณาลองใหม่อีกครั้ง',
        'success' => 'อัพเดทซัพพลายเออร์เสร็จสมบูรณ์แล้ว',
    ],

    'delete' => [
        'confirm' => 'คุณแน่ใจว่าคุณตั้งใจจะลบซัพพลายเออร์นี้?',
        'error' => 'มีปัญหาระหว่างลบซัพพลายเออร์ กรุณาลองใหม่อีกครั้ง',
        'success' => 'ลบซัพพลายเออร์เสร็จสมบูรณ์แล้ว',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
