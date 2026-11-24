<?php

return [

    'does_not_exist' => 'Tokios kategorijos nėra.',
    'assoc_models' => 'Ši kategorija šiuo metu yra susieta bent su vienu modeliu ir negali būti ištrinta. Atnaujinkite savo modelius, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',
    'assoc_items' => 'Ši kategorija šiuo metu yra susieta bent su vienu :asset_type ir negali būti ištrinta. Atnaujinkite savo :asset_type, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',

    'create' => [
        'error' => 'Kategorijos sukurti nepavyko, badykite dar kartą.',
        'success' => 'Kategorija sėkmingai sukurta.',
    ],

    'update' => [
        'error' => 'Kategorija nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Kategorija sėkmingai atnaujinta.',
        'cannot_change_category_type' => 'Negalite pakeisti kategorijos tipo po to, kai ji jau buvo sukurta',
    ],

    'delete' => [
        'confirm' => 'Ar jūs tikrai norite ištrinti šią kategoriją?',
        'error' => 'Bandant ištrinti kategoriją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Kategorija sėkmingai ištrinta.',
        'bulk_success' => 'Kategorija sėkmingai ištrinta.|Kategorijos (:count) sėkmingai ištrintos.',
        'partial_success' => 'Kategorija sėkmingai ištrinta. Daugiau informacijos rasite žemiau. | Kategorijos (:count) buvo sėkmingai ištrintos. Daugiau informacijos rasite žemiau.',
    ],

    'bulkedit' => [
        'warn' => 'Ketinate redaguoti šios kategorijos ypatybes:|Ketinate redaguoti šių :count kategorijų ypatybes:',
        'no_selection' => 'Turite pasirinkti bent vieną kategoriją, kurią norite redaguoti.',
        'no_changes' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'success' => 'Kategorija sėkmingai atnaujinta. |Kategorijos (:count) sėkmingai atnaujintos.',
    ],

];
