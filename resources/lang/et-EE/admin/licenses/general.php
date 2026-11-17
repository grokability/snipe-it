<?php

return [
    'about_licenses_title' => 'Litsentside kohta',
    'about_licenses' => 'Litsentse kasutatakse tarkvara jälgimiseks. Neil on kindlaksmääratud arv istekohti, mida saab üksikisikutele kontrollida',
    'checkin' => 'Checkin License Seat',
    'checkout_history' => 'Checkout ajalugu',
    'checkout' => 'Checkout\'i litsentside asukoht',
    'edit' => 'Redigeeri litsentsi',
    'filetype_info' => 'Lubatud failitüübid on png, gif, jpg, jpeg, doc, docx, pdf, txt, zip ja rar.',
    'clone' => 'Klooni litsents',
    'history_for' => 'Ajalugu jaoks',
    'in_out' => 'Sees väljas',
    'info' => 'Litsentsi info',
    'license_seats' => 'Litsentse',
    'seat' => 'Istekoht',
    'seat_count' => 'Kasutuskoht :count',
    'seats' => 'Istekohad',
    'software_licenses' => 'Tarkvara litsentsid',
    'user' => 'Kasutaja',
    'view' => 'Vaadake litsentsi',
    'delete_disabled' => 'Seda litsentsi ei saa veel kustutada, sest osa kasutuskohti on endiselt välja antud.',
    'bulk' => [
        'checkin_all' => [
            'button' => 'Tagasta kõik kasutuskohad',
            'modal' => 'See toiming tagastab ühe kasutuskoha. | See toiming tagastab selle litsentsi kõik :checkedout_seats_count kasutuskohta.',
            'enabled_tooltip' => 'Tagasta selle litsentsi KÕIK kasutuskohad nii kasutajatelt kui ka vahenditelt',
            'disabled_tooltip' => 'See toiming on keelatud, sest ühtki kasutuskohta pole praegu välja antud',
            'disabled_tooltip_reassignable' => 'See toiming on keelatud, sest litsentsi ei saa ümber määrata',
            'success' => 'Litsentsi tagastamine õnnestus! | Kõigi litsentside tagastamine õnnestus!',
            'log_msg' => 'Tagastatud litsentside haldusvaate hulgitagastuse kaudu',
        ],

        'checkin_selected' => [
            'success' => ':count kasutuskoha tagastamine õnnestus. | :count kasutuskoha tagastamine õnnestus.',
            'no_seats_selected' => 'Ühtegi kasutuskohta pole valitud.',
        ],

        'checkout_all' => [
            'button' => 'Väljasta kõik kasutuskohad',
            'modal' => 'See toiming väljastab ühe kasutuskoha esimesele sobivale kasutajale. | See toiming väljastab kõik :available_seats_count kasutuskohta esimestele sobivatele kasutajatele. Kasutaja sobib kasutuskoha saajaks, kui talle pole seda litsentsi veel väljastatud ja tema kontol on lubatud litsentside automaatne määramine.',
            'enabled_tooltip' => 'Väljasta KÕIK kasutuskohad (või nii palju, kui neid on saadaval) KÕIGILE kasutajatele',
            'disabled_tooltip' => 'See toiming on keelatud, sest praegu pole vabu kasutuskohti',
            'success' => 'Litsentsi väljastamine õnnestus! | :count litsentsi väljastamine õnnestus!',
            'error_no_seats' => 'Sellel litsentsil pole enam vabu kasutuskohti.',
            'warn_not_enough_seats' => 'Litsents määrati :count kasutajale, kuid vabad kasutuskohad said otsa.',
            'warn_no_avail_users' => 'Midagi pole teha. Pole kasutajaid, kellele seda litsentsi pole juba määratud.',
            'log_msg' => 'Väljastatud litsentside haldusvaate hulgiväljastuse kaudu',

        ],

        'delete_with_checkin' => [
            'label' => 'Tagasta kasutuskohad ja kustuta',
            'log_msg' => 'Tagastatud litsentside loendis hulgitagastuse ja kustutamise kaudu',
        ],
    ],

    'below_threshold' => 'Sellel litsentsil on alles vaid :remaining_count kasutuskohta, kuigi miinimumkogus on :min_amt. Kaalu kasutuskohtade juurde ostmist.',
    'below_threshold_short' => 'Selle kirje kogus on alla nõutava miinimumi.',
];
