<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Энэ лицензтэй холбогдохыг хүссэн хөрөнгө байхгүй байна.',
    'owner_doesnt_match_asset' => 'Энэ лицензтэй холбогдохыг хүсэж буй хөрөнгө нь тухайн сонгосон хүнээс доош сомдолоос өөр сомений эзэмшдэг.',
    'assoc_users'	 => 'Энэ лицензийг одоогоор хэрэглэгчид шалгаж, устгах боломжгүй байна. Лицензийг эхлээд шалгаад дахин устгахыг оролдоно уу.',
    'select_asset_or_person' => 'Та хөрөнгө эсвэл хэрэглэгчийг сонгох ёстой, гэхдээ хоёулаа биш.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Лиценз үүсгэгдсэнгүй, дахин оролдоно уу.',
        'success' => 'Лиценз амжилттай болсон.'
    ),

    'deletefile' => array(
        'error'   => 'Файлыг устгаагүй байна. Дахин оролдоно уу.',
        'success' => 'Файл амжилттай устгагдсан.',
    ),

    'upload' => array(
        'error'   => 'Файлд байршуулаагүй файл. Дахин оролдоно уу.',
        'success' => 'Файлууд амжилттай байршуулсан.',
        'nofiles' => 'Та байршуулах ямар ч файл сонгоогүй, эсвэл байршуулах гэж буй файл хэт том байна',
        'invalidfiles' => 'Таны файлуудын нэг юмуу хэд нь хэтэрхий том юмуу эсвэл файлын төрлийг зөвшөөрдөггүй. Зөвшөөрөгдсөн filetypes нь png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, lic юм.',
    ),

    'update' => array(
        'error'   => 'Лиценз шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Лиценз шинэчлэгдсэн.'
    ),

    'delete' => array(
        'confirm'   => 'Та энэ лицензийг устгахыг хүсч байна уу?',
        'error'   => 'Лицензийг устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Лиценз амжилттай устгагдсан.'
    ),

    'checkout' => array(
        'error'   => 'Лицензийг шалгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Лицензийг амжилттай шалгасан',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Лиценз дээр асуудал гарлаа. Дахин оролдоно уу.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Лицензийг амжилттай шалгасан байна'
    ),

);
