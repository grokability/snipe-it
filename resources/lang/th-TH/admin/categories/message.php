<?php

return array(

    'does_not_exist' => 'ยังไม่มีหมวดหมู่',
    'assoc_models'	 => 'หมวดหมู่นี้เชื่อมโยงกับโมเดลอย่างน้อยหนึ่งรายการและไม่สามารถลบออกได้ โปรดอัปเดตโมเดลของคุณเพื่อไม่ให้อ้างอิงหมวดหมู่นี้อีกแล้วลองอีกครั้ง',
    'assoc_items'	 => 'หมวดหมู่นี้มีการเชื่อมโยงกับ asset_type อย่างน้อยหนึ่งรายการและไม่สามารถลบได้ โปรดอัปเดต: asset_type เพื่อไม่ให้อ้างอิงหมวดหมู่นี้อีกแล้วลองอีกครั้ง',

    'create' => array(
        'error'   => 'ยังไม่ได้สร้างหมวดหมู่ กรุณาลองอีกครั้ง.',
        'success' => 'สร้างหมวดหมู่เรียบร้อยแล้ว.'
    ),

    'update' => array(
        'error'   => 'ยังไม่ได้ปรับปรุงหมวดหมู่ กรุณาลองอีกครั้ง',
        'success' => 'ปรับปรุงหมวดหมู่เรียบร้อยแล้ว.',
        'cannot_change_category_type'   => 'คุณไม่สามารถเปลี่ยนประเภทหมวดหมู่ได้เมื่อสร้างแล้ว',
    ),

    'delete' => array(
        'confirm'                => 'คุณแน่ใจที่ต้องการจะลบหมวดหมู่นี้?',
        'error'                  => 'มีปัญหาขณะลบหมวดหมู่นี้ กรุณาลองอีกครั้ง.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
