<?php

return [

    'does_not_exist' => '[:id] дагалдах хэрэгсэл байхгүй байна.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Одоогоор энэ дагалдах хэрэгсэлд: хэрэглэгчдийг шалгасан зүйлсийг тоолж байна. Дагалдах хэрэгслийг шалгаад, дахин оролдоно уу.',

    'create' => [
        'error' => 'Дагалдах хэрэгсэл үүсгээгүй байна, дахин оролдоно уу.',
        'success' => 'Дагалдах хэрэгсэл амжилттай хийгдсэн.',
    ],

    'update' => [
        'error' => 'Дагалдах хэрэгсэл шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Дагалдах хэрэгсэл амжилттай шинэчлэгдсэн.',
    ],

    'delete' => [
        'confirm' => 'Та энэ нэмэлт хэрэгслийг устгахыг хүсч байгаадаа итгэлтэй байна уу?',
        'error' => 'Дагалдах хэрэгсэл устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Дагалдах хэрэгсэл амжилттай устгагдсан.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Дагалдах хэрэгсэл шалгагдаагүй байна, дахин оролдоно уу',
        'success' => 'Дагалдах хэрэгсэл амжилттай шалгасан.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Дагалдах хэрэгсэл нэвтэрсэнгүй, дахин оролдоно уу',
        'success' => 'Дагалдах хэрэгсэл амжилттай шалгасан байна.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ямар ч талбар өөрчлөгдсөнгүй тул шинэчлэгдээгүй байна.',
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
