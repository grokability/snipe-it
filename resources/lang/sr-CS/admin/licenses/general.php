<?php

return array(
    'about_licenses_title'      => 'O licencama',
    'about_licenses'            => 'Licence se koriste za praćenje softvera. Imaju određeni broj mesta koja se mogu dodeliti pojedincima',
    'checkin'  					=> 'Checkin License Seat',
    'checkout_history'  		=> 'Checkout History',
    'checkout'  				=> 'Checkout License Seat',
    'edit'  					=> 'Uredite licencu',
    'filetype_info'				=> 'Dopuštene vrste datoteka su png, gif, jpg, jpeg, doc, docx, pdf, txt, zip i rar.',
    'clone'  					=> 'Klon licence',
    'history_for'  				=> 'Istorija za ',
    'in_out'  					=> 'U/I',
    'info'  					=> 'Informacije o licenci',
    'license_seats'  			=> 'Broj licenciranih mesta',
    'seat'  					=> 'Mesto',
    'seat_count'  				=> 'Mesta :count',
    'seats'  					=> 'Mesta',
    'software_licenses'  		=> 'Licence za softver',
    'user'  					=> 'Korisnik',
    'view'  					=> 'Prikaži licencu',
    'delete_disabled'           => 'Ova licenca još uvek ne može biti obrisana jer su neka mesta još uvek zadužena.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Razduži sva mesta',
                'modal'             => 'Ova radnja će razdužiti jedno mesto. | Ova radnja će razdužiti svih :checkedout_seats_count mesta ove licence.',
                'enabled_tooltip'   => 'Razduži SVA mesta za ovu licencu od korisnika i imovine',
                'disabled_tooltip'  => 'Ovo je onemogućeno jer trenutno nema zaduženih mesta',
                'disabled_tooltip_reassignable'  => 'Ovo je onemogućeno jer Licenca nije premestiva',
                'success'           => 'Licenca je uspešno razdužena! | Sve licence su uspešno razdužene!',
                'log_msg'           => 'Razduži putem grupnog razduživanja u interfejsu licence',
            ],

            'checkout_all'              => [
                'button'                => 'Zaduži sva mesta',
                'modal'                 => 'Ova radnja će zadužiti jedno mesto prvom dostupnom korisniku. | Ova radnja će zadužiti svih :available_seats_count mesta prvim dostupnim korisnicima. Korisnik se smatra dostupnim za ovo mesto ako već nemaju zaduženu ovu licencu, i parametar Automatsko dodeljivanje licence je omogućen za njihov nalog.',
                'enabled_tooltip'   => 'Zaduži SVA mesta (ili koliko ih je dostupno) SVIM korisnicima',
                'disabled_tooltip'  => 'Ovo je onemogućeno jer trenutno nema dostupnih mesta',
                'success'           => 'Licenca je uspešno zadužena! | :count licenci je uspešno zaduženo!',
                'error_no_seats'    => 'Nema preostalih slobodnih mesta za ovu licencu.',
                'warn_not_enough_seats'    => ':count korisnika je dodeljena ova licenca, ali je ponestalo dostupnih mesta za licencu.',
                'warn_no_avail_users'    => 'Ne treba ništa uraditi. Nema korisnika koji nemaju već dodeljenu ovu licencu.',
                'log_msg'           => 'Zaduženo grupnim razduživanjem u ekranu licenci',


            ],
    ],

    'below_threshold' => 'Ostalo je samo :remaining_count slobodnih mesta za ovu licencu sa minimalnom količinom :min_amt. Možda bi ste želeli da razmotrite nabavku nove količine.',
    'below_threshold_short' => 'Ovaj predmet je ispod minimuma potrebne količine.',
);
