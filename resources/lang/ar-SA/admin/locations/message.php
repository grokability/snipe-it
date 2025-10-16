<?php

return array(

    'does_not_exist' => 'الموقع غير موجود.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'هذا الموقع مرتبط حاليا بمادة عرض واحدة على الأقل ولا يمكن حذفها. يرجى تحديث مواد العرض لم تعد تشير إلى هذا الموقع ثم أعد المحاولة. ',
    'assoc_child_loc'	 => 'هذا الموقع هو حاليا أحد الوالدين لموقع طفل واحد على الأقل ولا يمكن حذفه. يرجى تحديث مواقعك لم تعد تشير إلى هذا الموقع ثم أعد المحاولة.',
    'assigned_assets' => 'الأصول المعينة',
    'current_location' => 'الموقع الحالي',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'لم يتم إنشاء الموقع، الرجاء المحاولة مرة أخرى.',
        'success' => 'تم إنشاء الموقع بنجاح.'
    ),

    'update' => array(
        'error'   => 'لم يتم تحديث الموقع، الرجاء المحاولة مرة أخرى',
        'success' => 'تم تحديث الموقع بنجاح.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'هل تريد بالتأكيد حذف هذا الموقع؟',
        'error'   => 'حدثت مشكلة أثناء حذف الموقع. حاول مرة اخرى.',
        'success' => 'تم حذف الموقع بنجاح.'
    )

);
