<?php

return array(

    'accepted'                  => 'קיבלת בהצלחה את הנכס הזה.',
    'declined'                  => 'דחיית את הנכס הזה בהצלחה.',
    'bulk_manager_warn'	        => 'המשתמשים שלך עודכנו בהצלחה, אך רשומת המנהל שלך לא נשמרה מפני שהמנהל שבחרת נבחר גם ברשימת המשתמשים כדי לערוך, והמשתמשים לא יכולים להיות המנהל שלהם. בחר שוב את המשתמשים שלך, למעט המנהל.',
    'user_exists'               => 'משתמש כבר קיים!',
    'user_not_found'            => 'User does not exist or you do not have permission view them.',
    'user_login_required'       => 'יש להזין את שדה הכניסה',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'נדרשת הסיסמה.',
    'insufficient_permissions'  => 'הרשאות לא מספיקות.',
    'user_deleted_warning'      => 'משתמש זה נמחק. יהיה עליך לשחזר משתמש זה כדי לערוך אותם או להקצות להם נכסים חדשים.',
    'ldap_not_configured'        => 'שילוב LDAP לא הוגדר עבור התקנה זו.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'המשתמש נוצר בהצלחה.',
        'update'    => 'המשתמש עודכן בהצלחה.',
        'update_bulk'    => 'המשתמשים עודכנו בהצלחה!',
        'delete'    => 'המשתמש נמחק בהצלחה.',
        'ban'       => 'המשתמש אוסר בהצלחה.',
        'unban'     => 'המשתמש בוטל בהצלחה.',
        'suspend'   => 'המשתמש הושעה בהצלחה.',
        'unsuspend' => 'המשתמש בוטל בהצלחה.',
        'restored'  => 'המשתמש שוחזר בהצלחה.',
        'import'    => 'המשתמשים יובאו בהצלחה.',
    ),

    'error' => array(
        'create' => 'היתה בעיה ביצירת המשתמש. בבקשה נסה שוב.',
        'update' => 'היתה בעיה בעדכון המשתמש. בבקשה נסה שוב.',
        'delete' => 'היתה בעיה במחיקת המשתמש. בבקשה נסה שוב.',
        'delete_has_assets' => 'למשתמש זה יש פריטים שהוקצו ולא ניתן למחוק אותם.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'היתה בעיה בהתעלמות מהמשתמש. בבקשה נסה שוב.',
        'import'    => 'היתה בעיה בייבוא ​​משתמשים. בבקשה נסה שוב.',
        'asset_already_accepted' => 'הנכס כבר התקבל.',
        'accept_or_decline' => 'עליך לקבל או לדחות את הנכס.',
        'cannot_delete_yourself' => 'אנחנו נרגיש רע מאוד אם תמחק את עצמך, בבקשה שקול זאת מחדש.',
        'incorrect_user_accepted' => 'הנכס שניסית לקבל לא נבדק לך.',
        'ldap_could_not_connect' => 'לא ניתן להתחבר לשרת LDAP. בדוק את תצורת שרת LDAP בקובץ תצורת LDAP. <br> שגיאה משרת LDAP:',
        'ldap_could_not_bind' => 'לא ניתן היה להתחבר לשרת LDAP. בדוק את תצורת שרת LDAP בקובץ תצורת LDAP. <br> שגיאה משרת LDAP:',
        'ldap_could_not_search' => 'לא ניתן לחפש בשרת LDAP. בדוק את תצורת שרת LDAP בקובץ תצורת LDAP. <br> שגיאה משרת LDAP:',
        'ldap_could_not_get_entries' => 'לא ניתן לקבל רשומות משרת LDAP. בדוק את תצורת שרת LDAP בקובץ תצורת LDAP. <br> שגיאה משרת LDAP:',
        'password_ldap' => 'הסיסמה עבור חשבון זה מנוהלת על ידי LDAP / Active Directory. צור קשר עם מחלקת ה- IT כדי לשנות את הסיסמה שלך.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'הקובץ לא נמחק. בבקשה נסה שוב.',
        'success' => 'הקובץ נמחק בהצלחה.',
    ),

    'upload' => array(
        'error'   => 'הקובץ לא הועלה. בבקשה נסה שוב.',
        'success' => 'הקבצים הועלו בהצלחה.',
        'nofiles' => 'לא בחרת קבצים להעלאה',
        'invalidfiles' => 'אחד או יותר מהקבצים שלך גדול מדי או שהוא סוג קובץ שאינו מותר. סוגי קבצים מותרים הם png, gif, jpg, doc, docx, pdf ו- txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'דוא"ל אינו מוגדר עבור משתמש זה.',
        'success' => 'המשתמש עודכן בדבר הפריטים הרשומים עליו.'
    )
);