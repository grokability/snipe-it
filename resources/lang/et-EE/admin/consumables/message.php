<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Kuluvahendit pole olemas.',

    'create' => [
        'error' => 'Kuluvahendit ei loodud, proovi uuesti.',
        'success' => 'Kuluvahendi loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Kuluvahendit ei muudetud, proovi uuesti',
        'success' => 'Kuluvahendi muutmine õnnestus.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle kuluvahendi kustutada?',
        'error' => 'Kuluvahendi kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Kuluvahendi kustutamine õnnestus.',
    ],

    'checkout' => [
        'error' => 'Tarbitavat ei kontrollitud, proovige uuesti',
        'success' => 'Tarbitav kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja ei sobi. Palun proovi uuesti.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'checkin' => [
        'error' => 'Tarbitavat ei märgitud, proovige uuesti',
        'success' => 'Tarbitav kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja ei sobi. Palun proovi uuesti.',
    ],

];
