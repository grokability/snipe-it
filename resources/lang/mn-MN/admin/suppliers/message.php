<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Нийлүүлэгч байхгүй байна.',


    'create' => array(
        'error'   => 'Нийлүүлэгч үүсгээгүй байна, дахин оролдоно уу.',
        'success' => 'Ханган нийлүүлэгч амжилтанд хүрсэн.'
    ),

    'update' => array(
        'error'   => 'Нийлүүлэгч шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Ханган нийлүүлэгч амжилттай шинэчлэгдсэн.'
    ),

    'delete' => array(
        'confirm'   => 'Та энэ нийлүүлэгчийг устгахыг хүсэж байна уу?',
        'error'   => 'Ханган нийлүүлэгчийг устгах асуудал гарч байсан. Дахин оролдоно уу.',
        'success' => 'Ханган нийлүүлэгч амжилттай устгагдсан.',
        'assoc_assets'	 => 'Энэ нийлүүлэгч одоогоор :asset_count хөрөнгөтэй холбоотой байгаа тул устгах боломжгүй байна. Энэ үйлдвэрлэгчтэй холбоогүй болгож хөрөнгөө шинэчлээд дахин оролдоно уу. ',
        'assoc_licenses'	 => 'Энэ нийлүүлэгч одоогоор :licenses_count лицензтэй холбоотой байгаа тул устгах боломжгүй байна. Энэ нийлүүлэгчтэй холбоогүй болгож лицензээ шинэчлээд дахин оролдоно уу. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
