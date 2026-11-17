<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Нийлүүлэгч байхгүй байна.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Нийлүүлэгч үүсгээгүй байна, дахин оролдоно уу.',
        'success' => 'Ханган нийлүүлэгч амжилтанд хүрсэн.',
    ],

    'update' => [
        'error' => 'Нийлүүлэгч шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Ханган нийлүүлэгч амжилттай шинэчлэгдсэн.',
    ],

    'delete' => [
        'confirm' => 'Та энэ нийлүүлэгчийг устгахыг хүсэж байна уу?',
        'error' => 'Ханган нийлүүлэгчийг устгах асуудал гарч байсан. Дахин оролдоно уу.',
        'success' => 'Ханган нийлүүлэгч амжилттай устгагдсан.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
