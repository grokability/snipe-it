<?php

return array(
    'about_licenses_title'      => 'Om lisenser',
    'about_licenses'            => 'Lisenser er brukt til å spore bruk av programvare.  De har et angitt antall seter som kan sjekkes ut til enkeltpersoner',
    'checkin'  					=> 'Sjekk inn setelisens',
    'checkout_history'  		=> 'Utsjekkhistorikk',
    'checkout'  				=> 'Sjekk ut setelisens',
    'edit'  					=> 'Rediger lisens',
    'filetype_info'				=> 'Gyldige filtyper er png, gif, jpg, jpeg, doc docx, pdf, txt, zip og rar.',
    'clone'  					=> 'Klon lisens',
    'history_for'  				=> 'Historikk for ',
    'in_out'  					=> 'Inne/ute',
    'info'  					=> 'Lisensinformasjon',
    'license_seats'  			=> 'Setelisenser',
    'seat'  					=> 'Setelisens',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Setelisenser',
    'software_licenses'  		=> 'Programvarelisenser',
    'user'  					=> 'Bruker',
    'view'  					=> 'Vis lisens',
    'delete_disabled'           => 'Denne lisensen kan ikke slettes ennå fordi det fremdeles er noen seter i den som er sjekket ut.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Sjekk inn alle seter',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Sjekk inn ALLE seter for denne lisensen fra både brukere og ressurser',
                'disabled_tooltip'  => 'Dette er deaktivert fordi det ikke er seter som er sjekket ut',
                'disabled_tooltip_reassignable'  => 'Dette er deaktivert fordi lisensen ikke kan refordeles',
                'success'           => 'Lisensen ble sjekket inn! | Alle lisensene ble vellykket sjekket inn!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Sjekk ut alle seter',
                'modal'                 => 'Denne handlingen vil utsjekke ett sete til den første tilgjengelige brukeren. | Denne handlingen vil kassere alle :available_seats_count seter til de første tilgjengelige brukerne. En bruker anses som tilgjengelig for dette setet hvis de ikke allerede har sjekket ut denne lisensen til dem, og Auto-Assign License egenskapen er aktivert på deres brukerkonto.',
                'enabled_tooltip'   => 'Sjekk ut ALLE seter (eller så mange som er tilgjengelige) til ALLE brukere',
                'disabled_tooltip'  => 'Dette er deaktivert fordi det ikke er tilgjengelige seter for øyeblikket',
                'success'           => 'Lisensen ble sjekket ut! | :count lisenser ble vellykket sjekket ut!',
                'error_no_seats'    => 'Det er ingen gjenværende seter igjen for denne lisensen.',
                'warn_not_enough_seats'    => ':count brukere ble tildelt denne lisensen, men vi gikk tom for tilgjengelige lisensseter.',
                'warn_no_avail_users'    => 'Ingenting å gjøre. Det er ingen brukere som ikke allerede har denne lisensen tildelt dem.',
                'log_msg'           => 'Sjekket ut via masselisensutsjekking i lisens GUI',


            ],
    ],

    'below_threshold' => 'Det er bare :remaining_count seter igjen for denne lisensen med et minimum av :min_amt. Du kan vurdere å kjøpe flere seter.',
    'below_threshold_short' => 'Denne varen er under det minstekravene kreves.',
);
