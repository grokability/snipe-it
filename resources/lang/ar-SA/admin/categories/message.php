<?php

return array(

    'does_not_exist' => 'التصنيف غير موجود.',
    'assoc_models'	 => 'هذا التصنيف حاليا مرتبط مع موديل ولا يمكن حذفه. الرجاء القيام بتحديث الموديلات بحيث لا تكون مرتبطة بهذا التصنيف، ثم حاول مرة اخرى. ',
    'assoc_items'	 => 'هذه التصنيف مرتبط حاليا مع واحد على الأقل من أنواع الأصول :asset_type ولا يمكن حذفه. الرجاء تحديث نوع الأصل الخاص بك :asset_type حيث لا يكون مرتبط بهذا التصنيف، ثم حاول مرة أخرى. ',

    'create' => array(
        'error'   => 'لقد فشل إنشاء التصنيف، الرجاء المحاولة مرة أخرى.',
        'success' => 'تم إنشاء التصنيف بنجاح.'
    ),

    'update' => array(
        'error'   => 'لقد فشل تحديث التصنيف، الرجاء المحاولة مرة أخرى',
        'success' => 'تم تحديث التصنيف بنجاح.',
        'cannot_change_category_type'   => 'لا يمكنك تغيير نوع الفئة بمجرد إنشائها',
    ),

    'delete' => array(
        'confirm'                => 'هل أنت متأكد أنك تريد حذف هذا التصنيف؟',
        'error'                  => 'حدث خطأ أثناء محاولة حذف التصنيف. الرجاء المحاولة مرة أخرى.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
