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

    'restore' => [
        'error' => 'Komponendi taastamine ebaõnnestus, proovi uuesti',
        'success' => 'Komponendi taastamine õnnestus.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle komponendi kustutada?',
        'error' => 'Komponendi kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Komponendi kustutamine õnnestus.',
        'error_qty' => 'Osa seda tüüpi komponente on endiselt väljastatud. Tagasta need ja proovi uuesti.',
    ],

    'checkout' => [
        'error' => 'Komponenti ei kontrollitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt välja võetud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
        'unavailable' => 'Komponente pole piisavalt: alles on :remaining, taotleti :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'Ühtegi komponenti pole valitud.',
        'no_action_selected' => 'Vali rippmenüüst toiming.',
        'no_fields_updated' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'skipped_unauthorized' => ':count komponenti jäeti vahele, sest sul puudub õigus neid uuendada.',

        'update' => [
            'success' => ':count komponent uuendati.|:count komponenti uuendati.',
            'none_updated' => 'Valitud komponendi uuendamine ebaõnnestus. Vaata üksikasju allolevatest vigadest.|Ühegi valitud komponendi uuendamine ei õnnestunud. Vaata üksikasju allolevatest vigadest.',
        ],

        'delete' => [
            'success' => ':count komponent kustutati.|:count komponenti kustutati.',
            'partial_success' => ':count komponent kustutati. Ülejäänute vead leiad allpool.|:count komponenti kustutati. Ülejäänute vead leiad allpool.',
        ],

        'restore' => [
            'success' => ':count komponent taastati.|:count komponenti taastati.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'Ühtegi väljastatud komponenti pole valitud.',
            'success' => ':unit_count ühik tagastati :count väljastuse kohta.|:unit_count ühikut tagastati :count väljastuse kohta.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponent ei olnud märgitud, palun proovige uuesti',
        'success' => 'Komponent on edukalt registreeritud.',
        'user_does_not_exist' => 'Vale kasutaja. Palun proovi uuesti.',
    ],

];
