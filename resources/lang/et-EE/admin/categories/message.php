<?php

return [

    'does_not_exist' => 'Kategooria puudub.',
    'assoc_models' => 'Selle kategooriaga on seotud vähemalt üks mudel ja seda ei saa kustutada. Palun uuendage oma mudeleid, et need ei kasutaks seda kategooriat ning seejärel proovige uuesti. ',
    'assoc_items' => 'Selle kategooriaga on seotud vähemalt üks :asset_type ja seda ei saa kustutada. Palun uuendage oma :asset_typr, et too ei kasutaks seda kategooriat ning seejärel proovige uuesti. ',

    'create' => [
        'error' => 'Kategooriat ei loodud, proovi uuesti.',
        'success' => 'Kategooria loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Kategooriat ei uuendatud, proovige uuesti',
        'success' => 'Kategooria uuendamine õnnestus.',
        'cannot_change_category_type' => 'Kategooria tüüpi ei saa pärast loomist muuta',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle kategooria kustutada?',
        'error' => 'Kategooria kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Kategooria kustutamine õnnestus.',
        'bulk_success' => 'Kategooria kustutamine õnnestus.|:count kategooria kustutamine õnnestus.',
        'partial_success' => 'Kategooria kustutamine õnnestus. Vaata lisateavet allpool. | :count kategooria kustutamine õnnestus. Vaata lisateavet allpool.',
    ],

    'bulkedit' => [
        'warn' => 'Oled muutmas järgmise kategooria omadusi:|Oled muutmas järgmise :count kategooria omadusi:',
        'no_selection' => 'Vali muutmiseks vähemalt üks kategooria.',
        'no_changes' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'success' => 'Kategooria uuendamine õnnestus.|:count kategooria uuendamine õnnestus.',
    ],

];
