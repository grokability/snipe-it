<?php

return array(

    'support_url_help' => 'Променливите <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, и <code>{MODEL_NAME}</code> можат да бидат користени во вашето URL за да имате автоматско пополнување кога ги гледате средствата - на пример https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Производителот не постои.',
    'assoc_users'	 => 'Овој производител моментално е поврзан со барем еден модел и не може да се избрише. Ве молиме да ги ажурирате моделите за да не го користат овој производител и обидете се повторно. ',

    'create' => array(
        'error'   => 'Производителот не е креиран, обидете се повторно.',
        'success' => 'Производителот е креиран.'
    ),

    'update' => array(
        'error'   => 'Производителот не е ажуриран, обидете се повторно',
        'success' => 'Производителот е ажуриран.'
    ),

    'restore' => array(
        'error'   => 'Производителот не е вреатен, обидете се повторно',
        'success' => 'Производителот е вратен.'
    ),

    'delete' => array(
        'confirm' => 'Дали сте сигурни дека сакате да го избришете овој производител?',
        'error'   => 'Имаше проблем со бришење на производителот. Обидете се повторно.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
