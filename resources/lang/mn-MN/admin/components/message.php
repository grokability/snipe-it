<?php

return [

    'does_not_exist' => 'Бүрэлдэхүүн хэсэг байхгүй байна.',

    'create' => [
        'error' => 'Бүрэлдэхүүн бүтээсэнгүй, дахин оролдоно уу.',
        'success' => 'Бүрэлдэхүүн хэсэг амжилттай бүтээгдсэн.',
    ],

    'update' => [
        'error' => 'Бүрэлдэхүүн шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Бүрэлдэхүүн хэсэг амжилттай шинэчлэгдсэн.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Та энэ бүрэлдэхүүн хэсгийг устгахдаа итгэлтэй байна уу?',
        'error' => 'Энэ бүрэлдэхүүнийг устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Бүрэлдэхүүн хэсэг амжилттай устгагдсан.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Бүрэлдэхүүнийг шалгаагүй байна, дахин оролдоно уу',
        'success' => 'Бүрэлдэхүүн хэсэг амжилттай шалгагдсан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ямар ч талбар өөрчлөгдсөнгүй тул шинэчлэгдээгүй байна.',
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
        'error' => 'Бүрэлдэхүүнийг шалгаагүй байна, дахин оролдоно уу',
        'success' => 'Бүрэлдэхүүн хэсэг амжилттай шалгагдсан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
    ],

];
