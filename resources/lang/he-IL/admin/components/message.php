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

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
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

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'לא השתנו שדות, ולכן שום דבר לא עודכן.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'הרכיב לא נבדק, נסה שוב',
        'success' => 'רכיב נבדק בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
    ],

];
