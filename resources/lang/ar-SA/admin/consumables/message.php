<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'المادة الإستهلاكية غير موجودة.',

    'create' => [
        'error' => 'لم يتم انشاء المادة الإستهلاكية، الرجاء المحاولة مرة اخرى.',
        'success' => 'تم إنشاء المادة الإستهلاكية بنجاح.',
    ],

    'update' => [
        'error' => 'لم يتم تحديث المادة الإستهلاكية، الرجاء المحاولة مرة اخرى',
        'success' => 'تم تحديث المادة الإستهلاكية بنجاح.',
    ],

    'delete' => [
        'confirm' => 'هل أنت متأكد من رغبتك في حذف هذه المادة الإستهلاكية؟',
        'error' => 'لقد حدثت مشكلة اثناء عملية حذف المادة الإستهلاكية، الرجاء المحاولة مرة اخرى.',
        'success' => 'تم حذف المادة الإستهلاكية بنجاح.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'لم يتم إخراج المادة الإستهلاكية، الرجاء المحاولة مرة اخرى',
        'success' => 'تم إخراج المادة الإستهلاكية بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صحيح. الرجاء المحاولة مرة أخرى.',
        'unavailable' => 'لا يوجد ما يكفي من المواد الاستهلاكية لهذا الدفع. الرجاء التحقق من الكمية المتبقية. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'لم يتم ادخال المادة الإستهلاكية، الرجاء المحاولة مرة اخرى',
        'success' => 'تم إخراج المادة الإستهلاكية بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صحيح. الرجاء المحاولة مرة أخرى.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'لم يتم تغيير أي حقول، لذلك لم يتم تحديث أي شيء.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
