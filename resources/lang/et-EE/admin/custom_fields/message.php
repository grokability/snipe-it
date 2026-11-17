<?php

return [

    'field' => [
        'invalid' => 'Seda välja ei eksisteeri.',
        'already_added' => 'Väli on juba lisatud',
        'none_selected' => 'Ühtegi välja pole valitud',

        'create' => [
            'error' => 'Väli ei loodud, proovige uuesti.',
            'success' => 'Väli loodi edukalt.',
            'assoc_success' => 'Väli edukalt lisatud fieldset\'i.',
        ],

        'update' => [
            'error' => 'Väli ei värskendatud, proovige uuesti',
            'success' => 'Väli värskendati edukalt.',
        ],

        'delete' => [
            'confirm' => 'Kas olete kindel, et soovite kustutada selle välja?',
            'error' => 'Välja jäeti välja küsimus. Palun proovi uuesti.',
            'success' => 'Väli kustutati edukalt.',
            'in_use' => 'Väli on endiselt kasutusel.',
            'in_use_named' => ':name on endiselt seotud vähemalt ühe väljakomplektiga ning seda ei saa kustutada.',
            'bulk_success' => 'Välja kustutamine õnnestus.|:count välja kustutamine õnnestus.',
            'partial_success' => ':count välja kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.|:count välja kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset ei ole olemas',

        'create' => [
            'error' => 'Fieldset ei loodud, proovige uuesti.',
            'success' => 'Fieldset loodud edukalt.',
        ],

        'update' => [
            'error' => 'Fieldset ei värskendatud, proovige uuesti',
            'success' => 'Fieldset uuendati edukalt.',
        ],

        'delete' => [
            'confirm' => 'Kas olete kindel, et soovite kustutada selle väljadestiivi?',
            'error' => 'Väljaku seade kustutati. Palun proovi uuesti.',
            'success' => 'Väljatrükk on edukalt kustutatud.',
            'in_use' => 'Fieldset on endiselt kasutusel.',
            'in_use_named' => ':name on endiselt seotud vähemalt ühe vahendimudeliga ning seda ei saa kustutada.',
            'bulk_success' => 'Väljakomplekti kustutamine õnnestus.|:count väljakomplekti kustutamine õnnestus.',
            'partial_success' => ':count väljakomplekti kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.|:count väljakomplekti kustutamine õnnestus, kuid teisi ei saanud kustutada. Vaata üksikasju allpool.',
        ],

        'clone' => [
            'success' => 'Väljakomplekti kloonimine õnnestus.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Väljakomplekti vaikeväärtuste valideerimisel tekkis viga.',

    ],

];
