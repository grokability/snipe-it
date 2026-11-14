<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'مواد مصرفی یافت نشد.',

    'create' => [
        'error' => 'لوازم مصرفي ایجاد نشد, لطفا دوباره سعی کنید.',
        'success' => 'مواد مصرفی ایجاد شد.',
    ],

    'update' => [
        'error' => 'لوازم مصرفي بروز رسانی نشد, لطفا دوباره سعی کنید',
        'success' => 'لوازم مصرفي با موفقیت به روز رسانی شد.',
    ],

    'delete' => [
        'confirm' => 'آیا مطمعنید که می خواهید این ماده مصرفی را حذف کنید؟',
        'error' => 'یک مشکل در حذف کردن ماده مصرفی وجود دارد،دوباره سعی کنید.',
        'success' => 'مواد مصرفی حذف شد.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'مواد مصرفی بررسی نشد ، دوباره سعی کنید.',
        'success' => 'مواد مصرفی بررسی شد.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
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
        'error' => 'مواد مصرفی بررسی نشد ، دوباره سعی کنید',
        'success' => 'مواد مصرفی بررسی شد.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'هیچ فیلدی تغییر نکرده بود، بنابراین چیزی به روز نشد.',
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
