<?php

return array(
    'about_licenses_title'      => 'Apie licencijas',
    'about_licenses'            => 'Licencijos naudojamos programinės įrangai sekimui. Jos turi nurodytą vietų skaičių, kiek jų galima priskirti naudotojams',
    'checkin'  					=> 'Paimti licenciją',
    'checkout_history'  		=> 'Išdavimo istorija',
    'checkout'  				=> 'Išduoti licenciją',
    'edit'  					=> 'Redaguoti licenciją',
    'filetype_info'				=> 'Leidžiami failų tipai yra: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar.',
    'clone'  					=> 'Klonuoti licenciją',
    'history_for'  				=> 'Istorija ',
    'in_out'  					=> 'Į/Iš',
    'info'  					=> 'Licencijos informacija',
    'license_seats'  			=> 'Licencijos vietų skaičius',
    'seat'  					=> 'Vieta',
    'seat_count'  				=> 'Vietos :count',
    'seats'  					=> 'Vietos',
    'software_licenses'  		=> 'Programinės įrangos licencijos',
    'user'  					=> 'Naudotojas',
    'view'  					=> 'Peržiūrėti licenciją',
    'delete_disabled'           => 'Ši licencija negali būti panaikinta, kadangi kai kurios vietos vis dar yra išduotos.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Paimti visas vietas',
                'modal'             => 'Šis veiksmas paims vieną vietą. | Šis veiksmas paims visas :checkedout_seats_count šios licencijos vietas.',
                'enabled_tooltip'   => 'Paimkite VISAS licencijos vietas – tiek iš naudotojų, tiek iš turto',
                'disabled_tooltip'  => 'Išjungta, kadangi šiuo metu nėra išduotų vietų',
                'disabled_tooltip_reassignable'  => 'Išjungta, kadangi licencijos negalima perduoti',
                'success'           => 'Licencija paimta sėkmingai! | Visos licencijos paimtos sėkmingai!',
                'log_msg'           => 'Paimta naudojant masinio licencijų paėmimo sąsają',
            ],

            'checkout_all'              => [
                'button'                => 'Išduoti visas vietas',
                'modal'                 => 'Atlikus šį veiksmą, pirmam laisvam naudotojui bus priskirta viena vieta. | Atlikus šį veiksmą, bus priskirtos visos :available_seats_count vietos pirmiesiems galimiems naudotojams. Laikoma, kad naudotojas gali gauti vietą, jei jis dar neturi šios licencijos, o jo naudotojo paskyroje yra įgalintas automatinis licencijos priskyrimas.',
                'enabled_tooltip'   => 'Priskirti VISAS vietas (arba tiek, kiek yra), VISIEMS naudotojams',
                'disabled_tooltip'  => 'Išjungta, kadangi šiuo metu nėra laisvų vietų',
                'success'           => 'Licencija išduota sėkmingai! | :count licencijos išduotos sėkmingai!',
                'error_no_seats'    => 'Nėra laisvų licencijos vietų.',
                'warn_not_enough_seats'    => ':count naudotojams buvo priskirta ši licencija, bet mums baigėsi laisvos licencijos vietos.',
                'warn_no_avail_users'    => 'Nieko daryti nereikia. Nėra naudotojų, kuriems nebūtų priskirta ši licencija.',
                'log_msg'           => 'Išduota naudojant masinio licencijų išdavimo sąsają',


            ],
    ],

    'below_threshold' => 'Liko tik :remaining_count šios licencijos vietos, kai mažiausias kiekis yra :min_amt. Apsvarstykite galimybę įsigyti daugiau vietų.',
    'below_threshold_short' => 'Turimas šio elemento kiekis yra mažesnis už mažiausią reikalaujamą kiekį.',
);
