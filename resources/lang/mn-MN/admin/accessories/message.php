<?php

return array(

    'does_not_exist' => '[:id] дагалдах хэрэгсэл байхгүй байна.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Одоогоор энэ дагалдах хэрэгсэлд: хэрэглэгчдийг шалгасан зүйлсийг тоолж байна. Дагалдах хэрэгслийг шалгаад, дахин оролдоно уу.',

    'create' => array(
        'error'   => 'Дагалдах хэрэгсэл үүсгээгүй байна, дахин оролдоно уу.',
        'success' => 'Дагалдах хэрэгсэл амжилттай хийгдсэн.'
    ),

    'update' => array(
        'error'   => 'Дагалдах хэрэгсэл шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Дагалдах хэрэгсэл амжилттай шинэчлэгдсэн.'
    ),

    'delete' => array(
        'confirm'   => 'Та энэ нэмэлт хэрэгслийг устгахыг хүсч байгаадаа итгэлтэй байна уу?',
        'error'   => 'Дагалдах хэрэгсэл устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Дагалдах хэрэгсэл амжилттай устгагдсан.'
    ),

     'checkout' => array(
        'error'   		=> 'Дагалдах хэрэгсэл шалгагдаагүй байна, дахин оролдоно уу',
        'success' 		=> 'Дагалдах хэрэгсэл амжилттай шалгасан.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Дагалдах хэрэгсэл нэвтэрсэнгүй, дахин оролдоно уу',
        'success' 		=> 'Дагалдах хэрэгсэл амжилттай шалгасан байна.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.'
    )


);
