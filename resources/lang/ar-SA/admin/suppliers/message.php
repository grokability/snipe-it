<?php

return array(

    'deleted' => 'المورد المحذوف',
    'does_not_exist' => 'المورد غير مجود.',


    'create' => array(
        'error'   => 'لم يتم إنشاء المورد، الرجاء المحاولة مرة أخرى.',
        'success' => 'تم إنشاء المورد بنجاح.'
    ),

    'update' => array(
        'error'   => 'لم يتم تحديث المورد، يرجى إعادة المحاولة',
        'success' => 'تم تحديث المورد بنجاح.'
    ),

    'delete' => array(
        'confirm'   => 'هل أنت متأكد من رغبتك في حذف هذا المورد؟',
        'error'   => 'حدثت مشكلة أثناء حذف المورد. حاول مرة اخرى.',
        'success' => 'تم حذف المورد بنجاح.',
        'assoc_assets'	 => 'هذا المورد مرتبط حاليا مع :asset_count من الأصول على الاقل ولا يمكن حذفه. الرجاء تحديث الأصول لكي لا تشير الى هذا المورد ثم حاول مرة أخرى. ',
        'assoc_licenses'	 => 'هذا المورد مرتبط حاليا مع :licenses_count من التراخيص ولا يمكن حذفه. الرجاء تحديث التراخيص الخاصة بك لكي لا تشير الى هذا المورد وحاول مرة أخرى. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
