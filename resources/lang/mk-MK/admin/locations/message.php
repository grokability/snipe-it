<?php

return array(

    'does_not_exist' => 'Локацијата не постои.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Оваа локација моментално е поврзана со барем едно основно средство и не може да се избрише. Ве молиме да ги ажурирате вашите основни средства за да не ја користите оваа локација и обидете се повторно. ',
    'assoc_child_loc'	 => 'Оваа локација моментално е родител на најмалку една локација и не може да се избрише. Ве молиме да ги ажурирате вашите локации повеќе да не ја користат оваа локација како родител и обидете се повторно. ',
    'assigned_assets' => 'Доделени средства',
    'current_location' => 'Моментална локација',
    'open_map' => 'Отвори во :map_provider_icon мапи',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Локацијата не е креирана, обидете се повторно.',
        'success' => 'Локацијата е успешно креирана.'
    ),

    'update' => array(
        'error'   => 'Локацијата не беше ажурирана, обидете се повторно',
        'success' => 'Локацијата е успешно ажурирана.'
    ),

    'restore' => array(
        'error'   => 'Локацијата не е обновена, ве молиме пробајте повторно',
        'success' => 'Локацијата е обновена успешно.'
    ),

    'delete' => array(
        'confirm'   	=> 'Дали сте сигурни дека сакате да ја избришете оваа локација?',
        'error'   => 'Имаше проблем со бришење на локацијата. Обидете се повторно.',
        'success' => 'Локацијата беше успешно избришана.'
    )

);
