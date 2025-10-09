<?php

return [
    'about_assets_title'           => 'درباره دارایی ها',
    'about_assets_text'            => 'دارایی ها مواردی هستند که توسط شماره سریال یا برچسب دارایی دنبال می شوند. آنها تمایل دارند که مقادیر ارزش بیشتری داشته باشند که در آن شناسایی یک مورد خاص موردنیاز باشد.',
    'archived'  				=> 'بایگانی شد',
    'asset'  					=> 'دارایی',
    'bulk_checkout'             => 'پرداخت بزرگ',
    'bulk_checkin'              => 'Bulk Checkin',
    'checkin'  					=> 'دارایی checkin',
    'checkout'  				=> 'دارایی پرداخت',
    'clear'                     => 'Clear',
    'clone'  					=> 'دارایی شگرف',
    'deployable'  				=> 'گسترش',
    'deleted'  					=> 'این دارایی حذف شده است.
',
    'delete_confirm'            => 'Are you sure you want to delete this asset?',
    'edit'  					=> 'ویرایش دارایی',
    'model_deleted'  			=> 'این مدل دارایی حذف شده است. قبل از اینکه بتوانید Asset را بازیابی کنید، باید مدل را بازیابی کنید.
',
    'model_invalid'             => 'This model for this asset is invalid.',
    'model_invalid_fix'         => 'The asset must be updated use a valid asset model before attempting to check it in or out, or to audit it.',
    'requestable'               => 'در خواست شد',
    'requested'				    => 'درخواست شده',
    'not_requestable'           => 'غیر قابل درخواست
',
    'requestable_status_warning' => 'Do not change requestable status',
    'require_serial'       => 'Require Serial Number',
    'require_serial_help'       => 'A serial number will be required when creating a new asset of this model.',
    'restore'  					=> 'بازیابی دارایی',
    'pending'  					=> 'در انتظار',
    'undeployable'  			=> 'غیرقابل گسترش',
    'undeployable_tooltip'  	=> 'This asset has a status label that is undeployable and cannot be checked out at this time.',
    'view'  					=> 'نمایش دارایی ها
',
    'csv_error' => 'شما یک خطا در فایل CSV خود دارید:
',
    'import_text' => '<p>Upload a CSV that contains asset history. The assets and users MUST already exist in the system, or they will be skipped. Matching assets for history import happens against the asset tag. We will try to find a matching user based on the user\'s name you provide, and the criteria you select below. If you do not select any criteria below, it will simply try to match on the username format you configured in the <code>Admin &gt; General Settings</code>.</p><p>Fields included in the CSV must match the headers: <strong>Asset Tag, Name, Checkout Date, Checkin Date</strong>. Any additional fields will be ignored. </p><p>Checkin Date: blank or future checkin dates will checkout items to associated user.  Excluding the Checkin Date column will create a checkin date with todays date.</p>    ',
    'csv_import_match_f-l' => 'Try to match users by <strong>firstname.lastname</strong> (<code>jane.smith</code>) format',
    'csv_import_match_initial_last' => 'Try to match users by <strong>first initial last name</strong> (<code>jsmith</code>) format',
    'csv_import_match_first' => 'Try to match users by <strong>first name</strong> (<code>jane</code>) format',
    'csv_import_match_email' => 'Try to match users by <strong>email</strong> as username',
    'csv_import_match_username' => 'Try to match users by <strong>username</strong>',
    'error_messages' => 'پیام خطا',
    'success_messages' => 'پیام  موفقیت:
',
    'alert_details' => 'لطفا برای جزئیات زیر را ببینید.
',
    'custom_export' => 'صادرات سفارشی
',
    'mfg_warranty_lookup' => ':manufacturer Warranty Status Lookup',
    'user_department' => 'User Department',
];
