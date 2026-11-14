<?php

return [

    'does_not_exist' => 'المكون غير موجود.',

    'create' => [
        'error' => 'لم يتم إنشاء المكون، الرجاء المحاولة مرة أخرى.',
        'success' => 'تم إنشاء المكون بنجاح.',
    ],

    'update' => [
        'error' => 'لم يتم تحديث المكون، الرجاء المحاولة مرة أخرى',
        'success' => 'تم تحديث المكون بنجاح.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'هل أنت متأكد من رغبتك في حذف هذا المكون؟',
        'error' => 'حدثت مشكلة اثناء عملية حذف المكون. الرجاء المحاولة مرة اُخرى.',
        'success' => 'تم حذف المكون بنجاح.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'لم تتم عملية اخراج المكون، الرجاء المحاولة مرة اخرى',
        'success' => 'تم إخراج المكون بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم خاطئ، الرجاء المحاولة مرة أخرى.',
        'unavailable' => 'لا توجد عناصر كافية remaining: :remaining متبقية، :requested مطلوبة ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'لم يتم تغيير أي حقول، لذلك لم يتم تحديث أي شيء.',
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
        'error' => 'لم تتم عملية ادخال المكون، الرجاء المحاولة مرة اخرى',
        'success' => 'تم إدخال المكون بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم خاطئ، الرجاء المحاولة مرة أخرى.',
    ],

];
