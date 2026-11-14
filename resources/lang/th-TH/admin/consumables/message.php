<?php

return [

    'invalid_category_type' => 'หมวดหมู่ต้องเป็นหมวดหมู่ของวัสดุสิ้นเปลือง',
    'does_not_exist' => 'ไม่มีรายการวัสดุสิ้นเปลืองนี้',

    'create' => [
        'error' => 'ยังไม่ได้สร้างวัสดุสิ้นเปลือง กรุณาลองใหม่อีกครั้ง',
        'success' => 'สร้างวัสดุสิ้นเปลืองเรียบร้อยแล้ว',
    ],

    'update' => [
        'error' => 'ยังไม่ได้ปรับปรุงวัสดุสิ้นเปลือง กรุณาลองใหม่อีกครั้ง',
        'success' => 'ปรับปรุงวัสดุสิ้นเปลืองเรียบร้อยแล้ว.',
    ],

    'delete' => [
        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลการสิ้นเปลืองนี้',
        'error' => 'มีปัญหาระหว่างการลบวัสดุสิ้นเปลือง กรุณาลองอีกครั้ง',
        'success' => 'การสิ้นเปลืองถูกลบเรียบร้อยแล้ว',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'วัสดุสิ้นเปลืองยังไม่ถูกเช็คเอ้าท์ กรุณาลองใหม่อีกครั้ง',
        'success' => 'เช็คเอ้าท์วัสดุสิ้นเปลืองแล้ว',
        'user_does_not_exist' => 'ผู้ใช้งานไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง.',
        'unavailable' => 'วัสดุสิ้นเปลืองมีไม่พอสำหรับการแจกจ่าย กรุณาตรวจสอบยอดคงเหลือ ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'วัสดุสิ้นเปลืองยังไม่ถูกเช็คอิน กรุณาลองใหม่อีกครั้ง',
        'success' => 'เช็คอินวัสดุสิ้นเปลืองแล้ว.',
        'user_does_not_exist' => 'ผู้ใช้งานไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'ไม่มีการเปลี่ยนแปลงเขตข้อมูลดังนั้นไม่มีอะไรที่ได้รับการปรับปรุง',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
