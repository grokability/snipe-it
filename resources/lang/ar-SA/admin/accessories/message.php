<?php

return [

    'does_not_exist' => 'الملحق [:id] غير موجود.',
    'not_found' => 'لم يتم العثور على هذا الملحق.',
    'assoc_users' => 'تم إخراج :count عنصر من هذا الملحق للمستخدمين، الرجاء ادخال بعض الملحقات ثم حاول مرة أخرى. ',

    'create' => [
        'error' => 'لم يتم انشاء الملحق، الرجاء المحاولة مرة اخرى.',
        'success' => 'تم انشاء الملحق بنجاح.',
    ],

    'update' => [
        'error' => 'لم يتم تحديث الملحق، الرجاء المحاولة مرة أخرى',
        'success' => 'تم تحديث الملحق بنجاح.',
    ],

    'delete' => [
        'confirm' => 'هل أنت متأكد من رغبتك في حذف هذا الملحق؟',
        'error' => 'حدث خطأ أثناء محاولة حذف الملحق. الرجاء المحاولة مرة أخرى.',
        'success' => 'تم حذف الملحق بنجاح.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'لم يتم إخراج الملحق، الرجاء المحاولة مرة أخرى',
        'success' => 'تم إخراج الملحق بنجاح.',
        'unavailable' => 'الملحق غير متوفر لعملية الدفع. تحقق من الكمية المتاحة',
        'user_does_not_exist' => 'هذا المستخدم خاطئ، الرجاء المحاولة مرة أخرى.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'لم يتم ادخال الملحق، الرجاء المحاولة مرة أخرى',
        'success' => 'تم ادخال الملحق بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صحيح. الرجاء المحاولة مرة أخرى.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'لم يتم تغيير أي حقول، لذلك لم يتم تحديث أي شيء.',
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
