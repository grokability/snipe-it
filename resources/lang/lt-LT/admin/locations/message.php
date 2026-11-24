<?php

return [

    'does_not_exist' => 'Tokios vietos nėra.',
    'assoc_users' => 'Šios vietos negalima ištrinti, nes ji yra bent vieno daikto ar naudotojo vieta, jai yra priskirtas turtas arba ji yra nurodyta kaip pagrindinė kitos vietos vieta. Atnaujinkite savo įrašus, kad jie nebeturėtų sąsajų su šia vieta ir bandykite dar kartą ',
    'assoc_assets' => 'Ši vieta šiuo metu yra susieta bent su vienu turto vienetu ir negali būti ištrinta. Atnaujinkite savo turtą, kad nebebūtų sąsajos su šia vieta, ir bandykite dar kartą. ',
    'assoc_child_loc' => 'Ši vieta šiuo metu yra kaip pagrindinė bent vienai žemesnio lygio vietai ir negali būti ištrinta. Atnaujinkite savo žemesnio lygio vietas, kad nebebūtų sąsajos su šia vieta, ir bandykite dar kartą. ',
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
        'confirm' => 'Ar tikrai norite ištrinti šią vietą?',
        'error' => 'Bandant ištrinti vietą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Vieta sėkmingai ištrinta.',
    ],

    'bulkedit' => [
        'error' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'success' => 'Vieta sėkmingai atnaujinta. |Vietos (:count) sėkmingai atnaujintos.',
        'warn' => 'Redaguokite žemiau pateiktus laukus, kad atnaujintumėte šią vietą. Laukai, kuriuos paliksite tuščius, nebus pakeisti šiai vietai.|Redaguokite žemiau pateiktus laukus, kad atnaujintumėte visas pasirinktas vietas (:count). Laukai, kuriuos paliksite tuščius, nebus pakeisti nė vienai iš jų.',
        'show_selected' => '1 pasirinkta vieta|:count pasirinktos vietos',
        'company_scope_mismatch_partial' => 'Įmonė nebuvo pakeista 1 vietai, nes toje vietoje esantys daiktai ar naudotojai priklauso kitoms įmonėms. Pirmiau juos atnaujinkite arba perkelkite.|Įmonė nebuvo pakeista (:count) vietoms, nes tose vietose esantys daiktai ar naudotojai priklauso kitoms įmonėms. Pirmiau juos atnaujinkite arba perkelkite.',
        'company_scope_mismatch_all' => 'Nė viena vieta nebuvo perkelta. Užklausos įmonė nesutampa su daiktais ar naudotojais pasirinktoje vietoje.|Nė viena vieta nebuvo perkelta. Užklausos įmonė nesutampa su objektais ar naudotojais nė vienoje iš pasirinktų (:count) vietų.',
        'parent_company_mismatch_partial' => 'Pagrindinė įmonė arba įmonė nebuvo pakeista 1 vietai, nes dėl to vieta liktų kitoje įmonėje nei jos pagrindinė įmonė.|Pagrindinė įmonė arba įmonė nebuvo pakeista vietoms (:count), nes dėl to vietos liktų kitoje įmonėje nei jų pagrindinė įmonė.',
        'parent_company_mismatch_all' => 'Pakeitimai nebuvo išsaugoti. Nurodyta pagrindinė įmonė arba įmonė paliktų vietą kitoje įmonėje nei jos pagrindinė įmonė.|Pakeitimai nebuvo išsaugoti. Nurodyta pagrindinė įmonė arba įmonė paliktų visas pasirinktas vietas (:count) kitoje įmonėje nei jų pagrindinė įmonė.',
    ],

];
