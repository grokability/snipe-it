<?php

return [

    'invalid_category_type' => 'Kategooria peab olema kulumaterjalide kategooria.',
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
        'bulk_success' => 'Kulumaterjali kustutamine õnnestus.|:count kulumaterjali kustutamine õnnestus.',
        'partial_success' => ':count kulumaterjali kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.|:count kulumaterjali kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.',
    ],

    'checkout' => [
        'error' => 'Tarbitavat ei kontrollitud, proovige uuesti',
        'success' => 'Tarbitav kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja ei sobi. Palun proovi uuesti.',
        'unavailable' => 'Väljastamiseks pole piisavalt kulumaterjale. Kontrolli allesjäänud kogust. ',
    ],

    'consumed' => [
        'success' => 'Väljastus märgiti tarbituks.',
        'bulk_success' => ':count väljastus märgiti tarbituks.|:count väljastust märgiti tarbituks.',
        'already' => 'See väljastus oli juba tarbituks märgitud.',
        'error' => 'Väljastuse tarbituks märkimine ebaõnnestus, proovi uuesti.',
        'no_rows_selected' => 'Ühtegi kehtivat väljastust pole valitud.',
    ],

    'checkin' => [
        'error' => 'Tarbitavat ei märgitud, proovige uuesti',
        'success' => 'Tarbitav kontrollitud edukalt.',
        'user_does_not_exist' => 'See kasutaja ei sobi. Palun proovi uuesti.',
    ],

    'restore' => [
        'error' => 'Kulumaterjali taastamine ebaõnnestus, proovi uuesti',
        'success' => 'Kulumaterjali taastamine õnnestus.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'Ühtegi kulumaterjali pole valitud.',
        'no_action_selected' => 'Vali rippmenüüst toiming.',
        'no_fields_updated' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'skipped_unauthorized' => ':count kulumaterjali jäeti vahele, sest sul puudub õigus neid uuendada.',

        'update' => [
            'success' => ':count kulumaterjal uuendati.|:count kulumaterjali uuendati.',
            'none_updated' => 'Valitud kulumaterjali uuendamine ebaõnnestus. Vaata üksikasju allolevatest vigadest.|Ühegi valitud kulumaterjali uuendamine ei õnnestunud. Vaata üksikasju allolevatest vigadest.',
        ],

        'restore' => [
            'success' => ':count kulumaterjal taastati.|:count kulumaterjali taastati.',
        ],
    ],
];
