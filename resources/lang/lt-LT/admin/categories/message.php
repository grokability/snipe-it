<?php

return array(

    'does_not_exist' => 'Tokios kategorijos nėra.',
    'assoc_models'	 => 'Ši kategorija šiuo metu yra susieta bent su vienu modeliu ir negali būti panaikinta. Atnaujinkite savo modelius, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',
    'assoc_items'	 => 'Ši kategorija šiuo metu yra susieta bent su vienu : asset_type ir negali būti panaikinta. Atnaujinkite savo :asset_type, kad nebebūtų sąsajos su šia kategorija, ir bandykite dar kartą. ',

    'create' => array(
        'error'   => 'Kategorijos sukurti nepavyko, badykite dar kartą.',
        'success' => 'Kategorija sukurta sėkmingai.'
    ),

    'update' => array(
        'error'   => 'Kategorija nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Kategorija atnaujinta sėkmingai.',
        'cannot_change_category_type'   => 'Negalite pakeisti kategorijos tipo po to, kai ji jau buvo sukurta',
    ),

    'delete' => array(
        'confirm'                => 'Ar jūs tikrai norite panaikinti šią kategoriją?',
        'error'                  => 'Bandant panaikinti kategoriją įvyko klaida. Bandykite dar kartą.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
