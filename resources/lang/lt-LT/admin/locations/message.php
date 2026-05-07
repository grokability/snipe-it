<?php

return [

    'does_not_exist' => 'Tokios vietos nėra.',
    'assoc_users' => 'Šios vietos negalima panaikinti, nes ji yra bent vieno daikto ar naudotojo vieta, jai yra priskirtas turtas arba ji yra nurodyta kaip pagrindinė kitos vietos vieta. Atnaujinkite savo įrašus, kad jie nebeturėtų sąsajų su šia vieta ir bandykite dar kartą ',
    'assoc_assets' => 'Ši vieta šiuo metu yra susieta bent su vienu turto vienetu ir negali būti panaikinta. Atnaujinkite savo turtą, kad nebebūtų sąsajos su šia vieta, ir bandykite dar kartą. ',
    'assoc_child_loc' => 'Ši vieta šiuo metu yra kaip pagrindinė bent vienai žemesnio lygio vietai ir negali būti panaikinta. Atnaujinkite savo žemesnio lygio vietas, kad nebebūtų sąsajos su šia vieta, ir bandykite dar kartą. ',
    'assigned_assets' => 'Priskirtas turtas',
    'current_location' => 'Dabartinė vieta',
    'deleted_warning' => 'Ši vieta buvo ištrinta. Prieš bandydami atlikti bet kokius pakeitimus, turite ją atkurti.',

    'create' => [
        'error' => 'Vieta nebuvo sukurta. Bandykite dar kartą.',
        'success' => 'Vieta sėkmingai sukurta.',
    ],

    'update' => [
        'error' => 'Vieta nebuvo atnaujinta. Bandykite dar kartą',
        'success' => 'Vieta sėkmingai atnaujinta.',
    ],

    'restore' => [
        'error' => 'Vieta nebuvo atkurta. Bandykite dar kartą',
        'success' => 'Vieta sėkmingai atkurta.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite panaikinti šią vietą?',
        'error' => 'Bandant panaikinti vietą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Vieta sėkmingai panaikinta.',
    ],

];
