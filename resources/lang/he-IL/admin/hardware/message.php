<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'הנכס אינו קיים.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'לא סופק תג נכס.',
    'does_not_exist_or_not_requestable' => 'הנכס אינו קיים או לא זמין.',
    'assoc_users'	 	 => 'הנכס הזה מסומן כרגע למשתמש ולא ניתן למחוק אותו. בדוק תחילה את הנכס ולאחר מכן נסה למחוק שוב.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'תוויות נוצרו בהצלחה.',
    'error_generating_labels' => 'שגיאה ביצירת תוויות.',
    'no_assets_selected' => 'לא נבחרו נכסים.',

    'create' => [
        'error'   		=> 'הנכס לא נוצר, נסה שוב. You',
        'success' 		=> 'הנכס נוצר בהצלחה. :)',
        'success_linked' => 'נכס עם תג :tag נוצר בהצלחה. <strong><a href=":link" style="color: white;">לחץ כאן לצפייה</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'המשתמש המוגדר לא ניתן לאיתור.',
            'asset' => 'הפריט המוקצה לא ניתן לאיתור.',
            'location' => 'המיקום המוגדר לא נמצא.',
        ],
    ],

    'update' => [
        'error'   			=> 'הנכס לא עודכן, נסה שוב',
        'success' 			=> 'הנכס עודכן בהצלחה.',
        'encrypted_warning' => 'הנכס עודכן בהצלחה, אבל השדות המותאמים-אישית שמוצפנים לא עודכנו בגלל מחסור בהרשאות',
        'nothing_updated'	=>  'לא נבחרו שדות, ולכן דבר לא עודכן.',
        'no_assets_selected'  =>  'לא נבחרו פריטים, לא בוצעו שינויים.',
        'assets_do_not_exist_or_are_invalid' => 'הפריטים הנבחרים לא יכולים להתעדכן.',
    ],

    'restore' => [
        'error'   		=> 'הנכס לא שוחזר, נסה שוב',
        'success' 		=> 'הנכס שוחזר בהצלחה.',
        'bulk_success' 		=> 'הנכס שוחזר בהצלחה.',
        'nothing_updated'   => 'לא נבחרו פריטים, כלום לא שוחזר.', 
    ],

    'audit' => [
        'error'   		=> 'ביקורת פריט לא הצליחה: :error ',
        'success' 		=> 'ביקורת נכסים נרשמה בהצלחה.',
    ],


    'deletefile' => [
        'error'   => 'הקובץ לא נמחק. בבקשה נסה שוב.',
        'success' => 'הקובץ נמחק בהצלחה.',
    ],

    'upload' => [
        'error'   => 'הקובץ לא הועלה. בבקשה נסה שוב.',
        'success' => 'הקבצים הועלו בהצלחה.',
        'nofiles' => 'לא בחרת קבצים להעלאה, או שהקובץ שאתה מנסה להעלות גדול מדי',
        'invalidfiles' => 'אחד או יותר מהקבצים שלך גדול מדי או שהוא סוג קובץ שאינו מותר. סוגי קבצים מותרים הם png, gif, jpg, doc, docx, pdf ו- txt.',
    ],

    'import' => [
        'import_button'         => 'יבוא תהליך',
        'error'                 => 'פריטים מסוימים לא ייבאו כראוי.',
        'errorDetail'           => 'הפריטים הבאים לא יובאו בגלל שגיאות.',
        'success'               => 'הקובץ שלך יובא',
        'file_delete_success'   => 'הקובץ שלך נמחק בהצלחה',
        'file_delete_error'      => 'לא ניתן היה למחוק את הקובץ',
        'file_missing' => 'הקובץ הנבחר חסר',
        'file_already_deleted' => 'הקובץ הנבחר כבר נמחק',
        'header_row_has_malformed_characters' => 'אחד או יותר נתונים בעמודות הכותרת מכילים תווי UTF-8 פגומים',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'האם אתה בטוח שברצונך למחוק את הנכס הזה?',
        'error'   		=> 'היתה בעיה במחיקת הנכס. בבקשה נסה שוב.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'לא נבחרו נכסים ולכן לא נמחק דבר.',
        'success' 		=> 'הנכס נמחק בהצלחה.',
    ],

    'checkout' => [
        'error'   		=> 'הנכס לא נבדק, נסה שוב',
        'success' 		=> 'הנכס הוצא בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
        'not_available' => 'הנכס הזה אינו זמין לקופה!',
        'no_assets_selected' => 'עליך לבחור לפחות בנכס אחד מהרשימה',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'הנכס לא נבדק, נסה שוב',
        'success' 		=> 'הנכס נבדק בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
        'already_checked_in'  => 'הנכס כבר נבדק.',

    ],

    'requests' => [
        'error'   		=> 'הבקשה לא הצליחה, נא לנסות שוב.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'הבקשה בוטלה בהצלחה.',
        'cancel'        => 'בטל בקשה עבור פריט זה',
    ],

];
