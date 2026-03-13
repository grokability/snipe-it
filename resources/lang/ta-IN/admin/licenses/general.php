<?php

return array(
    'about_licenses_title'      => 'உரிமங்கள் பற்றி',
    'about_licenses'            => 'மென்பொருள் கண்காணிக்க பயன்படுகிறது. தனிநபர்களிடம் சோதிக்கப்படக்கூடிய குறிப்பிட்ட எண்ணிக்கையிலான இடங்களை அவர்கள் கொண்டுள்ளனர்',
    'checkin'  					=> 'Checkin உரிமத்தின் இடம்',
    'checkout_history'  		=> 'புதுப்பித்து வரலாறு',
    'checkout'  				=> 'புதுப்பித்து உரிமம் இருக்கை',
    'edit'  					=> 'உரிமம் திருத்தவும்',
    'filetype_info'				=> 'அனுமதிக்கப்பட்ட கோப்புரிமைகள் png, gif, jpg, jpeg, doc, docx, pdf, txt, zip மற்றும் rar ஆகியவை.',
    'clone'  					=> 'க்ளோன் உரிமம்',
    'history_for'  				=> 'வரலாறு',
    'in_out'  					=> 'உள்ளே வெளியே',
    'info'  					=> 'உரிமம் தகவல்',
    'license_seats'  			=> 'உரிமம் இருக்கைகள்',
    'seat'  					=> 'இருக்கை',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'இருக்கைகள்',
    'software_licenses'  		=> 'மென்பொருள் உரிமங்கள்',
    'user'  					=> 'பயனர்',
    'view'  					=> 'உரிமத்தைக் காட்டு',
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
