<?php

return [

    'does_not_exist' => 'ابزار/وسیله [:id] وجود ندارد.',
    'not_found' => 'وسیله پیدا نشد.',
    'assoc_users' => 'این وسیله هم اکنون:آیتم های چک شده به کاربران را حساب کنید.لطفا در لوازم جانبی چک کنید و دوباره امتحان کنید',

    'create' => [
        'error' => 'وسیله ایجاد نشد.لطفا دوباره امتحان کنید.',
        'success' => 'وسیله با موفقیت ایجاد شد',
    ],

    'update' => [
        'error' => 'وسیله به روزرسانی نشد لطفا دوباره امتحان کنید',
        'success' => 'وسیله با موفقیت به روزرسانی شد.',
    ],

    'delete' => [
        'confirm' => 'آیا مطمئن هستید می خواهید این وسیله حذف شود?',
        'error' => 'اشکال در حذف دسته بندی.لطفا دوباره امتحان کنید.',
        'success' => 'دسته بندی با موفقیت حذف شد.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'وسیله چک نشده بود. لطفا دوباره امتحان کنید',
        'success' => 'وسیله با موفقیت چک شد.',
        'unavailable' => 'امکان تحویل وسیله وجود ندارد. تعداد موجودی را چک کنید',
        'user_does_not_exist' => 'کاربر نامعتبر است. لطفا دوباره امتحان کنید.',
        'checkout_qty' => [
            'lte' => 'فقط یک عدد از این وسیله وجود دارد و شما می‌خواهید :checkout_qty تا از آن را تحویل دهید. لطفا تعداد تحویل یا موجودی وسیله را چک کنید و مجدد تلاش کنید.|فقط :number_currently_remaining عدد از این وسیله وجود دارد و شما می‌خواهید :checkout_qty تا از آن را تحویل دهید. لطفا تعداد تحویل یا موجودی وسیله را چک کنید و مجدد تلاش کنید.',
        ],

    ],

    'checkin' => [
        'error' => 'وسیله چک نشده.لطفا دوباره امتحان کنید',
        'success' => 'وسیله با موفقیت چک شد.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'هیچ فیلدی تغییر نکرده بود، بنابراین چیزی به روز نشد.',
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
