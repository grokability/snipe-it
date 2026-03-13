<?php

return array(

    'deleted' => 'דגם פריט מחוק',
    'does_not_exist' => 'המודל אינו קיים.',
    'no_association' => 'אזהרה! דגם הפריט אינו תקין או חסר!',
    'no_association_fix' => 'זה ישבור דברים בדרכים שונות ומשונות. ערוך פריט זה עכשיו וקבע לו דגם.',
    'assoc_users'	 => 'מודל זה משויך כרגע לנכס אחד או יותר ולא ניתן למחוק אותו. מחק את הנכסים ולאחר מכן נסה למחוק שוב.',
    'invalid_category_type' => 'הקטגוריה הזאת חייב להיות קטגוריית משאבים.',

    'create' => array(
        'error'   => 'המודל לא נוצר, נסה שוב.',
        'success' => 'המודל נוצר בהצלחה.',
        'duplicate_set' => 'כבר קיים מודל נכסים עם שם, יצרן ומספר דגם זה.',
    ),

    'update' => array(
        'error'   => 'המודל לא עודכן, נסה שוב',
        'success' => 'המודל עודכן בהצלחה.',
    ),

    'delete' => array(
        'confirm'   => 'האם אתה בטוח שברצונך למחוק מודל נכס זה?',
        'error'   => 'הייתה בעיה במחיקת המודל. בבקשה נסה שוב.',
        'success' => 'המודל נמחק בהצלחה.'
    ),

    'restore' => array(
        'error'   		=> 'המודל לא שוחזר, נסה שוב',
        'success' 		=> 'המודל שוחזר בהצלחה.'
    ),

    'bulkedit' => array(
        'error'   		=> 'לא השתנו שדות, ולכן שום דבר לא עודכן.',
        'success' 		=> 'דגם עודכן בהצלחה. |:model_count דגמים עודכנו בהצלחה.',
        'warn'          => 'אתה עומד לעדכן את תכונותיו של הדגם הבא:|אתה עומד לערוך את תכונותיהם של |:model_count הדגמים הבאים:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'לא נבחרו מודלים, לכן לא נמחק שום דבר.',
        'success' 		    => 'דגם נמחק!:|success_count דגמים נמחקו!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
