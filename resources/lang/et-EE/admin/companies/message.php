<?php

return [
    'does_not_exist' => 'Ettevõtet pole olemas.',
    'deleted' => 'Kustutatud ettevõte',
    'assoc_users' => 'See ettevõte on praegu seotud vähemalt ühe muu kirjega (asukoht, kasutaja, litsents, vahend jne) Snipe-IT-s ning seda ei saa kustutada. Uuenda mudeleid nii, et need ei viitaks enam sellele ettevõttele, ja proovi uuesti. ',
    'create' => [
        'error' => 'Ettevõtet ei loodud, proovi uuesti.',
        'success' => 'Ettevõtte loomine õnnestus.',
    ],
    'update' => [
        'error' => 'Ettevõtet ei uuendatud, proovige uuesti',
        'success' => 'Ettevõtte uuendamine õnnestus.',
    ],
    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle ettevõtte kustutada?',
        'error' => 'Ettevõtte kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Ettevõtte kustutamine õnnestus.',
        'bulk_success' => 'Ettevõtte kustutamine õnnestus.|:count ettevõtte kustutamine õnnestus.',
        'partial_success' => 'Ettevõtte kustutamine õnnestus. Vaata lisateavet allpool. | :count ettevõtte kustutamine õnnestus. Vaata lisateavet allpool.',
    ],
    'webhook_fields_require_superuser' => 'Veebikonksu seadistusvälju (:fields) saab muuta ainult superkasutaja.',
];
