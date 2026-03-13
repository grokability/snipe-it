<?php

return array(

    'accepted'                  => 'คุณได้ยอมรับสิ่งของนี้แล้ว',
    'declined'                  => 'คุณได้ปฏิเสธสิ่งของนี้แล้ว',
    'bulk_manager_warn'	        => 'ผู้ใช้ของคุณได้รับการอัปเดตแล้วแม้ว่ารายการผู้จัดการจะไม่ได้รับการบันทึกเนื่องจากผู้จัดการที่คุณเลือกอยู่ในรายชื่อผู้ใช้ที่จะแก้ไขและผู้ใช้ต้องไม่เป็นผู้จัดการของตัวเอง โปรดเลือกผู้ใช้ของคุณอีกครั้งโดยไม่รวมผู้จัดการ',
    'user_exists'               => 'มีผู้ใช้งานนี้แล้ว',
    'cannot_delete'             => 'ไม่มีผู้ใช้นี้ หรือคุณไม่มีสิทธิ์ในการลบ',
    'user_not_found'            => 'ไม่มีผู้ใช้นี้ หรือคุณไม่มีสิทธิ์ในการดู',
    'user_login_required'       => 'ต้องการชื่อผู้ใช้งาน',
    'user_has_no_assets_assigned' => 'ไม่มีสินทรัพย์ใดอยู่ในความรับผิดชอบของผู้ใช้',
    'user_password_required'    => 'ต้องการรหัสผ่าน',
    'insufficient_permissions'  => 'สิทธิ์การใช้งานไม่เพียงพอ',
    'user_deleted_warning'      => 'ผู้ใช้งานนี้ถูกลบแล้ว คุณจำเป็นต้องกู้คืนผู้ใช้งานก่อนแก้ไข',
    'ldap_not_configured'        => 'การทำงานร่วมกับ LDAP ไม่ได้ถูกตั้งค่าไว้สำหรับการติดตั้งนี้',
    'password_resets_sent'      => 'ผู้ใช้งานที่เลือกซึ่งเปิดใช้งานแล้วและมีที่อยู่อีเมลที่ถูกต้อง ได้รับลิงก์สำหรับรีเซ็ตรหัสผ่านแล้ว',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'ผู้ใช้นี้ยังไม่มีอีเมล์ในโปรไฟล์',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'สร้างผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'update'    => 'แก้ไขผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'update_bulk'    => 'อัปเดตผู้ใช้สำเร็จแล้ว!',
        'delete'    => 'ลบผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'ban'       => 'แบนผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'unban'     => 'ยกเลิกการแบนผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'suspend'   => 'ระงับผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'unsuspend' => 'ยกเลิกระงับผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'restored'  => 'กู้คืนผู้ใช้งานเสร็จสมบูรณ์แล้ว',
        'import'    => 'นำเข้าผู้ใช้งานเสร็จสมบูรณ์แล้ว',
    ),

    'error' => array(
        'create' => 'มีปัญหาระหว่างการสร้างผู้ใช้งาน กรุณาลองใหม่อีกครั้ง',
        'update' => 'มีปัญหาระหว่างปรับปรุงข้อมูลผู้ใช้ กรุณาลองใหม่อีกครั้ง',
        'delete' => 'มีปัญหาระหว่างลบผู้ใช้งาน กรุณาลองใหม่อีกครั้ง',
        'delete_has_assets' => 'ผู้ใช้รายนี้มีรายการที่กำหนดและไม่สามารถลบได้',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'มีปัญหาระหว่างการยกเลิกการระงับผู้ใช้งาน กรุณาลองใหม่อีกครั้ง',
        'import'    => 'มีปัญหาระหว่างการนำเข้าผู้ใช้งาน กรุณาลองใหม่อีกครั้ง',
        'asset_already_accepted' => 'ทรัพย์สินนี้ได้รับการยอมรับแล้ว',
        'accept_or_decline' => 'คุณต้องยอมรับ หรือปฏิเสธสินทรัพย์นี้',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'เนื้อหาที่คุณพยายามยอมรับไม่ได้ถูกเช็คเอาท์ให้คุณ',
        'ldap_could_not_connect' => 'ไม่สามารถเชื่อมต่อกับ LDAP Server ได้ กรุณาตรวจสอบการตั้งค่า LDAP Server ของคุณในไฟล์ตั้งค่า LDAP <br>ผิดพลาดจาก LDAP Server:',
        'ldap_could_not_bind' => 'ไม่สามารถผูกกับ LDAP Server ได้ กรุณาตรวจสอบการตั้งค่า LDAP Server ของคุณในไฟล์ตั้งค่า LDAP <br>ผิดพลาดจาก LDAP Server: ',
        'ldap_could_not_search' => 'ไม่สามารถค้นหา LDAP Server ได้ กรุณาตรวจสอบการตั้งค่า LDAP Server ของคุณในไฟล์ตั้งค่า LDAP <br>ผิดพลาดจาก LDAP Server:',
        'ldap_could_not_get_entries' => 'ไม่สามารถดึงข้อมูลจาก LDAP Server ได้ กรุณาตรวจสอบการตั้งค่า LDAP Server ของคุณในไฟล์ตั้งค่า LDAP <br>ผิดพลาดจาก LDAP Server:',
        'password_ldap' => 'รหัสผ่านสำหรับบัญชีนี้ได้รับการจัดการโดย LDAP / Active Directory โปรดติดต่อฝ่ายไอทีของคุณเพื่อเปลี่ยนรหัสผ่านของคุณ',
        'multi_company_items_assigned' => 'ผู้ใช้นี้มีสิ่งของที่ได้รับมอบหมายซึ่งเป็นของบริษัทอื่น โปรดตรวจสอบรายการเหล่านั้นหรือแก้ไขข้อมูลบริษัทของผู้ใช้'
    ),

    'deletefile' => array(
        'error'   => 'ไฟล์ยังไม่ถูกลบ กรุณาลองใหม่อีกครั้ง',
        'success' => 'ไฟล์ถูกลบเรียบร้อยแล้ว',
    ),

    'upload' => array(
        'error'   => 'ไฟล์ยังไม่ถูกอัพโหลด กรุณาลองอีกครั้ง',
        'success' => 'ไฟล์ถูกอัพโหลดเรียบร้อยแล้ว',
        'nofiles' => 'คุณยังไม่ได้เลือกไฟล์สำหรับอัพโหลด',
        'invalidfiles' => 'ไฟล์ข้อมูลของคุณมีขนาดใหญ่เกินไป หรือประเภทของไฟล์ไม่ได้รับการอนุญาต ประเภทของไฟล์ที่ได้รับอนุญาตแล้วมีดังนี้ png, gif, jpg, doc, docx, pdf, and txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);