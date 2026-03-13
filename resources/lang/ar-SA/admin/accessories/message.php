<?php

return array(

    'does_not_exist' => 'الملحق [:id] غير موجود.',
    'not_found' => 'لم يتم العثور على هذا الملحق.',
    'assoc_users'	 => 'تم إخراج :count عنصر من هذا الملحق للمستخدمين، الرجاء ادخال بعض الملحقات ثم حاول مرة أخرى. ',

    'create' => array(
        'error'   => 'لم يتم انشاء الملحق، الرجاء المحاولة مرة اخرى.',
        'success' => 'تم انشاء الملحق بنجاح.'
    ),

    'update' => array(
        'error'   => 'لم يتم تحديث الملحق، الرجاء المحاولة مرة أخرى',
        'success' => 'تم تحديث الملحق بنجاح.'
    ),

    'delete' => array(
        'confirm'   => 'هل أنت متأكد من رغبتك في حذف هذا الملحق؟',
        'error'   => 'حدث خطأ أثناء محاولة حذف الملحق. الرجاء المحاولة مرة أخرى.',
        'success' => 'تم حذف الملحق بنجاح.'
    ),

     'checkout' => array(
        'error'   		=> 'لم يتم إخراج الملحق، الرجاء المحاولة مرة أخرى',
        'success' 		=> 'تم إخراج الملحق بنجاح.',
        'unavailable'   => 'الملحق غير متوفر لعملية الدفع. تحقق من الكمية المتاحة',
        'user_does_not_exist' => 'هذا المستخدم خاطئ، الرجاء المحاولة مرة أخرى.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'لم يتم ادخال الملحق، الرجاء المحاولة مرة أخرى',
        'success' 		=> 'تم ادخال الملحق بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صحيح. الرجاء المحاولة مرة أخرى.'
    )


);
