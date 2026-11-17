<?php

return [

    'field' => [
        'invalid' => 'این زمینه وجود ندارد.',
        'already_added' => 'زمینه اضافه شده است',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'فیلد ایجاد نشده است، لطفا دوباره تلاش کنید.',
            'success' => 'فیلد با موفقیت ایجاد شد.',
            'assoc_success' => 'فیلد با موفقیت به تنظیمات فیلد اضافه شد.',
        ],

        'update' => [
            'error' => 'فیلد به روز رسانی نشده است، لطفا دوباره تلاش کنید',
            'success' => 'فیلد با موفقیت به روز رسانی شد.',
        ],

        'delete' => [
            'confirm' => 'آیا شما مطمئن هستید که می خواهید این فیلد را حذف کنید؟',
            'error' => 'در حذف کردن این فیلد مشکلی وجود داشت. لطفا دوباره تلاش کنید.',
            'success' => 'فیلد با موفقیت حذف شد.',
            'in_use' => 'فیلد همچنان در حال استفاده است.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset وجود ندارد',

        'create' => [
            'error' => 'تنظیمات فیلد ایجاد نشده است، لطفا دوباره تلاش کنید.',
            'success' => 'تنظیمات فیلد با موفقیت ایجاد شد.',
        ],

        'update' => [
            'error' => 'تنظیمات فیلد به روز رسانی نشده است، لطفا دوباره تلاش کنید',
            'success' => 'تنظیمات فیلد به موفقیت به روز رسانی شد.',
        ],

        'delete' => [
            'confirm' => 'آیا شما مطمئن هستید که می خواهید این تنظیمات فیلد را حذف کنید؟',
            'error' => 'در حذف کردن تنظیمات فیلد مشکلی وجود داشت. لطفا دوباره تلاش کنید.',
            'success' => 'تنظیمات فیلد با موفقیت حذف شد.',
            'in_use' => 'تنظیمات فیلد همچنان در حال استفاده است.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'خطا در اعتبارسنجی مقادیر مجموعه فیلدهای پیش‌فرض.
',

    ],

];
