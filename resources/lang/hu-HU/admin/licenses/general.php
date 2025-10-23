<?php

return array(
    'about_licenses_title'      => 'A licencekről',
    'about_licenses'            => 'Az engedélyeket a szoftverek nyomon követésére használják. Meghatározott számú ülőhellyel rendelkeznek, melyeket az egyéneknek lehet ellenőrizni',
    'checkin'  					=> 'Bevét engedély Seat',
    'checkout_history'  		=> 'Visszavét előzmények',
    'checkout'  				=> 'Kiadás Licence ülés/kérelem',
    'edit'  					=> 'Engedély szerkesztése',
    'filetype_info'				=> 'Az engedélyezett fájltípusok png, gif, jpg, jpeg, doc, docx, pdf, txt, zip és rar.',
    'clone'  					=> 'Clone License',
    'history_for'  				=> 'A történelem',
    'in_out'  					=> 'Be ki',
    'info'  					=> 'Licensz információ',
    'license_seats'  			=> 'Licenc ülések',
    'seat'  					=> 'Ülés',
    'seat_count'  				=> 'Licenchely: :count',
    'seats'  					=> 'ülések',
    'software_licenses'  		=> 'Szoftverlicencek',
    'user'  					=> 'használó',
    'view'  					=> 'Licenc megtekintése',
    'delete_disabled'           => 'Ez a licensz nem törölhető még, mert vannak belőle kiadott példányok.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Minden licenchely visszaadása',
                'modal'             => 'Ez a művelet egy licenchely visszaadását végzi. | Ez a művelet visszaadja az összes, :checkedout_seats_count licenchelyt ehhez a licenchez.',
                'enabled_tooltip'   => 'Az összes licenchely visszaadása ehhez a licenchez mind a felhasználóktól, mind az eszközökről',
                'disabled_tooltip'  => 'Ez le van tiltva, mert jelenleg nincs kivett licenchely',
                'disabled_tooltip_reassignable'  => 'Ez le van tiltva, mert a licenc nem átruházható',
                'success'           => 'Licenc visszavétel sikeres! | Minden licenc sikeresen visszavéve!',
                'log_msg'           => 'Visszaadva a licenc GUI-ban végzett tömeges licenchely-visszaadással.”',
            ],

            'checkout_all'              => [
                'button'                => 'Minden licenchely kadása',
                'modal'                 => 'Ez a művelet egy licenchelyt ad ki az első elérhető felhasználónak. | Ez a művelet az összes, :available_seats_count licenchelyt kiosztja az első elérhető felhasználóknak. Egy felhasználó az adott licenchelyre elérhetőnek számít, ha még nincs hozzá kiosztva, és az Automatikus licenckiosztás beállítás engedélyezve van a felhasználói fiókjában.',
                'enabled_tooltip'   => 'Az összes licenchely (vagy amennyi elérhető) kiosztása minden felhasználónak',
                'disabled_tooltip'  => 'Ez le van tiltva, mert jelenleg nincs elérhető licenchely',
                'success'           => 'Licenc sikeresen kiadva! | :count db. licenc sikeresen kiadva !',
                'error_no_seats'    => 'Ehhez a licenchöz már nincs szabad licenchely.',
                'warn_not_enough_seats'    => ':count felhasználónak lett kiosztva ez a licenc, de elfogytak az elérhető licenchelyek.',
                'warn_no_avail_users'    => 'Nincs teendő. Nincsenek olyan felhasználók, akiknek még nincs hozzárendelve ez a licenc.',
                'log_msg'           => 'Checked out via bulk license checkout in license GUI',


            ],
    ],

    'below_threshold' => 'Ehhez a licenchez már csak :remaining_count hely maradt, a minimálisan elvárt mennyiség pedig :min_amt. Érdemes lehet további helyeket vásárolni.',
    'below_threshold_short' => 'Ebből az elemből nincs meg a beállított minimum mennyiség.',
);
