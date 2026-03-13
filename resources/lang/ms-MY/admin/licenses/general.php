<?php

return array(
    'about_licenses_title'      => 'Mengenai Lesen',
    'about_licenses'            => 'Lesen digunakan untuk mengesan perisian. Mereka mempunyai bilangan tempat duduk tertentu yang boleh diperiksa kepada individu',
    'checkin'  					=> 'Terima Kekosongan Lesen',
    'checkout_history'  		=> 'Sejarah Agihan',
    'checkout'  				=> 'Agihkan Kekosongan Lesen',
    'edit'  					=> 'Kemaskini Lesen',
    'filetype_info'				=> 'Filetype yang dibenarkan adalah png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, dan rar.',
    'clone'  					=> 'Pendua Lesen',
    'history_for'  				=> 'Sejarah untuk ',
    'in_out'  					=> 'Masuk/Keluar',
    'info'  					=> 'Maklumat Lesen',
    'license_seats'  			=> 'Lesen Kosong',
    'seat'  					=> 'Kekosongan',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Kekosongan',
    'software_licenses'  		=> 'Lesen Perisian',
    'user'  					=> 'Pengguna',
    'view'  					=> 'Papar Lesen',
    'delete_disabled'           => 'This license cannot be deleted yet because some seats are still checked out.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Checkin All Seats',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Checkin ALL seats for this license from both users and assets',
                'disabled_tooltip'  => 'This is disabled because there are no seats currently checked out',
                'disabled_tooltip_reassignable'  => 'This is disabled because the License is not reassignable',
                'success'           => 'License successfully checked in! | All licenses were successfully checked in!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Checkout All Seats',
                'modal'                 => 'This action will checkout one seat to the first available user. | This action will checkout all :available_seats_count seats to the first available users. A user is considered available for this seat if they do not already have this license checked out to them, and the Auto-Assign License property is enabled on their user account.',
                'enabled_tooltip'   => 'Checkout ALL seats (or as many as are available) to ALL users',
                'disabled_tooltip'  => 'This is disabled because there are no seats currently available',
                'success'           => 'License successfully checked out! | :count licenses were successfully checked out!',
                'error_no_seats'    => 'There are no remaining seats left for this license.',
                'warn_not_enough_seats'    => ':count users were assigned this license, but we ran out of available license seats.',
                'warn_no_avail_users'    => 'Nothing to do. There are no users who do not already have this license assigned to them.',
                'log_msg'           => 'Checked out via bulk license checkout in license GUI',


            ],
    ],

    'below_threshold' => 'There are only :remaining_count seats left for this license with a minimum quantity of :min_amt. You may want to consider purchasing more seats.',
    'below_threshold_short' => 'This item is below the minimum required quantity.',
);
