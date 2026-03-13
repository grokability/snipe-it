<?php

return [

    'update' => [
        'error'                 => 'حدث خطأ أثناء التحديث. ',
        'success'               => 'تم تحديث الإعدادات بنجاح.',
    ],
    'backup' => [
        'delete_confirm'        => 'هل تريد بالتأكيد حذف ملف النسخة الاحتياطية هذا؟ لا يمكن التراجع عن هذا الإجراء.',
        'file_deleted'          => 'تم حذف ملف النسخ الاحتياطي بنجاح.',
        'generated'             => 'تم إنشاء ملف نسخ احتياطي جديد بنجاح.',
        'file_not_found'        => 'تعذر العثور على ملف النسخ الاحتياطي هذا على الملقم.',
        'restore_warning'       => 'نعم، استعادة. أقر بأن هذا سوف يستبدل أي بيانات موجودة حاليا في قاعدة البيانات. سيؤدي هذا أيضا إلى تسجيل جميع المستخدمين الحاليين (بما في ذلك أنت).',
        'restore_confirm'       => 'هل أنت متأكد من رغبتك في استعادة قاعدة البيانات الخاصة بك من :filename؟'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'حدث خطأ أثناء التطهير.',
        'validation_failed'     => 'تأكيد التطهير غير صحيح. الرجاء كتابة الكلمة "ديليت" في مربع التأكيد.',
        'success'               => 'تمت إزالة السجلات المحذوفة بنجاح.',
    ],
    'mail' => [
        'sending' => 'جارٍ إرسال بريد إلكتروني تجريبي...',
        'success' => 'تم إرسال البريد!',
        'error' => 'تعذر إرسال البريد.',
        'additional' => 'لم يتم توفير أي رسالة خطأ إضافية. تحقق من إعدادات البريد الخاص بك وسجل التطبيقات الخاص بك.'
    ],
    'ldap' => [
        'testing' => 'اختبار اتصال LDAP ، ربط واستعلام ...',
        '500' => '500 خطأ في الخادم. الرجاء التحقق من سجلات الخادم للحصول على مزيد من المعلومات.',
        'error' => 'حدث خطأ ما :(',
        'sync_success' => 'عينة من 10 مستخدمين عادت من خادم LDAP بناء على الإعدادات الخاصة بك:',
        'testing_authentication' => 'اختبار مصادقة LDAP...',
        'authentication_success' => 'تمت المصادقة على المستخدم ضد LDAP بنجاح!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'إرسال رسالة اختبار :app ...',
        'success' => 'يعمل تكامل :webhook_name الخاص بك!',
        'success_pt1' => 'نجاح! تحقق من ',
        'success_pt2' => ' قناة لرسالة الاختبار الخاصة بك، وتأكد من النقر فوق SAVE أدناه لتخزين الإعدادات الخاصة بك.',
        '500' => '500 خطأ في الخادم.',
        'error' => 'حدث خطأ ما. استجاب :app :error_message',
        'error_redirect' => 'خطأ: 301/302 :endpoint يرجع إعادة توجيه. لأسباب أمنية، نحن لا نتابع إعادة التوجيه. الرجاء استخدام نقطة النهاية الفعلية.',
        'error_misc' => 'حدث خطأ ما. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
