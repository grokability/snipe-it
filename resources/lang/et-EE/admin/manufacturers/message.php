<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Tootjat ei eksisteeri.',
    'assoc_users'	 => 'Selle tootjaga on seotud vähemalt üks mudel ja seda ei saa kustutada. Palun uuenda oma mudeleid, et need ei kasutaks seda tootjat ning seejärel proovi uuesti. ',

    'create' => array(
        'error'   => 'Tootjat ei loodud, proovi uuesti.',
        'success' => 'Tootja loomine õnnestus.'
    ),

    'update' => array(
        'error'   => 'Tootjat ei uuendatud, palun proovi uuesti',
        'success' => 'Tootja uuendamine õnnestus.'
    ),

    'restore' => array(
        'error'   => 'Tootjad ei taastatud, proovi uuesti',
        'success' => 'Tootja taastati edukalt.'
    ),

    'delete' => array(
        'confirm' => 'Kas oled kindel, et soovid selle tootja kustutada?',
        'error'   => 'Tootja kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
