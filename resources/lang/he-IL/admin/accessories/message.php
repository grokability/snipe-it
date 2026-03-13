<?php

return array(

    'does_not_exist' => 'האביזר [:id] לא קיים.',
    'not_found' => 'אביזר זה לא נמצא.',
    'assoc_users'	 => 'קיימות :count יחידות מונפקות מאבזר זה אצל משתמשים. אנא החזר את האבזרים ונסה שנית. ',

    'create' => array(
        'error'   => 'האבזר לא נוצר, אנא נסה שנית.',
        'success' => 'אביזר נוצר בהצלחה.'
    ),

    'update' => array(
        'error'   => 'האבזר לא עודכן, אנא נסה שנית',
        'success' => 'אביזר עודכן בהצלחה.'
    ),

    'delete' => array(
        'confirm'   => 'האם אתה בטוח שברצונך לשמור את האבזר?',
        'error'   => 'ישנה בעיה במחיקת האבזר.
אנא נסה שנית.',
        'success' => 'האבזר נמחק בהצלחה.'
    ),

     'checkout' => array(
        'error'   		=> 'האבזר לא הונפק, אנא נסה שנית',
        'success' 		=> 'האבזר הונפק בהצלחה.',
        'unavailable'   => 'אביזר זה לא ניתן למסירה. אנא בדוק כמות זמינה',
        'user_does_not_exist' => 'משתמש אינו קיים. אנא נסה/י שנית.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'האבזר לא הוחזר, אנא נסה שנית',
        'success' 		=> 'האבזר הוחזר בהצלחה.',
        'user_does_not_exist' => 'משתמש אינו קיים. אנא נסה/י שנית.'
    )


);
