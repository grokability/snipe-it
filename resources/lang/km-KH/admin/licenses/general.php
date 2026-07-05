<?php

return [
    'about_licenses_title' => 'អំពីអាជ្ញាប័ណ្ណ',
    'about_licenses' => 'អាជ្ញាប័ណ្ណត្រូវបានប្រើដើម្បីតាមដានកម្មវិធី។ ពួកគេ​មាន​ចំនួន​អាសនៈ​ដែល​បាន​បញ្ជាក់​ដែល​អាច​ពិនិត្យ​ចេញ​សម្រាប់​បុគ្គល',
    'checkin' => 'ពិនិត្យកៅអីអាជ្ញាប័ណ្ណ',
    'checkout_history' => 'ប្រវត្តិនៃការប្រគល់អោយ',
    'checkout' => 'ចំនួនអាជ្ញាប័ណ្ណដែលប្រគល់អោយ',
    'edit' => 'កែសម្រួលអាជ្ញាប័ណ្ណ',
    'filetype_info' => 'ប្រភេទឯកសារដែលបានអនុញ្ញាតគឺ png, gif, jpg, jpeg, doc, docx, pdf, txt, zip និង rar ។',
    'clone' => 'អាជ្ញាប័ណ្ណក្លូន',
    'history_for' => 'ប្រវត្តិសម្រាប់ ',
    'in_out' => 'In/Out',
    'info' => 'ព័ត៌មានអាជ្ញាប័ណ្ណ',
    'license_seats' => 'ចំនួនអាជ្ញាប័ណ្ណ',
    'seat' => 'ចំនួន',
    'seat_count' => 'Seat :count',
    'seats' => 'កៅអី',
    'software_licenses' => 'អាជ្ញាប័ណ្ណកម្មវិធី',
    'user' => 'អ្នក​ប្រើ',
    'view' => 'មើលអាជ្ញាប័ណ្ណ',
    'delete_disabled' => 'អាជ្ញាបណ្ណនេះមិនអាចត្រូវបានលុបនៅឡើយទេ ដោយសារកៅអីមួយចំនួននៅតែត្រូវបានប្រគល់អោយអ្នកប្រើប្រាស់',
    'bulk' => [
        'checkin_all' => [
            'button' => 'Checkin All Seats',
            'modal' => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
            'enabled_tooltip' => 'Checkin ALL seats for this license from both users and assets',
            'disabled_tooltip' => 'This is disabled because there are no seats currently checked out',
            'disabled_tooltip_reassignable' => 'This is disabled because the License is not reassignable',
            'success' => 'License successfully checked in! | All licenses were successfully checked in!',
            'log_msg' => 'Checked in via bulk license checkin in license GUI',
        ],

        'checkin_selected' => [
            'success' => ':count seat checked in successfully. | :count seats checked in successfully.',
            'no_seats_selected' => 'No seats were selected.',
        ],

        'checkout_all' => [
            'button' => 'Checkout All Seats',
            'modal' => 'This action will checkout one seat to the first available user. | This action will checkout all :available_seats_count seats to the first available users. A user is considered available for this seat if they do not already have this license checked out to them, and the Auto-Assign License property is enabled on their user account.',
            'enabled_tooltip' => 'Checkout ALL seats (or as many as are available) to ALL users',
            'disabled_tooltip' => 'This is disabled because there are no seats currently available',
            'success' => 'License successfully checked out! | :count licenses were successfully checked out!',
            'error_no_seats' => 'There are no remaining seats left for this license.',
            'warn_not_enough_seats' => ':count users were assigned this license, but we ran out of available license seats.',
            'warn_no_avail_users' => 'Nothing to do. There are no users who do not already have this license assigned to them.',
            'log_msg' => 'Checked out via bulk license checkout in license GUI',

        ],
    ],

    'below_threshold' => 'There are only :remaining_count seats left for this license with a minimum quantity of :min_amt. You may want to consider purchasing more seats.',
    'below_threshold_short' => 'This item is below the minimum required quantity.',
];
