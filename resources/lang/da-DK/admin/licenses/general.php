<?php

return array(
    'about_licenses_title'      => 'Om licenser',
    'about_licenses'            => 'Licenser bruges til at spore software. De har et bestemt antal pladser, der kan tjekkes ud til enkeltpersoner',
    'checkin'  					=> 'Checkin Licenssæde',
    'checkout_history'  		=> 'Checkout historie',
    'checkout'  				=> 'Checkout Licenssæde',
    'edit'  					=> 'Redigere licens',
    'filetype_info'				=> 'Tilladte filtyper er png, gif, jpg, jpeg, doc, docx, pdf, txt, zip og rar.',
    'clone'  					=> 'Klon licens',
    'history_for'  				=> 'Historie for',
    'in_out'  					=> 'Ind ud',
    'info'  					=> 'Licens Info',
    'license_seats'  			=> 'Licenssæder',
    'seat'  					=> 'Sæde',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Sæder',
    'software_licenses'  		=> 'Softwarelicenser',
    'user'  					=> 'Bruger',
    'view'  					=> 'Se licens',
    'delete_disabled'           => 'Denne licens kan ikke slettes endnu, da nogle pladser stadig er tjekket ud.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Tjek Alle Pladser Ind',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Checkin ALLE pladser til denne licens fra både brugere og aktiver',
                'disabled_tooltip'  => 'Dette er deaktiveret, fordi der ikke er nogen pladser i øjeblikket tjekket ud',
                'disabled_tooltip_reassignable'  => 'Dette er deaktiveret fordi licensen ikke kan gentildeles',
                'success'           => 'Licensen blev tjekket ind! - Alle licenser blev tjekket ind!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Tjek Alle Pladser Ud',
                'modal'                 => 'Denne handling vil kassere et sæde til den første tilgængelige bruger. ● Denne handling vil kasserer alle :available_seats_count sæder til de første tilgængelige brugere. En bruger anses for at være tilgængelig for dette sæde, hvis de ikke allerede har denne licens tjekket ud til dem, og Auto-Tildel Licens-egenskaben er aktiveret på deres brugerkonto.',
                'enabled_tooltip'   => 'Checkout ALLE sæder (eller så mange som er tilgængelige) til ALLE brugere',
                'disabled_tooltip'  => 'Dette er deaktiveret, fordi der ikke er ledige pladser i øjeblikket',
                'success'           => 'Licensen blev tjekket ud! ● :count licenser blev tjekket ud!',
                'error_no_seats'    => 'Der er ingen resterende pladser tilbage til denne licens.',
                'warn_not_enough_seats'    => ':count brugere blev tildelt denne licens, men vi løb tør for tilgængelige licens sæder.',
                'warn_no_avail_users'    => 'Intet at gøre. Der er ingen brugere som ikke allerede har denne licens tildelt dem.',
                'log_msg'           => 'Checket ud via bulk licens checkout i licens GUI',


            ],
    ],

    'below_threshold' => 'Der er kun :remaining_count pladser tilbage til denne licens med et minimum antal :min_amt. Du kan overveje at købe flere pladser.',
    'below_threshold_short' => 'Denne vare er under den krævede minimumsmængde.',
);
