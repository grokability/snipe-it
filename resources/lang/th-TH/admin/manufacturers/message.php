<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'ยังไม่มีผู้ผลิต',
    'assoc_users'	 => 'ผู้ผลิตนี้มีความสัมพันธ์ในรายการโมเดลอย่างน้อยหนึ่งรายการในปัจจุบัน และไม่สามารถลบได้ กรุณาอัพเดทโมเดลของคุณออกจากความสัมพันธ์ดังกล่าว และลองอีกครั้ง ',

    'create' => array(
        'error'   => 'ยังไม่ได้สร้างผู้ผลิต กรุณาลองใหม่อีกครั้ง',
        'success' => 'สร้างผู้ผลิตเรียบร้อยแล้ว'
    ),

    'update' => array(
        'error'   => 'ยังไม่ได้ปรับปรุงผู้ผลิต กรุณาลองใหม่อีกครั้ง',
        'success' => 'ปรับปรุงผู้ผลิตเรียบร้อยแล้ว'
    ),

    'restore' => array(
        'error'   => 'ยังไม่ได้ปรับปรุงผู้ผลิต กรุณาลองใหม่อีกครั้ง',
        'success' => 'กู้คืนข้อมูลผู้ผลิตเรียบร้อยแล้ว'
    ),

    'delete' => array(
        'confirm' => 'คุณแน่ใจที่จะลบผู้ผลิตนี้?',
        'error'   => 'มีปัญหาระหว่างการลบผู้ผลิต กรุณาลองใหม่อีกครั้ง',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
