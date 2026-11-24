<?php

return [

    'field' => [
        'invalid' => 'Tokio lauko nėra.',
        'already_added' => 'Laukas jau pridėtas',
        'none_selected' => 'Nepasirinktas joks laukas',

        'create' => [
            'error' => 'Laukas nebuvo sukurtas, bandykite dar kartą.',
            'success' => 'Laukas sėkmingai sukurtas.',
            'assoc_success' => 'Laukas sėkmingai įtrauktas į laukų rinkinį.',
        ],

        'update' => [
            'error' => 'Laukas nebuvo atnaujintas, bandykite dar kartą',
            'success' => 'Laukas sėkmingai atnaujintas.',
        ],

        'delete' => [
            'confirm' => 'Ar tikrai norite ištrinti šį lauką?',
            'error' => 'Bandant ištrinti lauką įvyko klaida. Bandykite dar kartą.',
            'success' => 'Laukas sėkmingai ištrintas.',
            'in_use' => 'Laukas yra naudojamas.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Laukas sėkmingai ištrintas.|Laukai (:count) sėkmingai ištrinti.',
            'partial_success' => ':count laukas buvo sėkmingai ištrintas, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.|Laukai (:count) buvo sėkmingai ištrinti, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Tokio laukų rinkinio nėra',

        'create' => [
            'error' => 'Laukas nebuvo sukurtas, bandykite dar kartą.',
            'success' => 'Laukų rinkinys sėkmingai sukurtas.',
        ],

        'update' => [
            'error' => 'Laukų rinkinys nebuvo atnaujintas, bandykite dar kartą',
            'success' => 'Laukų rinkinys sėkmingai atnaujintas.',
        ],

        'delete' => [
            'confirm' => 'Ar tikrai norite ištrinti šį laukų rinkinį?',
            'error' => 'Bandant ištrinti laukų rinkinį įvyko klaida. Bandykite dar kartą.',
            'success' => 'Laukų rinkinys sėkmingai ištrintas.',
            'in_use' => 'Laukų rinkinys yra naudojamas.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Laukų rinkinys sėkmingai ištrintas.|Laukų rinkiniai (:count) sėkmingai ištrinti.',
            'partial_success' => ':count laukų rinkinys buvo sėkmingai ištrintas, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.|Laukų rinkiniai (:count) buvo sėkmingai ištrinti, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.',
        ],

        'clone' => [
            'success' => 'Laukų rinkinys sėkmingai klonuotas.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Klaida tikrinant numatytąsias laukų reikšmes.',

    ],

];
