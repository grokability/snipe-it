<?php

return array(

    'does_not_exist' => 'ไม่พบใบอนุญาตหรือคุณไม่มีสิทธิ์ในการเข้าถึง',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'เนื้อหาที่คุณกำลังพยายามเชื่อมโยงกับใบอนุญาตนี้ไม่มีอยู่',
    'owner_doesnt_match_asset' => 'เนื้อหาที่คุณกำลังพยายามเชื่อมโยงกับใบอนุญาตนี้เป็นของ somene ไม่ใช่บุคคลที่เลือกในรายการที่กำหนดให้กับ dropdown',
    'assoc_users'	 => 'ขณะนี้ใบอนุญาตนี้ออกให้แก่ผู้ใช้แล้วและไม่สามารถลบได้ โปรดตรวจสอบใบอนุญาตเป็นครั้งแรกจากนั้นลองลบอีกครั้ง',
    'select_asset_or_person' => 'คุณต้องเลือกเนื้อหาหรือผู้ใช้ แต่ไม่ใช่ทั้งสองอย่าง',
    'not_found' => 'ไม่พบใบอนุญาต',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'ไม่ได้สร้างสัญญาอนุญาตโปรดลองอีกครั้ง',
        'success' => 'สร้างสัญญาอนุญาตเรียบร้อยแล้ว'
    ),

    'deletefile' => array(
        'error'   => 'ไฟล์ไม่ถูกลบ กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ถูกลบเรียบร้อยแล้ว',
    ),

    'upload' => array(
        'error'   => 'ไฟล์ไม่ได้อัปโหลด กรุณาลองอีกครั้ง.',
        'success' => 'ไฟล์ที่อัปโหลดเรียบร้อยแล้ว',
        'nofiles' => 'คุณไม่ได้เลือกไฟล์ใด ๆ สำหรับการอัปโหลดหรือไฟล์ที่คุณกำลังพยายามอัปโหลดมีขนาดใหญ่เกินไป',
        'invalidfiles' => 'ไฟล์ของคุณอย่างน้อยหนึ่งไฟล์มีขนาดใหญ่เกินไปหรือเป็นไฟล์ที่ไม่ได้รับอนุญาต ไฟล์ที่อนุญาตคือ png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml และ lic',
    ),

    'update' => array(
        'error'   => 'สัญญาอนุญาตไม่ได้รับการปรับปรุงโปรดลองอีกครั้ง',
        'success' => 'มีการอัปเดตใบอนุญาตเรียบร้อยแล้ว'
    ),

    'delete' => array(
        'confirm'   => 'คุณแน่ใจหรือไม่ว่าต้องการลบสัญญาอนุญาตนี้',
        'error'   => 'เกิดปัญหาในการนำออกใบอนุญาต กรุณาลองอีกครั้ง.',
        'success' => 'ใบอนุญาตถูกลบเรียบร้อยแล้ว'
    ),

    'checkout' => array(
        'error'   => 'มีปัญหาในการตรวจสอบใบอนุญาต กรุณาลองอีกครั้ง.',
        'success' => 'ออกใบอนุญาตแล้ว',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'เกิดปัญหาในการตรวจสอบใบอนุญาต กรุณาลองอีกครั้ง.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'ใบอนุญาตได้รับการตรวจสอบเรียบร้อยแล้ว'
    ),

);
