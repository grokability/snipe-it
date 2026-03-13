<?php

return [

    'update' => [
        'error'                 => 'در حین به روزرسانی خطایی رخ داد. ',
        'success'               => 'تنظیمات با موفقیت به روزرسانی شد.',
    ],
    'backup' => [
        'delete_confirm'        => 'آیا شما مطمئن هستید که می خواهید این فایل پشتیبانی را حذف کنید؟ این کار برگشت ناپذیر است. ',
        'file_deleted'          => 'فایل پشتیبانی با موفقیت حذف شد. ',
        'generated'             => 'یک فایل پشتیبانی جدید با موفقیت ساخته شد.',
        'file_not_found'        => 'فایل پشتیبانی بر روی سرور یافت نمی شود.',
        'restore_warning'       => 'بله، آن را بازیابی کنید. من تصدیق می‌کنم که با این کار تمام داده‌های موجود در پایگاه داده بازنویسی می‌شود. با این کار همه کاربران فعلی شما (از جمله شما) نیز از سیستم خارج می شوند.
',
        'restore_confirm'       => 'آیا مطمئن هستید که می خواهید پایگاه داده خود را از :filename بازیابی کنید؟
'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'در حین پاکسازی خطایی رخ داد. ',
        'validation_failed'     => 'تایید پاکسازی ناصحیح است. لطفا کلمه ی "حذف" را در جعبه ی تاییدیه تایپ کنید.',
        'success'               => 'سوابق حذف شده با موفقیت پاکسازی شده اند.',
    ],
    'mail' => [
        'sending' => 'ارسال ایمیل تست',
        'success' => 'ایمیل فرستاده شد!
',
        'error' => 'خطا در ارسال ایمیل',
        'additional' => 'پیغام خطای اضافی ارائه نشده است. تنظیمات ایمیل و گزارش برنامه خود را بررسی کنید.
'
    ],
    'ldap' => [
        'testing' => 'تست اتصال LDAP، Binding و Query ...
',
        '500' => '500 خطای سرور. لطفا برای اطلاعات بیشتر گزارش های سرور خود را بررسی کنید.
',
        'error' => 'اوه! مشکلی پیش آمده',
        'sync_success' => 'نمونه ای از 10 کاربر بر اساس تنظیمات شما از سرور LDAP برگردانده شده است:
',
        'testing_authentication' => 'تست احراز هویت LDAP...
',
        'authentication_success' => 'کاربر در برابر LDAP با موفقیت احراز هویت شد!
'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Sending :app test message...',
        'success' => 'Your :webhook_name Integration works!',
        'success_pt1' => 'موفقیت! بررسی کنید
',
        'success_pt2' => 'برای پیام آزمایشی خود کانال را ارسال کنید و حتماً برای ذخیره تنظیمات خود روی ذخیره در زیر کلیک کنید.
',
        '500' => 'خطای سرور',
        'error' => 'Something went wrong. :app responded with: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'مشکلی پیش آمده. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
