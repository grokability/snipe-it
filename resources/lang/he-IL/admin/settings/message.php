<?php

return [

    'update' => [
        'error'                 => 'אירעה שגיאה בעת העדכון.',
        'success'               => 'ההגדרות עודכנו בהצלחה.',
    ],
    'backup' => [
        'delete_confirm'        => 'האם אתה בטוח שברצונך למחוק קובץ גיבוי זה? לא ניתן לבטל פעולה זו.',
        'file_deleted'          => 'קובץ הגיבוי נמחק בהצלחה.',
        'generated'             => 'קובץ גיבוי חדש נוצר בהצלחה.',
        'file_not_found'        => 'קובץ גיבוי זה לא נמצא בשרת.',
        'restore_warning'       => 'כן, שחזר זאת. אני מאשר שזה ידרוס מידע קיים במסד הנתונים. זה גם ינתק את כלל המשתמשים הקיימים (כולל אותך).',
        'restore_confirm'       => 'האם ברצונך לשחזר את המסד נתונים מ: קובץ?'
    ],
    'restore' => [
        'success'               => 'גיבוי המערכת שלך שוחזר. תתחבר מחדש בבקשה.'
    ],
    'purge' => [
        'error'     => 'אירעה שגיאה בעת הטיהור.',
        'validation_failed'     => 'אישור הטיהור שלך שגוי. הקלד את המילה "DELETE" בתיבת האישור.',
        'success'               => 'רשומות נמחקו בהצלחה.',
    ],
    'mail' => [
        'sending' => 'שולח מייל לבדיקה...',
        'success' => 'המייל נשלח!',
        'error' => 'מייל לא נשלח.',
        'additional' => 'קיימות שגיאות נוספות. בדוק במייל שלך ובלוגים.'
    ],
    'ldap' => [
        'testing' => 'בודק חיבור LDAP, שאילתות ומבנה נתונים...',
        '500' => 'שגיאה 500, בבקשה בודק את הלוגים בשרת לעוד נתונים.',
        'error' => 'משהו השתבש אופסי פופסי :(',
        'sync_success' => 'בדיקה מול שרת LDAP ל 10 משתמשים בוצעה בהתאם להגדרות שלך:',
        'testing_authentication' => 'בודק אימות מול שרת LDAP...',
        'authentication_success' => 'התחברות לשרת LDAפ עברה בהצלחה!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Sending :app test message...',
        'success' => 'Your :webhook_name Integration works!',
        'success_pt1' => 'הבדיקה עברה בהצלחה! בדוק את ',
        'success_pt2' => ' channel for your test message, and be sure to click SAVE below to store your settings.',
        '500' => '500 שגיאת שרת.',
        'error' => 'Something went wrong. :app responded with: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'משהו השתבש אופסי פופסי. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' ערוץ ההתליות לא נמצא.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
