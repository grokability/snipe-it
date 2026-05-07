<?php

return [

    'does_not_exist' => 'הרכיב אינו קיים.',

    'create' => [
        'error' => 'הרכיב לא נוצר, נסה שוב.',
        'success' => 'הרכיב נוצר בהצלחה.',
    ],

    'update' => [
        'error' => 'הרכיב לא עודכן, נסה שוב',
        'success' => 'הרכיב עודכן בהצלחה.',
    ],

    'delete' => [
        'confirm' => 'האם אתה בטוח שברצונך למחוק רכיב זה?',
        'error' => 'הייתה בעיה במחיקת הרכיב. בבקשה נסה שוב.',
        'success' => 'הרכיב נמחק בהצלחה.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'הרכיב לא נבדק, נסה שוב',
        'success' => 'רכיב הוצא בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'checkin' => [
        'error' => 'הרכיב לא נבדק, נסה שוב',
        'success' => 'רכיב נבדק בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
    ],

];
