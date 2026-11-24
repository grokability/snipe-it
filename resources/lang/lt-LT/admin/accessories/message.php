<?php

return [

    'does_not_exist' => 'Priedas [:id] neegzistuoja.',
    'not_found' => 'Priedas nerastas.',
    'assoc_users' => 'Naudotojams išduotų šio priedo vienetų skaičius - :count. Paimkite išduotus priedus ir bandykite dar kartą. ',

    'create' => [
        'error' => 'Priedas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Priedas sėkmingai sukurtas.',
    ],

    'update' => [
        'error' => 'Priedas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Priedas sėkmingai atnaujintas.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite ištrinti šį priedą?',
        'error' => 'Bandant ištrinti priedą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Priedas sėkmingai ištrintas.',
        'bulk_success' => 'Priedas sėkmingai ištrintas.|Priedai (:count) sėkmingai ištrinti.',
        'partial_success' => ':count priedas buvo sėkmingai ištrintas, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.|Priedai (:count) buvo sėkmingai ištrinti, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.',
    ],

    'checkout' => [
        'error' => 'Priedo nepavyko išduoti, bandykite dar kartą',
        'success' => 'Priedas sėkmingai išduotas.',
        'unavailable' => 'Priedo išduoti negalima. Patikrinkite likutį',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'checkout_qty' => [
            'lte' => 'Šiuo metu yra tik vienas šio tipo priedas, o jūs bandote išduoti :checkout_qty. Pakoreguokite išduodamą arba bendrą šio priedo atsargų kiekį ir bandykite dar kartą.|Yra :number_currently_remaining šio priedo vienetai (-ų), o jūs bandote išduoti :checkout_qty. Pakoreguokite išduodamą arba bendrą šio priedo atsargų kiekį ir bandykite dar kartą.',
        ],

    ],

    'checkin' => [
        'error' => 'Priedas nebuvo paimtas, bandykite dar kartą',
        'success' => 'Priedas sėkmingai paimtas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
    ],

    'restore' => [
        'error' => 'Priedo atkurti nepavyko, bandykite dar kartą',
        'success' => 'Priedas sėkmingai atkurtas.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'Nepasirinkta jokių priedų.',
        'no_action_selected' => 'Pasirinkite veiksmą iš išskleidžiamojo sąrašo.',
        'no_fields_updated' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'skipped_unauthorized' => 'Priedai (:count) buvo praleisti, nes neturite teisės jų atnaujinti.',

        'update' => [
            'success' => 'Buvo atnaujintas :count priedas.|Buvo atnaujinti :count priedai.',
            'none_updated' => 'Pasirinkto priedo nepavyko atnaujinti. Daugiau informacijos rasite žemiau pateiktuose klaidų pranešimuose.| Nepavyko atnaujinti nė vieno iš pasirinktų priedų. Daugiau informacijos rasite žemiau pateiktuose klaidų pranešimuose.',
        ],

        'restore' => [
            'success' => 'Buvo atkurtas :count priedas.|Buvo atkurti :count priedai.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'Nepasirinkta jokių išduotų priedų.',
            'success' => 'Grąžintas :count priedo išdavimas.|Grąžinti :count priedų išdavimai.',
        ],

        'checkout' => [
            'nothing_selectable' => 'None of the selected accessories can be checked out. Archived accessories and accessories with no remaining stock are excluded.',
            'excluded_notice' => 'The following accessories were excluded from this checkout because they are archived or have no remaining stock:',
            'skipped_archived' => ':name was skipped because it is archived.',
            'skipped_unavailable' => ':name was skipped because no units are currently available.',
            'skipped_company_mismatch' => ':name was skipped because it belongs to a different company (:item_company) than the selected target.',
            'summary' => ':checked_out of :total accessories were checked out.',
        ],
    ],
];
