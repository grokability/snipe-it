<?php

return [

    'does_not_exist' => 'Tokio komponento nėra.',

    'create' => [
        'error' => 'Komponentas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Komponentas sėkmingai sukurtas.',
    ],

    'update' => [
        'error' => 'Komponentas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai atnaujintas.',
    ],

    'restore' => [
        'error' => 'Komponentas nebuvo atkurtas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai atkurtas.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite ištrinti šį komponentą?',
        'error' => 'Bandant ištrinti komponentą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Komponentas sėkmingai ištrintas.',
        'error_qty' => 'Kai kurie šio tipo komponentai vis dar yra išduoti. Paimkite juos ir bandykite dar kartą.',
    ],

    'checkout' => [
        'error' => 'Komponentas nebuvo išduotas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai išduotas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'unavailable' => 'Nepakanka komponentų: yra :remaining, o prašoma :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'Nebuvo pasirinkta jokių komponentų.',
        'no_action_selected' => 'Pasirinkite veiksmą iš išskleidžiamojo sąrašo.',
        'no_fields_updated' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'skipped_unauthorized' => 'Komponentai (:count) buvo praleisti, nes neturite teisės jų atnaujinti.',

        'update' => [
            'success' => 'Buvo atnaujintas :count komponentas.|Buvo atnaujinti :count komponentai.',
            'none_updated' => 'Pasirinkto komponento nepavyko atnaujinti. Daugiau informacijos rasite žemiau pateiktuose klaidų pranešimuose.| Nepavyko atnaujinti nė vieno iš pasirinktų komponentų. Daugiau informacijos rasite žemiau pateiktuose klaidų pranešimuose.',
        ],

        'delete' => [
            'success' => 'Buvo ištrintas :count komponentas.|Buvo ištrinti :count komponentai.',
            'partial_success' => 'Komponentas (:count) buvo ištrintas. Dėl likusių peržiūrėkite žemiau pateiktas klaidas.|Komponentai (:count) buvo ištrinti. Dėl likusių peržiūrėkite žemiau pateiktas klaidas.',
        ],

        'restore' => [
            'success' => 'Buvo atkurtas :count komponentas.|Buvo atkurti :count komponentai.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'Nepasirinkta jokių išduotų komponentų.',
            'success' => ':unit_count vienetas grąžintas per :count išdavimą.|:unit_count vienetai grąžinti per :count išdavimus.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponentas nebuvo paimtas, bandykite dar kartą',
        'success' => 'Komponentas sėkmingai paimtas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
    ],

];
