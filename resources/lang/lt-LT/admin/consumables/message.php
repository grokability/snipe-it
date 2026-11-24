<?php

return [

    'invalid_category_type' => 'Kategorija turi būti eksploatacinių medžiagų kategorija.',
    'does_not_exist' => 'Tokios eksploatacinės medžiagos nėra.',

    'create' => [
        'error' => 'Eksploatacinė medžiaga nebuvo sukurta, bandykite dar kartą.',
        'success' => 'Eksploatacinė medžiaga sėkmingai sukurta.',
    ],

    'update' => [
        'error' => 'Eksploatacinė medžiaga nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Eksploatacinė medžiaga sėkmingai atnaujinta.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite ištrinti šią eksploatacinę medžiagą?',
        'error' => 'Bandant ištrinti eksploatacinę medžiagą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Eksploatacinė medžiaga sėkmingai ištrinta.',
        'bulk_success' => 'Eksploatacinė medžiaga sėkmingai ištrinta.|Eksploatacinės medžiagos (:count) sėkmingai ištrintos.',
        'partial_success' => ':count eksploatacinė medžiaga buvo sėkmingai ištrinta, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.|Eksploatacinės medžiagos (:count) buvo sėkmingai ištrintos, tačiau kitų ištrinti nepavyko. Daugiau informacijos rasite žemiau.',
    ],

    'checkout' => [
        'error' => 'Eksploatacinė medžiaga nebuvo išduota, bandykite dar kartą',
        'success' => 'Eksploatacinė medžiaga sėkmingai išduota.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'unavailable' => 'Šiam išdavimui nepakanka eksploatacinių medžiagų. Patikrinkite likutį. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Eksploatacinė medžiaga nebuvo paimta, bandykite dar kartą',
        'success' => 'Eksploatacinė medžiaga sėkmingai paimta.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Eksploatacinė medžiaga sėkmingai atkurta.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'Nepasirinkta jokių eksploatacinių medžiagų.',
        'no_action_selected' => 'Pasirinkite veiksmą iš išskleidžiamojo sąrašo.',
        'no_fields_updated' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
