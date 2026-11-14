<?php

return [

    'field' => [
        'invalid' => 'שדה זה אינו קיים.',
        'already_added' => 'השדה כבר נוסף',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'שדה לא נוצר, נסה שוב.',
            'success' => 'שדה נוצר בהצלחה.',
            'assoc_success' => 'שדה נוסף בהצלחה ל- fieldset.',
        ],

        'update' => [
            'error' => 'השדה לא עודכן, נסה שוב',
            'success' => 'שדה עודכן בהצלחה.',
        ],

        'delete' => [
            'confirm' => 'האם אתה בטוח שברצונך למחוק שדה זה?',
            'error' => 'הייתה בעיה במחיקת השדה. בבקשה נסה שוב.',
            'success' => 'השדה נמחק בהצלחה.',
            'in_use' => 'שדה עדיין בשימוש.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset אינו קיים',

        'create' => [
            'error' => 'Fieldset לא נוצר, נסה שוב.',
            'success' => 'Fieldset נוצר בהצלחה.',
        ],

        'update' => [
            'error' => 'Fieldset לא עודכן, נסה שוב',
            'success' => 'Fieldset עודכן בהצלחה.',
        ],

        'delete' => [
            'confirm' => 'האם אתה בטוח שברצונך למחוק את השדה הזה?',
            'error' => 'היתה בעיה במחיקת השדה. בבקשה נסה שוב.',
            'success' => 'ה- fieldset נמחק בהצלחה.',
            'in_use' => 'Fieldset עדיין בשימוש.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
