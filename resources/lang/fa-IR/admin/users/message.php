<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'کاربران شما با موفقیت به روز شده اند، با این حال مدیر ورود شما ذخیره نشد زیرا مدیر شما انتخاب شده بود نیز در لیست کاربر برای ویرایش، و کاربران ممکن است مدیر خود نیست. لطفا کاربران خود را دوباره انتخاب کنید، به غیر از مدیر.',
    'user_exists'               => 'کاربر "{0}" در حال حاضر وجود دارد.',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'فیلد ورود الزامی است.',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'کلمه عبور ضروری است.',
    'insufficient_permissions'  => 'دسترسی محدود',
    'user_deleted_warning'      => 'این کاربر حذف شده است.شما باید این کاربر را بازگردانی و آنها را ویرایش و دارایی به آنها اختصاص دهید.',
    'ldap_not_configured'        => 'LDAPبرای این نصب و راه اندازی پیکربندی نشده است.',
    'password_resets_sent'      => 'برای کاربران منتخبی که فعال شده اند و آدرس ایمیل معتبری دارند، پیوند بازنشانی رمز عبور ارسال شده است.
',
    'password_reset_sent'       => 'پیوند بازنشانی رمز عبور به :email ارسال شده است!
',
    'user_has_no_email'         => 'این کاربر آدرس ایمیلی در نمایه خود ندارد.
',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'کاربر با موفقیت ایجاد شد.',
        'update'    => 'کاربر با موفقیت به روز شد.',
        'update_bulk'    => 'کاربران با موفقیت به روز شد!',
        'delete'    => 'کاربر با موفقیت حذف شد.',
        'ban'       => 'کاربر با موفقیت متوقف شد.',
        'unban'     => 'محرومیت کاربر با موفقیت متوقف شد.',
        'suspend'   => 'کاربر با موفقیت معلق شد.',
        'unsuspend' => 'تعلیق کاربر با موفقیت متوقف شد.',
        'restored'  => 'کابر با موفقیت بازگردانی شد.',
        'import'    => 'کاربران با موفقیت وارد شدند.',
    ),

    'error' => array(
        'create' => 'اشکال در ایجاد کاربر.لطفا دوباره تلاش کنید.',
        'update' => 'اشکال در به روزرسانی کاربر.لطفا دوباره تلاش کنید.',
        'delete' => 'اشکال در حذف کاربر.لطفا دوباره تلاش کنید.',
        'delete_has_assets' => 'این کاربر دارای مواردی تعیین شده است و نمی تواند حذف شود.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'اشکال در به رفع تعلیق کاربر.لطفا دوباره تلاش کنید.',
        'import'    => 'اشکال در به وارد کردن کاربران.لطفا دوباره تلاش کنید.',
        'asset_already_accepted' => 'دارایی پذیرفته شده است.',
        'accept_or_decline' => 'شما باید این دارایی را قبول یا رد کنید.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'دارایی که میخواهید قبول یا رد کنید به شما محول نشده.',
        'ldap_could_not_connect' => 'ارتباط با سرور LDAP برقرار نشد.لطفا پیکربندی LDPA سرور را در فایل LDPA config بررسی کنید.<br>اشکال از سرور LDPA:',
        'ldap_could_not_bind' => 'ارتباط با سرور LDAP برقرار نشد.لطفا پیکربندی LDPA سرور را در فایل LDPA config بررسی کنید.<br>اشکال از سرور LDPA:',
        'ldap_could_not_search' => 'جستجو در سرور LDPA انجام نشد.لطفا پیکربندی LDPA سرور را در فایل LDPA config بررسی کنید.<br>اشکال از سرور LDPA:',
        'ldap_could_not_get_entries' => 'مجوز از سرور LDPA گرفته نشد.لطفا پیکربندی LDPA سرور را در فایل LDPA config بررسی کنید.<br>اشکال از سرور LDPA:',
        'password_ldap' => 'رمز عبور این حساب توسط LDAP / Active Directory مدیریت می شود. برای تغییر رمز عبور خود، لطفا با بخش IT خود تماس بگیرید.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'فایل حذف نشد.لطفا دوباره تلاش کنید.',
        'success' => 'نقش با موفقیت حذف شد.',
    ),

    'upload' => array(
        'error'   => 'فایل(ها) بارگذاری نشدند.لطفا دوباره تلاش کنید.',
        'success' => 'فایل(ها) با موفقیت بارگذاری شدند.',
        'nofiles' => 'شما هیچ فایلی برای بارگذاری انتخاب نکرده اید.',
        'invalidfiles' => 'یک یا چندی از فایل های شما با پسوند غیرمجاز هستند.پسوند های مجاز شامل:png, gif, jpg, doc, docx, pdf و text می باشند.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);