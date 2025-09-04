<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'دارایی وجود ندارد.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'آن دارایی وجود ندارد یا قابل درخواست نیست.
',
    'assoc_users'	 	 => 'این دارایی در حال حاضر به یک کاربر چک کردن و پاک نمی شود. لطفا دارایی در اولین بار چک کنید، و سپس سعی کنید دوباره حذف کنید.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'دارایی ساخته نشده است، لطفا دوباره تلاش کنید.',
        'success' 		=> 'دارایی موفقیت ایجاد شده است. :)',
        'success_linked' => 'Asset with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'دارایی به روز نیست، لطفا دوباره امتحان کنید',
        'success' 			=> 'دارایی ها با موفقیت به روز رسانی.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'هیچ زمینه، انتخاب شدند تا هیچ چیز به روز شد.',
        'no_assets_selected'  =>  'هیچ دارایی انتخاب نشد، بنابراین چیزی به‌روزرسانی نشد.
',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'دارایی بازیابی نشد، لطفا دوباره تلاش کنید',
        'success' 		=> 'دارایی با موفقیت بازیابی شد.',
        'bulk_success' 		=> 'دارایی با موفقیت بازیابی شد.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'حسابرسی املاک با موفقیت وارد شد',
    ],


    'deletefile' => [
        'error'   => 'فایل حذف نمی شود. لطفا دوباره تلاش کنید.',
        'success' => 'فایل با موفقیت حذف شده است.',
    ],

    'upload' => [
        'error'   => 'فایل) آپلود نیست. لطفا دوباره تلاش کنید.',
        'success' => 'فایل (موفقیت آپلود شد.',
        'nofiles' => 'شما هر فایل برای آپلود انتخاب کنید، و یا فایل شما در حال تلاش برای آپلود بیش از حد بزرگ است',
        'invalidfiles' => 'یک یا بیشتر از فایل های خود را بیش از حد بزرگ است یا یک نوع فایل است که مجاز است. انواع فایل های مجاز عبارتند از PNG، GIF، JPG، DOC، DOCX، PDF، TXT و.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'بعضی از موارد به درستی وارد نشدند.',
        'errorDetail'           => 'موارد زیر به علت خطا وارد نشده است.',
        'success'               => 'فایل شما وارد شده است',
        'file_delete_success'   => 'فایل شما با موفقیت حذف شده است',
        'file_delete_error'      => 'فایل قابل حذف نشد',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'آیا شما مطمئن هستید که می خواهید این تنظیمات دارایی را حذف کنید؟',
        'error'   		=> 'اشکال در حذف دارایی.لطفا دوباره تلاش کنید.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'هیچ دارایی انتخاب نشده بود، بنابراین هیچ چیز حذف نشد.',
        'success' 		=> 'دارایی با موفقیت حذف شد.',
    ],

    'checkout' => [
        'error'   		=> 'دارایی در بررسی نیست، لطفا دوباره امتحان کنید',
        'success' 		=> 'دارایی را بررسی کنید موفقیت.',
        'user_does_not_exist' => 'کاربر نامعتبر است لطفا دوباره امتحان کنید.',
        'not_available' => 'این دارایی برای پرداخت در دسترس نیست!',
        'no_assets_selected' => 'شما حداقل باید یک دارایی از لیست انتخاب کنید',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'دارایی در بررسی نیست، لطفا دوباره امتحان کنید',
        'success' 		=> 'دارایی ها با موفقیت در بررسی.',
        'user_does_not_exist' => 'آن کاربر نامعتبر است. لطفا دوباره سعی کنید.',
        'already_checked_in'  => 'دارایی ها که در حال حاضر انتخاب شده است.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
