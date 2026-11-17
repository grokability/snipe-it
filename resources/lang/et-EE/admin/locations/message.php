<?php

return [

    'does_not_exist' => 'Asukohta ei eksisteeri.',
    'assoc_users' => 'Seda asukohta ei saa praegu kustutada, sest see on vähemalt ühe kirje või kasutaja registreeritud asukoht, sellele on väljastatud vahendeid või see on mõne teise asukoha ülem-asukoht. Uuenda kirjeid nii, et need ei viitaks enam sellele asukohale, ja proovi uuesti. ',
    'assoc_assets' => 'Selle asukohaga on seotud vähemalt üks vahend ja seda ei saa kustutada. Palun uuenda oma vahendeid, et need ei kasutaks seda asukohta ning seejärel proovi uuesti. ',
    'assoc_child_loc' => 'Sel asukohal on hetkel all-asukohti ja seda ei saa kustutada. Palun uuenda oma asukohti nii, et need ei kasutaks seda asukohta ning seejärel proovi uuesti. ',
    'assigned_assets' => 'Määratud Varad',
    'current_location' => 'Praegune asukoht',
    'deleted_warning' => 'See asukoht on kustutatud. Taasta see enne muudatuste tegemist.',

    'create' => [
        'error' => 'Asukohta ei loodud, palun proovi uuesti.',
        'success' => 'Asukoha loomine õnnestus.',
    ],

    'update' => [
        'error' => 'Asukohta ei uuendatud, palun proovi uuesti',
        'success' => 'Asukoha uuendamine õnnestus.',
    ],

    'restore' => [
        'error' => 'Asukoha taastamine ebaõnnestus, proovi uuesti',
        'success' => 'Asukoha taastamine õnnestus.',
    ],

    'delete' => [
        'confirm' => 'Oled sa kindel, et soovid selle asukoha kustutada?',
        'error' => 'Asukoha kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Asukoha kustutamine õnnestus.',
    ],

    'bulkedit' => [
        'error' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'success' => 'Asukoha uuendamine õnnestus.|:count asukoha uuendamine õnnestus.',
        'warn' => 'Muuda allolevaid välju selle asukoha uuendamiseks. Tühjaks jäetud välju asukohal ei muudeta.|Muuda allolevaid välju kõigi :count valitud asukoha uuendamiseks. Tühjaks jäetud välju ühelgi neist ei muudeta.',
        'show_selected' => '1 valitud asukoht|:count valitud asukohta',
        'company_scope_mismatch_partial' => 'Ühe asukoha ettevõtet ei muudetud, sest selle asukoha kirjed või kasutajad kuuluvad erinevatele ettevõtetele. Uuenda või teisalda need esmalt.|:count asukoha ettevõtet ei muudetud, sest nende asukohtade kirjed või kasutajad kuuluvad erinevatele ettevõtetele. Uuenda või teisalda need esmalt.',
        'company_scope_mismatch_all' => 'Ühtegi asukohta ei määratud ümber. Taotletud ettevõte ei vasta valitud asukoha kirjetele või kasutajatele.|Ühtegi asukohta ei määratud ümber. Taotletud ettevõte ei vasta ühegi :count valitud asukoha kirjetele või kasutajatele.',
        'parent_company_mismatch_partial' => 'Ühe asukoha ülem-asukohta või ettevõtet ei muudetud, sest asukoht jääks siis oma ülem-asukohast erinevasse ettevõttesse.|:count asukoha ülem-asukohta või ettevõtet ei muudetud, sest need jääksid siis oma ülem-asukohast erinevasse ettevõttesse.',
        'parent_company_mismatch_all' => 'Muudatusi ei salvestatud. Taotletud ülem-asukoha või ettevõtte korral jääks asukoht oma ülem-asukohast erinevasse ettevõttesse.|Muudatusi ei salvestatud. Taotletud ülem-asukoha või ettevõtte korral jääks iga :count valitud asukoht oma ülem-asukohast erinevasse ettevõttesse.',
    ],

];
