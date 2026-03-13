<?php

return array(
    'about_licenses_title'      => 'Ku saabsan shatiyada',
    'about_licenses'            => 'Shatiyada waxaa loo isticmaalaa in lagu raad raaco software. Waxay haystaan ​​tiro cayiman oo kuraas ah oo laga hubin karo shakhsiyaadka',
    'checkin'  					=> 'Hubinta Kursiga Shatiga',
    'checkout_history'  		=> 'Hubi Taariikhda',
    'checkout'  				=> 'Kursiga shatiga jeegaga',
    'edit'  					=> 'Wax ka beddel shatiga',
    'filetype_info'				=> 'Noocyada faylalka la oggol yahay waa png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, iyo rar.',
    'clone'  					=> 'Shatiga Clone',
    'history_for'  				=> 'Taariikhda ',
    'in_out'  					=> 'Gudaha/kabaxsan',
    'info'  					=> 'Macluumaadka shatiga',
    'license_seats'  			=> 'Kuraasta shatiga',
    'seat'  					=> 'Kursiga',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Kuraasta',
    'software_licenses'  		=> 'Shatiyada Software',
    'user'  					=> 'Isticmaale',
    'view'  					=> 'Eeg shatiga',
    'delete_disabled'           => 'Shatigan weli lama tirtiri karo sababtoo ah kuraasta qaarkood weli waa la hubiyay.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Hubi Dhammaan Kuraasta',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Hubi dhammaan kuraasta shatigan isticmaalayaasha iyo hantida labadaba',
                'disabled_tooltip'  => 'Tani waa naafo sababtoo ah ma jiraan kuraas hadda la hubiyay',
                'disabled_tooltip_reassignable'  => 'Tani waa naafo sababtoo ah shatiga dib looma wareejin karo',
                'success'           => 'Shatiga si guul leh ayaa loo hubiyay! | Dhammaan shatiyada si guul leh ayaa loo hubiyay!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Hubi Dhammaan Kuraasta',
                'modal'                 => 'Tallaabadani waxay hubin doontaa hal kursi isticmaalayaasha ugu horreeya ee jira. | Tallaabadani waxay hubin doontaa dhammaan :available_seats_count kuraasta isticmaalayaasha ugu horreeya ee la heli karo. Isticmaale waxa loo tixgalinayaa inuu u diyaar yahay kursigan haddii aanu horeba u haysan shatigan iyaga la hubiyay, iyo shatiga Auto-Assign-ka waxa loo ogolyahay akoonkiisa isticmaale.',
                'enabled_tooltip'   => 'Hubi dhammaan kuraasta (ama inta la heli karo) DHAMMAAN isticmaalayaasha',
                'disabled_tooltip'  => 'Tani waa naafo sababtoo ah ma jiraan kuraas hadda la heli karo',
                'success'           => 'Shatiga si guul leh loo hubiyay! | :count shatiyada si guul leh ayaa loo hubiyay!',
                'error_no_seats'    => 'Ma jiraan kuraas hadhay oo shatigan u hadhay.',
                'warn_not_enough_seats'    => ':count isticmaalayaasha waxa loo qoondeeyay shatigan, laakiin waxa naga dhamaaday kuraas shatiga la heli karo.',
                'warn_no_avail_users'    => 'Wax la sameeyo. Ma jiraan isticmaaleyaal aan horay u haysan shatigan iyaga loo qoondeeyay.',
                'log_msg'           => 'Lagu hubiyay iyada oo loo marayo hubinta shatiga badan ee GUI shatiga',


            ],
    ],

    'below_threshold' => 'Kama banaana kuraastan waxaan ka ahayn :remaining_count waxaa dhici karta inaad ku fakareyso iibashada boosas kale.',
    'below_threshold_short' => 'Cunsurkan wuu ka yaryahay xadkii ugu hooseysay ee la rabay.',
);
