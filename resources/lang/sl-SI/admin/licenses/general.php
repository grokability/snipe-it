<?php

return array(
    'about_licenses_title'      => 'O licencah',
    'about_licenses'            => 'Licence se uporabljajo za sledenje programske opreme. Imajo določeno število prostih mest, ki jih je mogoče izdati posameznikom',
    'checkin'  					=> 'Prevzem licenčnih mest',
    'checkout_history'  		=> 'Zgodovina izdaje',
    'checkout'  				=> 'Izdaj licenco',
    'edit'  					=> 'Urejanje licence',
    'filetype_info'				=> 'Dovoljene oblike datotek so png, gif, jpg, jpeg, doc, docx, pdf, txt, zip in rar.',
    'clone'  					=> 'Kloniraj licenco',
    'history_for'  				=> 'Zgodovina za ',
    'in_out'  					=> 'Vhod / izhod',
    'info'  					=> 'Informacije o licenci',
    'license_seats'  			=> 'Število licenc',
    'seat'  					=> 'Število licenc',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Število licenc',
    'software_licenses'  		=> 'Licence za programsko opremo',
    'user'  					=> 'Uporabnik',
    'view'  					=> 'Ogled licence',
    'delete_disabled'           => 'This license cannot be deleted yet because some seats are still checked out.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Checkin All Seats',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Prijava VSEH sedežev za to licenco tako od uporabnikov kot od sredstev',
                'disabled_tooltip'  => 'This is disabled because there are no seats currently checked out',
                'disabled_tooltip_reassignable'  => 'To je onemogočeno, ker licence ni mogoče prenesti naprej',
                'success'           => 'Licenca uspešno prijavljena! | Vse licence so bile uspešno prijavljene!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Checkout All Seats',
                'modal'                 => 'To dejanje bo rezerviralo en sedež prvemu razpoložljivemu uporabniku. | To dejanje bo rezerviralo vse :available_seats_count sedeže prvim razpoložljivim uporabnikom. Uporabnik se šteje za razpoložljivega za ta sedež, če te licence še ni rezervirane in če je v njegovem uporabniškem računu omogočena lastnost Samodejno dodeljevanje licenc.',
                'enabled_tooltip'   => 'Checkout ALL seats (or as many as are available) to ALL users',
                'disabled_tooltip'  => 'This is disabled because there are no seats currently available',
                'success'           => 'Licenca uspešno prevzeta! | :count licenc je bilo uspešno prevzetih!',
                'error_no_seats'    => 'There are no remaining seats left for this license.',
                'warn_not_enough_seats'    => ':count users were assigned this license, but we ran out of available license seats.',
                'warn_no_avail_users'    => 'Ničesar ni treba storiti. Ni uporabnikov, ki jim ta licenca še ni dodeljena.',
                'log_msg'           => 'Prevzeto prek množične prevzemnice licenc v grafičnem uporabniškem vmesniku za licence',


            ],
    ],

    'below_threshold' => 'Za to licenco je na voljo le še :remaining_count sedežev z minimalno količino :min_amt. Morda boste želeli razmisliti o nakupu dodatnih sedežev.',
    'below_threshold_short' => 'Ta artikel je pod minimalno zahtevano količino.',
);
