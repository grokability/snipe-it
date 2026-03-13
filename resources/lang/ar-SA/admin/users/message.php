<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'تم تحديث المستخدمين بنجاح، ولكن لم يتم حفظ إدخال مديرك لأن المدير الذي حددته كان أيضا في قائمة المستخدمين التي سيتم تعديلها، وقد لا يكون المستخدمون مديرهم الخاص. يرجى تحديد المستخدمين مرة أخرى، باستثناء المدير.',
    'user_exists'               => 'المستخدم موجود مسبقاً!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'المستخدم غير موجود أو ليس لديك الصلاحية لعرضها.',
    'user_login_required'       => 'حقل تسجيل الدخول اجباري',
    'user_has_no_assets_assigned' => 'لا توجد أصول مخصصة حاليا للمستخدم.',
    'user_password_required'    => 'كلمة المرور اجبارية.',
    'insufficient_permissions'  => 'صلاحيات غير كافية.',
    'user_deleted_warning'      => 'تم حذف المستخدم. سيكون عليك استعادة هذا المستخدم اذا ارت التعديل عليه او تسليمه اجهزة جديدة.',
    'ldap_not_configured'        => 'لم يتم تكوين دمج لداب لهذا التثبيت.',
    'password_resets_sent'      => 'تم إرسال رابط إعادة تعيين كلمة المرور للمستخدمين المحددين الذين تم تفعيلهم ولديهم عناوين بريد إلكتروني صالحة.',
    'password_reset_sent'       => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى البريد الإلكتروني!',
    'user_has_no_email'         => 'هذا المستخدم ليس لديه عنوان بريد إلكتروني في ملفه الشخصي.',
    'log_record_not_found'        => 'تعذر العثور على سجل مطابق لهذا المستخدم.',


    'success' => array(
        'create'    => 'تم إنشاء المستخدم بنجاح.',
        'update'    => 'تم تعديل المستخدم بنجاح.',
        'update_bulk'    => 'تم تحديث المستخدمين بنجاح!',
        'delete'    => 'تم حذف المستخدم بنجاح.',
        'ban'       => 'تم حظر المستخدم بنجاح.',
        'unban'     => 'تم إلغاء الحظر عن المستخدم بنجاح.',
        'suspend'   => 'تم تعليق المستخدم بنجاح.',
        'unsuspend' => 'تم إلغاء التعليق عن المستخدم بنجاح.',
        'restored'  => 'تم استعادة المستخدم بنجاح.',
        'import'    => 'تم استيراد المستخدمين بنجاح.',
    ),

    'error' => array(
        'create' => 'حدث خطأ ما أثناء إنشاء هذا المستخدم. حاول مرة أخرى.',
        'update' => 'حدث خطأ أثناء تحديث هذا المستخدم. حاول مرة أخرى.',
        'delete' => 'حدث خطأ ما أثناء حذف هذا المستخدم. حاول مرة أخرى.',
        'delete_has_assets' => 'يحتوي هذا المستخدم على عناصر تم اخراجها ولا يمكن حذفها.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'حدث خطأ ما أثناء إلغاء تقييد الانتظار. حاول مرة أخرى.',
        'import'    => 'حدث خطأ أثناء استيراد المستخدمين. حاول مرة أخرى.',
        'asset_already_accepted' => 'هذا الجهاز تم قبوله مسبقاً.',
        'accept_or_decline' => 'يجب إما قبول مادة العرض هذه أو رفضها.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'لم يتم سحب مادة العرض التي حاولت قبولها.',
        'ldap_could_not_connect' => 'تعذر الاتصال بخادم LDAP. الرجاء التحقق من الاعدادات الخاصة بخادم LDAP في ملف اعدادات LDAP. <br>الخطأ من خادم LDAP:',
        'ldap_could_not_bind' => 'تعذر ربط خادم LDAP. الرجاء التحقق من الاعدادات الخاصة بخادم LDAP في ملف اعدادات LDAP. <br>الخطأ من خادم LDAP: ',
        'ldap_could_not_search' => 'تعذر البحث في خادم LDAP. الرجاء التحقق من الاعدادات الخاصة بخادم LDAP في ملف اعدادات LDAP. <br>الخطأ من خادم LDAP:',
        'ldap_could_not_get_entries' => 'تعذر الحصول على إدخالات من خادم LDAP. الرجاء التحقق من الاعدادات الخاصة بخادم LDAP في ملف اعدادات LDAP. <br>الخطأ من خادم LDAP:',
        'password_ldap' => 'تتم إدارة كلمة المرور لهذا الحساب بواسطة لداب / أكتيف ديركتوري. يرجى الاتصال بقسم تقنية المعلومات لتغيير كلمة المرور.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'لم يتم حذف الملف. حاول مرة اخرى.',
        'success' => 'تم حذف الملف بنجاح.',
    ),

    'upload' => array(
        'error'   => 'لم يتم تحميل الملف. حاول مرة اخرى.',
        'success' => 'تم تحميل الملف بنجاح.',
        'nofiles' => 'لم تحدد أية ملفات للتحميل',
        'invalidfiles' => 'واحد أو أكثر من الملفات كبير جدا أو هو نوع ملف غير مسموح به. أنواع الملفات المسموح بها هي ينغ و جيف و جبغ و دوك و دوك و بدف و تكست.',
    ),

    'inventorynotification' => array(
        'error'   => 'لم يتم تعيين البريد الإلكتروني لهذا المستخدم.',
        'success' => 'تم إخطار المستخدم بالمخزون الحالي الخاص به.'
    )
);