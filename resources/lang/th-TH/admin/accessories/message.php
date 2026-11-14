<?php

return [

    'does_not_exist' => '[;id] อุปกรณ์เสริม ไม่มีอยู่',
    'not_found' => 'ไม่พบอุปกรณ์เสริมนั้น',
    'assoc_users' => 'อุปกรณ์เสริมนี้ได้เช็คเอ้าท์ให้ผู้ใช้งานแล้วจำนวน :count รายการในปัจจุบัน กรุณาเช็คอินอุปกรณ์เสริม และลองอีกครั้ง ',

    'create' => [
        'error' => 'อุปกรณ์เสริมยังไม่ได้ถูกสร้าง กรุณาลองใหม่อีกครั้ง',
        'success' => 'อุปกรณ์เสริมได้ถูกสร้างเรียบร้อยแล้ว',
    ],

    'update' => [
        'error' => 'ยังไม่ได้แก้ไขอุปกรณ์เสริม กรุณาลองใหม่',
        'success' => 'แก้ไขอุปกรณ์เสริมสำเร็จ',
    ],

    'delete' => [
        'confirm' => 'คุณแน่ใจที่จะลบอุปกรณ์เสริมนี้?',
        'error' => 'มีปัญหาขณะลบอุปกรณ์เสริมนี้ กรุณาลองอีกครั้ง.',
        'success' => 'ลบอุปกรณ์เสริมเรียบร้อยแล้ว',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'อุปกรณ์เสริมยังไม่ถูกเช็คเอ้าท์ กรุณาลองอีกครั้ง',
        'success' => 'อุปกรณ์เสริมเช็คเอ้าท์เรียบร้อยแล้ว',
        'unavailable' => 'ไม่มีอุปกรณ์เสริมสำหรับการชำระเงิน ตรวจสอบปริมาณที่มีอยู่',
        'user_does_not_exist' => 'ผู้ใช้งานไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
        'checkout_qty' => [
            'lte' => ':checkout_qty โปรดปรับจำนวนในการชำระเงินหรือจำนวนในสต็อกของอุปกรณ์เสริมนี้แล้วลองอีกครั้ง|มีอุปกรณ์เสริมที่มีอยู่ :number_currently_remaining ทั้งหมด และคุณกำลังพยายามตรวจสอบ :checkout_qty โปรดปรับปริมาณการชำระเงินหรือสต็อกรวมของอุปกรณ์เสริมนี้แล้วลองอีกครั้ง',
        ],

    ],

    'checkin' => [
        'error' => 'อุปกรณ์เสริมยังไม่ถูกเช็คอิน กรุณาลองอีกครั้ง',
        'success' => 'อุปกรณ์เสริมเช็คอินเรียบร้อยแล้ว.',
        'user_does_not_exist' => 'ผู้ใช้งานไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'ไม่มีการเปลี่ยนแปลงเขตข้อมูลดังนั้นไม่มีอะไรที่ได้รับการปรับปรุง',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
