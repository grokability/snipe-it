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
        'confirm' => 'Ar tikrai norite panaikinti šį priedą?',
        'error' => 'Bandant panaikinti priedą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Priedas sėkmingai panaikintas.',
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
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
