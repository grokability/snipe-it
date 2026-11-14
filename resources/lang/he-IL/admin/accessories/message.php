<?php

return [

    'does_not_exist' => 'האביזר [:id] לא קיים.',
    'not_found' => 'אביזר זה לא נמצא.',
    'assoc_users' => 'קיימות :count יחידות מונפקות מאבזר זה אצל משתמשים. אנא החזר את האבזרים ונסה שנית. ',

    'create' => [
        'error' => 'האבזר לא נוצר, אנא נסה שנית.',
        'success' => 'אביזר נוצר בהצלחה.',
    ],

    'update' => [
        'error' => 'האבזר לא עודכן, אנא נסה שנית',
        'success' => 'אביזר עודכן בהצלחה.',
    ],

    'delete' => [
        'confirm' => 'האם אתה בטוח שברצונך לשמור את האבזר?',
        'error' => 'ישנה בעיה במחיקת האבזר.
אנא נסה שנית.',
        'success' => 'האבזר נמחק בהצלחה.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'האבזר לא הונפק, אנא נסה שנית',
        'success' => 'האבזר הונפק בהצלחה.',
        'unavailable' => 'אביזר זה לא ניתן למסירה. אנא בדוק כמות זמינה',
        'user_does_not_exist' => 'משתמש אינו קיים. אנא נסה/י שנית.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'האבזר לא הוחזר, אנא נסה שנית',
        'success' => 'האבזר הוחזר בהצלחה.',
        'user_does_not_exist' => 'משתמש אינו קיים. אנא נסה/י שנית.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'לא השתנו שדות, ולכן שום דבר לא עודכן.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
