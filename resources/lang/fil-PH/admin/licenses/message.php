<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Ang asset na sinusubukang mong iugnay sa lisensyang ito ay hindi umiiral.',
    'owner_doesnt_match_asset' => 'Ang asset na sinusubukan nong iugnay sa lisensyang ito ay pag-aari ng isang tao maliban sa taong piniling maitalaga sa dropdown.',
    'assoc_users'	 => 'Ang lisensyang ito ay kasalukuyang nai-check out sa isang user at hindi maaaring mai-delete. Mangyaring suriin muna na naka in ang lisensya, at pagkatapos subukang i-delete muli. ',
    'select_asset_or_person' => 'Dapat kang pumili ng isang asset o isang user, pero hindi ang dalawa.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Ang lisensya ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Ang lisensya ay matagumpay na naisagawa.'
    ),

    'deletefile' => array(
        'error'   => 'Ang file ay hindi nai-delete. Mangyaring subukang muli.',
        'success' => 'Ang file ay matagumpay nang nai-delete.',
    ),

    'upload' => array(
        'error'   => 'Ang file(s) ay hindi nai-upload. Mangyaring subukang muli.',
        'success' => 'Ang file(s) ay matagumpay na nai-upload.',
        'nofiles' => 'Hindi ka pumili ng maga files para sa i-upload, o ang file na gusto mong i-upload ay masyadong malaki',
        'invalidfiles' => 'Ang isa o higit sa iyong mga file ay masyadong malaki o isang uri ng file na hindi pinapayagan. Ang mga pinapayagang mga file ay ang png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, and lic.',
    ),

    'update' => array(
        'error'   => 'Ang lisensya ay hindi nai-update, mangyaring subukang muli',
        'success' => 'Ang lisensya ay matagumpay na nai-update.'
    ),

    'delete' => array(
        'confirm'   => 'Sigurado kaba na gusto mong i-delete ang lisensyang ito?',
        'error'   => 'Mayroong isyu sa pag-delete ng lisensya. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-delete ang lisensya.'
    ),

    'checkout' => array(
        'error'   => 'Mayroong isyu sa pag-check out ng lisensya. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-check out ang lisensya',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Mayroong isyu sa pag-check in ng lisensya. Mangyaring subukang muli.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Matagumpay na nai-check in ang lisensya'
    ),

);
