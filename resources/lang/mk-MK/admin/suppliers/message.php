<?php

return array(

    'deleted' => 'Избришан добавувач',
    'does_not_exist' => 'Добавувачот не постои.',


    'create' => array(
        'error'   => 'Добавувачот не е креиран, обидете се повторно.',
        'success' => 'Добавувачот е креиран.'
    ),

    'update' => array(
        'error'   => 'Добавувачот не е ажуриран, обидете се повторно',
        'success' => 'Добавувачот е ажуриран.'
    ),

    'delete' => array(
        'confirm'   => 'Дали сте сигурни дека сакате да го избришете добавувачот?',
        'error'   => 'Имаше проблем со бришење на добавувачот. Обидете се повторно.',
        'success' => 'Добавувачот е избришан.',
        'assoc_assets'	 => 'Добавувачот моментално е поврзан со :asset_count основни средства и не може да се избрише. Ве молиме да ги ажурирате основните средства за да не го користат овој добавувач и обидете се повторно. ',
        'assoc_licenses'	 => 'Добавувачот моментално е поврзан со :licenses_count лиценци и не може да се избрише. Ве молиме да ги ажурирате лиценците за да не го користат овој добавувач и обидете се повторно. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
