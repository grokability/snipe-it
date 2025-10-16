<?php

return array(

    'does_not_exist' => 'مکان وجود ندارد.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'این مکان در حال حاضر همراه با حداقل یک دارایی است و قادر به حذف نمی شود. لطفا بروز دارایی های خود را به دیگر این مکان مرجع و دوباره امتحان کنید. ',
    'assoc_child_loc'	 => 'این مکان در حال حاضر پدر و مادر کودک حداقل یک مکان است و قادر به حذف نمی شود. لطفا به روز رسانی مکان خود را به دیگر این مکان مرجع و دوباره امتحان کنید. ',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'مکان ایجاد نشد،دوباره سعی کنید.',
        'success' => 'مکان با موفقیت ایجاد شد.'
    ),

    'update' => array(
        'error'   => 'مکان بروزرسانی نشد،دوباره سعی کنید',
        'success' => 'مکان با موفقیت به روز رسانی شد.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'آیا مطمئن هستید که می خواهید این مکان را حذف کنید؟',
        'error'   => 'یک مشکل در حذف مکان وجود دارد،دوباره سعی کنید.',
        'success' => 'این مکان با موفقیت حذف شد.'
    )

);
