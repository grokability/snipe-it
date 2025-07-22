<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'الأصل غير موجود.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'ذالك الأصل غير موجود أو غير قابل للطلب.',
    'assoc_users'	 	 => 'هذا الأصل مخرج حاليا لمستخدم ولا يمكن حذفه. يرجى التحقق من الأصل أولا، ثم حاول الحذف مرة أخرى. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'لم يتم إنشاء الأصل، يرجى إعادة المحاولة. :(',
        'success' 		=> 'تم إنشاء الأصل بنجاح. :)',
        'success_linked' => 'تم إنشاء الأصل مع العلامة :tag بنجاح. <strong><a href=":link" style="color: white;">انقر هنا لعرض</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'لم يتم تحديث الأصل، يرجى إعادة المحاولة',
        'success' 			=> 'تم تحديث الأصل بنجاح.',
        'encrypted_warning' => 'تم تحديث الأصل بنجاح، ولكن الحقول المخصصة المشفرة لم تكن بسبب الأذونات',
        'nothing_updated'	=>  'لم يتم اختيار أي حقول، لذلك لم يتم تحديث أي شيء.',
        'no_assets_selected'  =>  'لم يتم اختيار أي أصول، لذلك لم يتم تحديث أي شيء.',
        'assets_do_not_exist_or_are_invalid' => 'لا يمكن تحديث الأصول المحددة.',
    ],

    'restore' => [
        'error'   		=> 'لم تتم استعادة الأصل، يرجى إعادة المحاولة',
        'success' 		=> 'تمت استعادة الأصل بنجاح.',
        'bulk_success' 		=> 'تمت استعادة الأصل بنجاح.',
        'nothing_updated'   => 'ولم يتم اختيار أي أصول، وبالتالي لم يتم استعادة أي شيء.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'تم تسجيل تدقيق الأصل بنجاح.',
    ],


    'deletefile' => [
        'error'   => 'لم يتم حذف الملف. الرجاء المحاولة مرة اخرى.',
        'success' => 'تم حذف الملف بنجاح.',
    ],

    'upload' => [
        'error'   => 'لم يتم تحميل الملف. حاول مرة اخرى.',
        'success' => 'تم تحميل الملف بنجاح.',
        'nofiles' => 'لم تحدد أي ملفات للتحميل، أو أن الملف الذي تحاول تحميله كبير جدا',
        'invalidfiles' => 'واحد أو أكثر من الملفات كبيرة جداً أو نوع الملف غير مسموح. أنواع الملفات المسموح بها هي png, gif, jpg, doc, docx, pdf, و txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'لم يتم استيراد بعض العناصر بشكل صحيح.',
        'errorDetail'           => 'لم يتم استيراد العناصر التالية بسبب الأخطاء.',
        'success'               => 'تم استيراد الملف الخاص بك',
        'file_delete_success'   => 'تم حذف ملفك بنجاح',
        'file_delete_error'      => 'تعذر حذف الملف',
        'file_missing' => 'الملف المحدد مفقود',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'واحدة أو أكثر من السمات في الصف الترويجي تحتوي على أحرف UTF-8 سيئة',
        'content_row_has_malformed_characters' => 'واحدة أو أكثر من السمات في الصف الأول من المحتوى تحتوي على أحرف UTF-8 سيئة',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'هل تريد بالتأكيد حذف هذا الأصل؟',
        'error'   		=> 'حدثت مشكلة أثناء حذف هذا الأصل. حاول مرة اخرى.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'لم يتم اختيار أي أصول، لذلك لم يتم حذف أي شيء.',
        'success' 		=> 'تم حذف الأصل بنجاح.',
    ],

    'checkout' => [
        'error'   		=> 'لم يتم اخراج الأصل، يرجى إعادة المحاولة',
        'success' 		=> 'تم اخراج الأصل بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صالح. حاول مرة اخرى.',
        'not_available' => 'هذا الأصل غير متاح للخروج!',
        'no_assets_selected' => 'يجب عليك تحديد أصل واحد على الأقل من القائمة',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'لم يتم ادخال الأصل، يرجى إعادة المحاولة',
        'success' 		=> 'تم ادخال الأصل بنجاح.',
        'user_does_not_exist' => 'هذا المستخدم غير صالح. حاول مرة اخرى.',
        'already_checked_in'  => 'تم ادخال هذا الأصل مسبقا.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'إلغاء طلب هذا العنصر',
    ],

];
