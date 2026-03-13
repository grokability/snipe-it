<?php

return array(
    'about_licenses_title'      => 'O licenciach',
    'about_licenses'            => 'Licencie sa využívajú k sledovaniu softvéru. Majú stanovený počet slotov, ktoré môžu byť odovzdané používateľom',
    'checkin'  					=> 'Prevziať licenčný slot',
    'checkout_history'  		=> 'História odovzdania',
    'checkout'  				=> 'Odovzdať licenčný slot',
    'edit'  					=> 'Upraviť licenciu',
    'filetype_info'				=> 'Podporované typy súborov: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip a rar.',
    'clone'  					=> 'Duplikovať licenciu',
    'history_for'  				=> 'História pre ',
    'in_out'  					=> 'Vsetup/Výstup',
    'info'  					=> 'Informácie o licencii',
    'license_seats'  			=> 'Počet slotov',
    'seat'  					=> 'Slot',
    'seat_count'  				=> 'Slot :count',
    'seats'  					=> 'Sloty',
    'software_licenses'  		=> 'Softvérové licencie',
    'user'  					=> 'Používateľ',
    'view'  					=> 'Podrobnosti o licencii',
    'delete_disabled'           => 'Tieto licencie nemôžu byť zmazané pretože niektoré sloty sú stále odovzdané.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Prevziať všetky sloty',
                'modal'             => 'Táto akcia prevezme jeden slot. | Táto akcia prevezme všetkých :checkedout_seats_count slotov tejto licencie.',
                'enabled_tooltip'   => 'Prevziať všetky sloty tejto licencie od používateľov aj majetkov',
                'disabled_tooltip'  => 'Táto akcia je zakázaná, keďže nie je odovzdaná žiadna licencia',
                'disabled_tooltip_reassignable'  => 'Táto akcia je zakázaná pretože licencia nie je znovu priraditeľná',
                'success'           => 'Licencia úspešne prevzatá! | Všetky licencie boli úspešne prevzaté!',
                'log_msg'           => 'Hromadne prevziať v rozhraní pre správu licencií',
            ],

            'checkout_all'              => [
                'button'                => 'Odovzdať všetky sloty',
                'modal'                 => 'Táto akcia odovzdá jeden slot prvému dostupnému používateľovi. | Táto akcia odovzdá všetkých :available_seats_count slotov prvých dostupných používateľom. Používateľ je považovaný za dostupného v prípade, ak mu ešte nebola odovzdaná táto licencia a daný používateľ ma povolené automatické priradzovanie licencií.',
                'enabled_tooltip'   => 'Odovzdať všetky (dostupné) sloty všetkých používateľom',
                'disabled_tooltip'  => 'Táto možnosť je zablokovaná pretože nie sú dostupné žiadne sloty',
                'success'           => 'Licencia bola úspešne odovzdaná! | :count licencií bolo úspešne odovzdaných!',
                'error_no_seats'    => 'Nezostávajú žiadne voľné sloty pri tejto licencii.',
                'warn_not_enough_seats'    => ':count používateľom bola licencia priradená, avšak už nezostali žiadne voľné sloty.',
                'warn_no_avail_users'    => 'Nie je možné zrealizovať. Nezostal žiaden používateľ, ktorý by túto licenciu ešte nemal priradenú.',
                'log_msg'           => 'Hromadne odovzdať v rozhraní pre správu licencií',


            ],
    ],

    'below_threshold' => 'Zostáva už iba :remaining_count voľných slotov pre túto licenciu s nastaveným minimálnym množstvom :min_amt. Mali by ste zvážiť dokúpenie ďalších slotov.',
    'below_threshold_short' => 'Táto položka je pod hranicou minimálneho požadovaného množstva.',
);
