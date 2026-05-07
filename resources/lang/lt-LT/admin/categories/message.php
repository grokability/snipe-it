<?php

return [

    'does_not_exist' => 'Tokios kategorijos nėra.',
    'assoc_models' => 'Ši kategorija šiuo metu yra susieta bent su vienu modeliu ir negali būti panaikinta. Atnaujinkite savo modelius, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',
    'assoc_items' => 'Ši kategorija šiuo metu yra susieta bent su vienu : asset_type ir negali būti panaikinta. Atnaujinkite savo :asset_type, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',

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
        'confirm' => 'Ar jūs tikrai norite panaikinti šią kategoriją?',
        'error' => 'Bandant panaikinti kategoriją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Kategorija sėkmingai ištrinta.',
        'bulk_success' => 'Kategorijos sėkmingai ištrintos.',
        'partial_success' => 'Kategorija sėkmingai ištrinta. Daugiau informacijos rasite žemiau. | :count kategorijos (-ų) buvo sėkmingai ištrintos. Daugiau informacijos rasite žemiau.',
    ],

];
