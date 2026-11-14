<?php

return [

    'does_not_exist' => 'جزء وجود ندارد.',

    'create' => [
        'error' => 'شرکت ایجاد نشد،دوباره سعی کنید.',
        'success' => 'کامپیوننت ایجاد شد.',
    ],

    'update' => [
        'error' => 'کامپیوننت ایجاد نشد،دوباره سعی کنید',
        'success' => 'کامپیوننت ایجاد شد.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'آیا مطمعن هستید میخواهید این کامپیوننت را پاک کنید؟?',
        'error' => 'موضوع کامپوننت حذف شد. لطفا دوباره سعی کنید.',
        'success' => 'کامپیوننت حذف شد.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'کامپیوننت پرداخت نشد ، دوباره سعی کنید.',
        'success' => 'کامپیوننت پرداخت شد.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
        'unavailable' => 'کامپوننت کافی وجود ندارد: :remaining تا وجو دارد :requested درخواست شده است ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'هیچ فیلدی تغییر نکرده بود، بنابراین چیزی به روز نشد.',
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
        'error' => 'کامپیوننت بررسی نشد ، دوباره سعی کنید',
        'success' => 'کامپیوننت بررسی شد.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
    ],

];
