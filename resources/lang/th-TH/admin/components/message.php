<?php

return [

    'does_not_exist' => 'คอมโพเนนต์ไม่มีอยู่',

    'create' => [
        'error' => 'ไม่ได้สร้างคอมโพเนนต์โปรดลองอีกครั้ง',
        'success' => 'สร้างคอมโพเนนต์เรียบร้อยแล้ว',
    ],

    'update' => [
        'error' => 'คอมโพเนนต์ไม่ได้รับการอัปเดตโปรดลองอีกครั้ง',
        'success' => 'คอมโพเนนต์สำเร็จแล้ว',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบคอมโพเนนต์นี้',
        'error' => 'เกิดปัญหาในการลบคอมโพเนนต์ กรุณาลองอีกครั้ง.',
        'success' => 'คอมโพเนนต์ถูกลบเรียบร้อยแล้ว',
        'error_qty' => 'บางคอมโพเนนต์ถูกเบิกจ่ายไปแล้ว กรุณาส่งคืนและลองใหม่อีกครั้ง',
    ],

    'checkout' => [
        'error' => 'ตรวจสอบคอมโพเนนต์แล้วโปรดลองอีกครั้ง',
        'success' => 'ตรวจสอบคอมโพเนนต์เรียบร้อยแล้ว',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
        'unavailable' => 'คอมโพเนนต์ไม่เพียงพอ: จำนวนที่เหลืออยู่ :remaining, จำนวนที่ร้องขอ :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'ไม่มีการเปลี่ยนแปลงเขตข้อมูลดังนั้นไม่มีอะไรที่ได้รับการปรับปรุง',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'คอมโพเนนต์ไม่ได้รับการตรวจสอบโปรดลองอีกครั้ง',
        'success' => 'ตรวจสอบคอมโพเนนต์เรียบร้อยแล้ว',
        'user_does_not_exist' => 'ผู้ใช้รายนั้นไม่ถูกต้อง กรุณาลองอีกครั้ง.',
    ],

];
