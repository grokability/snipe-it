<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Ražotājs neeksistē.',
    'assoc_users'	 => 'Šis ražotājs pašlaik ir saistīts ar vismaz vienu modeli, un to nevar izdzēst. Lūdzu, atjauniniet savus modeļus, lai vairs nerādītu šo ražotāju, un mēģiniet vēlreiz.',

    'create' => array(
        'error'   => 'Ražotājs netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Ražotājs veiksmīgi izveidots.'
    ),

    'update' => array(
        'error'   => 'Ražotājs netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Ražotājs tika veiksmīgi atjaunināts.'
    ),

    'restore' => array(
        'error'   => 'Ražotāja dati nav atjaunoti, lūdzu mēģiniet vēlreiz',
        'success' => 'Ražotāja dati atjaunoti veiksmīgi.'
    ),

    'delete' => array(
        'confirm' => 'Vai tiešām vēlaties dzēst šo ražotāju?',
        'error'   => 'Radās problēma, izdzēšot ražotāju. Lūdzu mēģiniet vēlreiz.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
