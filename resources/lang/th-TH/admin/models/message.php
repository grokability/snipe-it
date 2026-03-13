<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'ไม่มีโมเดลนี้',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'โมเดลนี้มีความสัมพันธ์กับสินทรัพย์หนึ่ง หรือมากกว่าในปัจจุบัน และจะไม่สามารถลบได้ กรุณาลบสินทรัพย์และลองอีกครั้ง ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'ยังไม่ได้สร้างโมเดล กรุณาลองใหม่อีกครั้ง',
        'success' => 'สร้างโมเดียลเรียบร้อยแล้ว',
        'duplicate_set' => 'มีชื่อโมเดลสินทรัพย์ ผู้ผลิต และหมายเลขโมเดลแล้ว',
    ),

    'update' => array(
        'error'   => 'ยังไม่ได้ปรับปรุงโมเดล กรุณาลองใหม่อีกครั้ง',
        'success' => 'ปรับปรุงโมเดลเรียบร้อยแล้ว',
    ),

    'delete' => array(
        'confirm'   => 'คุณแน่ใจที่จะลบโมเดลนี้?',
        'error'   => 'มีปัญหาระหว่างลบโมเดล กรุณาลองใหม่อีกครั้ง.',
        'success' => 'ลบโมเดลเรียบร้อยแล้ว'
    ),

    'restore' => array(
        'error'   		=> 'ยังไม่ได้กู้คืนโมเดล กรุณาลองใหม่อีกครั้ง',
        'success' 		=> 'กู้คืนโมเดลเรียบร้อยแล้ว'
    ),

    'bulkedit' => array(
        'error'   		=> 'ไม่มีการเปลี่ยนแปลงเขตข้อมูลดังนั้นไม่มีอะไรที่ได้รับการปรับปรุง',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'ไม่มีการเลือกรายการใด ๆ ดังนั้นจึงไม่มีสิ่งใดถูกลบ',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ': success_count โมเดลถูกลบแล้วอย่างไรก็ตาม: ไม่สามารถลบข้อมูล fail_count เนื่องจากยังมีเนื้อหาที่เชื่อมโยงอยู่'
    ),

);
