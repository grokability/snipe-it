<?php

return [

    'field' => [
        'invalid' => 'Toto pole neexistuje.',
        'already_added' => 'Pole už bolo pridané',
        'none_selected' => 'Nebolo zvolené žiadne pole',

        'create' => [
            'error' => 'Pole nebolo pridané, prosím skúste znovu.',
            'success' => 'Pole bolo úspešne pridané.',
            'assoc_success' => 'Pole bolo úspešne pridané do skupiny polí.',
        ],

        'update' => [
            'error' => 'Pole nebolo upravené, skúste prosím znovu',
            'success' => 'Pole bolo úspešne upravené.',
        ],

        'delete' => [
            'confirm' => 'Ste si istý, že chcete zmazať toto pole?',
            'error' => 'Pri mazaní pola nastala chyba. Skúste prosím znovu.',
            'success' => 'Pole bolo úspešne zmazané.',
            'in_use' => 'Pole sa stále používa.',
            'in_use_named' => ':name – toto pole je stále priradené minimálne k jednej skupine polí, preto ho nie je možné odstrániť.',
            'bulk_success' => 'Pole bolo úspešne odstránené.|:count polí bolo úspešne odstránených.',
            'partial_success' => ':count pole bolo úspešne odstránené, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.|:count polí bolo úspešne odstránených, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Skupina polí neexistuje',

        'create' => [
            'error' => 'Skupina polí nebola vytvorená, skúste prosím znovu.',
            'success' => 'Skupina polí bola úspešne vytvorená.',
        ],

        'update' => [
            'error' => 'Skupina polí nebola upravená, skúste znovu',
            'success' => 'Skupina polí bola úspešne upravená.',
        ],

        'delete' => [
            'confirm' => 'Ste si istý, že chcete odstrániť túto skupinu polí?',
            'error' => 'Pri mazaní skupiny polí nastala chyba. Skúste znovu.',
            'success' => 'Skupina polí bola úspešne zmazaná.',
            'in_use' => 'Skupina polí sa stále používa.',
            'in_use_named' => ':name – táto skupina polí je stále priradená minimálne k jednému modelu majetku, preto ju nie je možné odstrániť.',
            'bulk_success' => 'Skupina polí bola úspešne odstránená.|:count skupín polí bolo úspešne odstránených.',
            'partial_success' => ':count skupina polí bola úspešne odstránená, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.|:count skupín polí bolo úspešne odstránených, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.',
        ],

        'clone' => [
            'success' => 'Skupina polí bola úspešne duplikovaná.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Chyba pri overovaní predvolených hodnôt množiny polí.',

    ],

];
