<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'הנכס שאתה מנסה לשייך לרישיון זה אינו קיים.',
    'owner_doesnt_match_asset' => 'הנכס שאתה מנסה לשייך לרישיון זה נמצא בבעלות somene אחר מאשר האדם שנבחר שהוקצה לתפריט הנפתח.',
    'assoc_users'	 => 'רישיון זה נבדק כעת למשתמש ולא ניתן למחוק אותו. בדוק תחילה את הרישיון ולאחר מכן נסה למחוק שוב.',
    'select_asset_or_person' => 'עליך לבחור נכס או משתמש, אך לא את שניהם.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'הרישיון לא נוצר, נסה שוב.',
        'success' => 'הרישיון נוצר בהצלחה.'
    ),

    'deletefile' => array(
        'error'   => 'הקובץ לא נמחק. בבקשה נסה שוב.',
        'success' => 'הקובץ נמחק בהצלחה.',
    ),

    'upload' => array(
        'error'   => 'הקובץ לא הועלה. בבקשה נסה שוב.',
        'success' => 'הקבצים הועלו בהצלחה.',
        'nofiles' => 'לא בחרת קבצים להעלאה, או שהקובץ שאתה מנסה להעלות גדול מדי',
        'invalidfiles' => 'אחד או יותר מהקבצים שלך גדול מדי או שהוא סוג קובץ שאינו מותר. סוגי קבצים מותרים הם png, gif, jpg, jpeg, docx, pdf, txt, zip, rar, rtf, XML ו- lic.',
    ),

    'update' => array(
        'error'   => 'הרישיון לא עודכן, נסה שוב',
        'success' => 'הרישיון עודכן בהצלחה.'
    ),

    'delete' => array(
        'confirm'   => 'האם אתה בטוח שברצונך למחוק רישיון זה?',
        'error'   => 'היתה בעיה במחיקת הרישיון. בבקשה נסה שוב.',
        'success' => 'הרישיון נמחק בהצלחה.'
    ),

    'checkout' => array(
        'error'   => 'היתה בעיה לבדוק את הרישיון. בבקשה נסה שוב.',
        'success' => 'הרשיון נבדק בהצלחה',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'היתה בעיה בבדיקת הרישיון. בבקשה נסה שוב.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'הרישיון נבדק בהצלחה'
    ),

);
