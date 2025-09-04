<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'הספק אינו קיים.',


    'create' => array(
        'error'   => 'הספק לא נוצר, נסה שוב.',
        'success' => 'הספק נוצר בהצלחה.'
    ),

    'update' => array(
        'error'   => 'הספק לא עודכן, נסה שוב',
        'success' => 'הספק עודכן בהצלחה.'
    ),

    'delete' => array(
        'confirm'   => 'האם אתה בטוח שברצונך למחוק ספק זה?',
        'error'   => 'היתה בעיה במחיקת הספק. בבקשה נסה שוב.',
        'success' => 'הספק נמחק בהצלחה.',
        'assoc_assets'	 => 'This supplier is currently associated with :asset_count asset(s) and cannot be deleted. Please update your assets to no longer reference this supplier and try again. ',
        'assoc_licenses'	 => 'This supplier is currently associated with :licenses_count licences(s) and cannot be deleted. Please update your licenses to no longer reference this supplier and try again. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
