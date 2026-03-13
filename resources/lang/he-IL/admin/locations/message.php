<?php

return array(

    'does_not_exist' => 'המיקום אינו קיים.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'המיקום משויך לפחות לפריט אחד ולכן לא ניתן למחוק אותו. אנא עדכן את הפריטים כך שלא יהיה אף פריט משויך למיקום זה ונסה שנית. ',
    'assoc_child_loc'	 => 'למיקום זה מוגדרים תתי-מיקומים ולכן לא ניתן למחוק אותו. אנא עדכן את המיקומים כך שלא שמיקום זה לא יכיל תתי מיקומים ונסה שנית. ',
    'assigned_assets' => 'פריטים מוקצים',
    'current_location' => 'מיקום נוכחי',
    'open_map' => 'פתיחה במפות :map_provider_icon',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'המיקום לא נוצר, אנא נסה שנית.',
        'success' => 'המיקום נוצר בהצלחה.'
    ),

    'update' => array(
        'error'   => 'המיקום לא עודכן, אנא נסה שנית',
        'success' => 'המיקום עודכן בהצלחה.'
    ),

    'restore' => array(
        'error'   => 'מיקום לא שוחזר, אנא נסה שוב',
        'success' => 'המקום שוחזר בהצלחה.'
    ),

    'delete' => array(
        'confirm'   	=> 'האם אתה בטוח שברצונך למחוק את המיקום?',
        'error'   => 'אירעה תקלה במחיקת המיקום. אנא נסה שנית.',
        'success' => 'המיקום נמחק בהצלחה.'
    )

);
