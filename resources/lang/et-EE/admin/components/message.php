<?php

return [

    'does_not_exist' => 'Komponenti pole olemas.',

    'create' => [
        'error' => 'Komponenti ei loodud, proovi uuesti.',
        'success' => 'Komponendi loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Komponenti ei värskendatud, proovige uuesti',
        'success' => 'Komponendi muutmine õnnestus.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle komponendi kustutada?',
        'error' => 'Komponendi kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Komponendi kustutamine õnnestus.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Komponenti ei kontrollitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt välja võetud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'checkin' => [
        'error' => 'Komponent ei olnud märgitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt registreeritud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
    ],

];
