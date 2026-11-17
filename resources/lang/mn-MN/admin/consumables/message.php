<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Хадгалах боломжгүй.',

    'create' => [
        'error' => 'Боломжит бүтээгдээгүй байна, дахин оролдоно уу.',
        'success' => 'Боломжит бүтээмж амжилттай болсон.',
    ],

    'update' => [
        'error' => 'Боломжийн талаар шинэчилсэн, дахин оролдоно уу',
        'success' => 'Хэрэглээ шинэчлэгдсэн.',
    ],

    'delete' => [
        'confirm' => 'Та үүнийг устгахыг хүсч байгаадаа итгэлтэй байна уу?',
        'error' => 'Хэрэглэгдэхүүн устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Хэрэглүүр амжилттай устгагдсан.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Боломжийн талаар шалгаагүй байна, дахин оролдоно уу',
        'success' => 'Боломжийн талаар амжилттай шалгасан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Боломжийн талаар шалгаагүй байна, дахин оролдоно уу',
        'success' => 'Боломжийн шалгуурт амжилттай шалгагдсан.',
        'user_does_not_exist' => 'Энэ хэрэглэгч буруу байна. Дахин оролдоно уу.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ямар ч талбар өөрчлөгдсөнгүй тул шинэчлэгдээгүй байна.',
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
