<?php

return array(

    'support_url_help' => 'Променливите <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, и <code>{MODEL_NAME}</code> могат да бъдат използвани в URL, за да бъдат автоматично попълнени за активите - например https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Несъществуващ производител.',
    'assoc_users'	 => 'Този производител е асоцииран с поне един от моделите и не може да бъде изтрит. Моля, променете връзките на моделите по отношение на този производител и опитайте отново. ',

    'create' => array(
        'error'   => 'Производителят не беше създаден. Моля, опитайте отново.',
        'success' => 'Производителят е създаден.'
    ),

    'update' => array(
        'error'   => 'Производителят не е обновен. Моля, опитайте отново',
        'success' => 'Производителят е обновен.'
    ),

    'restore' => array(
        'error'   => 'Производителят не беше възстановен. Моля, опитайте отново',
        'success' => 'Производителят възстановен успешно.'
    ),

    'delete' => array(
        'confirm' => 'Сигурни ли сте, че искате да изтриете този производител?',
        'error'   => 'Възникна проблем при изтриването на проиводителя. Моля, опитайте отново.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
