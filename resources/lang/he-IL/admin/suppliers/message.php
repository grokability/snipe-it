<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'הספק אינו קיים.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'הספק לא נוצר, נסה שוב.',
        'success' => 'הספק נוצר בהצלחה.',
    ],

    'update' => [
        'error' => 'הספק לא עודכן, נסה שוב',
        'success' => 'הספק עודכן בהצלחה.',
    ],

    'delete' => [
        'confirm' => 'האם אתה בטוח שברצונך למחוק ספק זה?',
        'error' => 'היתה בעיה במחיקת הספק. בבקשה נסה שוב.',
        'success' => 'הספק נמחק בהצלחה.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
