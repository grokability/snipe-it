<?php

return [

    'support_url_help' => 'URL-is saab kasutada muutujaid <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> ja <code>{MODEL_NAME}</code>, et need väärtused täidetaks vahendite vaatamisel automaatselt, näiteks https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Tootjat ei eksisteeri.',
    'assoc_users' => 'Selle tootjaga on seotud vähemalt üks mudel ja seda ei saa kustutada. Palun uuenda oma mudeleid, et need ei kasutaks seda tootjat ning seejärel proovi uuesti. ',

    'create' => [
        'error' => 'Tootjat ei loodud, proovi uuesti.',
        'success' => 'Tootja loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Tootjat ei uuendatud, palun proovi uuesti',
        'success' => 'Tootja uuendamine õnnestus.',
    ],

    'restore' => [
        'error' => 'Tootjad ei taastatud, proovi uuesti',
        'success' => 'Tootja taastati edukalt.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle tootja kustutada?',
        'error' => 'Tootja kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Tootja kustutamine õnnestus.',
        'bulk_success' => 'Tootja kustutamine õnnestus.|:count tootja kustutamine õnnestus.',
        'partial_success' => 'Tootja kustutamine õnnestus. Vaata lisateavet allpool. | :count tootja kustutamine õnnestus. Vaata lisateavet allpool.',
    ],

];
