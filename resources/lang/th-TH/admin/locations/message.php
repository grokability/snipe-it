<?php

return array(

    'does_not_exist' => 'ไม่มีสถานที่นี้.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'สถานที่นี้ถูกใช้งานหรือเกี่ยวข้องอยู่กับผู้ใช้งานคนใดคนหนึ่ง และไม่สามารถลบได้ กรุณาปรับปรุงผู้ใช้งานของท่านไม่ให้มีส่วนเกี่ยวข้องกับสถานที่นี้ และลองอีกครั้ง. ',
    'assoc_child_loc'	 => 'สถานที่นี้ถูกใช้งานหรือเกี่ยวข้องอยู่กับหมวดสถานที่ใดที่หนึ่ง และไม่สามารถลบได้ กรุณาปรับปรุงสถานที่ของท่านไม่ให้มีส่วนเกี่ยวข้องกับหมวดสถานที่นี้ และลองอีกครั้ง. ',
    'assigned_assets' => 'สินทรัพย์ถูกมอบหมายแล้ว',
    'current_location' => 'ตำแหน่งปัจจุบัน',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'สถานที่ยังไม่ถูกสร้าง กรุณาลองใหม่อีกครั้ง.',
        'success' => 'สร้างสถานที่เรียบร้อยแล้ว.'
    ),

    'update' => array(
        'error'   => 'สถานที่ยังไม่ถูกปรับปรุง กรุณาลองใหม่อีกครั้ง',
        'success' => 'ปรับปรุงสถานที่เรียบร้อยแล้ว.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'คุณแน่ใจที่จะลบสถานที่นี้?',
        'error'   => 'มีปัญหาระหว่างการลบสถานที่ กรุณาลองใหม่อีกครั้ง.',
        'success' => 'ลบสถานที่เรียบร้อยแล้ว.'
    )

);
