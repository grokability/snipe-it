<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'مدل موجود نیست.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'این مدل در حال حاضر همراه یک یا بیشتر از یک دارایی است و نمی تواند حذف شود. لطفا دارایی ها را حذف کنید و سپس برای حذف کردن مجددا تلاش کنید. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'مدل ساخته نشده است، لطفا دوباره تلاش کنید.',
        'success' => 'مدل با موفقیت ساخته شد.',
        'duplicate_set' => 'یک مدل دارایی با آن نام، سازنده و شماره ی مدل در حال حاضر موجود است.',
    ),

    'update' => array(
        'error'   => 'مدل به روزرسانی نشده است، لطفا دوباره تلاش کنید',
        'success' => 'مدل با موفقیت به روز رسانی شد.',
    ),

    'delete' => array(
        'confirm'   => 'آیا شما مطمئن هستید که می خواهید این مدل دارایی را حذف کنید؟',
        'error'   => 'در زمان حذف کردن مدل، مشکلی وجود داشت. لطفا دوباره تلاش کنید.',
        'success' => 'مدل با موفقیت حذف شد.'
    ),

    'restore' => array(
        'error'   		=> 'مدل بازیابی نشد، لطفا دوباره تلاش کنید',
        'success' 		=> 'مدل با موفقیت بازیابی شد.'
    ),

    'bulkedit' => array(
        'error'   		=> 'هیچ فیلدی تغییر نکرده بود، بنابراین چیزی به روز نشد.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'هیچ مدلی انتخاب نشده بود، بنابراین هیچ چیز حذف نشد.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> 'مدل(های) :success_count حذف شدند، اما :fail_count حذف نشدند زیرا هنوز دارایی های مرتبط با آنها هستند.
'
    ),

);
