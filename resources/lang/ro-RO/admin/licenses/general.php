<?php

return array(
    'about_licenses_title'      => 'Despre Licențe',
    'about_licenses'            => 'Licențele sunt utilizate pentru a urmări software-ul. Ei au un număr specific de locuri care pot fi verificate pentru persoane fizice',
    'checkin'  					=> 'Primire locuri licenta',
    'checkout_history'  		=> 'Istoric predari',
    'checkout'  				=> 'Locuri licente predate',
    'edit'  					=> 'Editeaza licenta',
    'filetype_info'				=> 'Tipurile de fișiere permise sunt png, gif, jpg, jpeg, doc, docx, pdf, txt, zip și rar.',
    'clone'  					=> 'Cloneaza licenta',
    'history_for'  				=> 'Istoric pentru ',
    'in_out'  					=> 'Predare/Primire',
    'info'  					=> 'Info licenta',
    'license_seats'  			=> 'Locuri licenta',
    'seat'  					=> 'Loc',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Locuri',
    'software_licenses'  		=> 'Licente software',
    'user'  					=> 'Utilizator',
    'view'  					=> 'Vizualizeaza licenta',
    'delete_disabled'           => 'Această licență nu poate fi ștearsă încă deoarece unele locuri sunt încă verificate.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Verifică toate locurile',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Verificați TOATE scaunele pentru această licență atât de la utilizatori, cât și de la active',
                'disabled_tooltip'  => 'Acest lucru este dezactivat deoarece nu există locuri în prezent verificate',
                'disabled_tooltip_reassignable'  => 'Acest lucru este dezactivat deoarece licența nu este reatribuită',
                'success'           => 'Licenta a fost verificata cu succes! In toate licentele au fost verificate cu succes!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Cumpără toate locurile',
                'modal'                 => 'Această acțiune va verifica un loc pentru primul utilizator disponibil. Acest lucru va verifica toate locurile :available_seats_count pentru primii utilizatori disponibili. Se consideră că un utilizator este disponibil pentru acest scaun dacă nu are deja licența verificată la el, iar proprietatea Auto-Atribuire Licență este activată pe contul lor de utilizator.',
                'enabled_tooltip'   => 'Verificați TOATE scaunele (sau oricât de multe sunt disponibile) pentru TOȚI utilizatorii',
                'disabled_tooltip'  => 'Acest lucru este dezactivat deoarece nu există locuri disponibile în prezent',
                'success'           => 'Licența a fost verificată cu succes! Licențele de virocount au fost verificate cu succes!',
                'error_no_seats'    => 'Nu mai sunt locuri rămase pentru această licență.',
                'warn_not_enough_seats'    => 'Utilizatorii :count au fost alocați pentru această licență, dar am rămas fără locurile de licență disponibile.',
                'warn_no_avail_users'    => 'Nimic de făcut. Nu există utilizatori care să nu aibă deja această licență atribuită.',
                'log_msg'           => 'Verificat prin licenta de verificare in Licenta GUI',


            ],
    ],

    'below_threshold' => 'Mai sunt doar :remaining_count seats pentru această licență cu o cantitate minimă de :min_amt. Poate doriți să luați în considerare achiziționarea mai multor locuri.',
    'below_threshold_short' => 'Acest obiect este sub cantitatea minimă cerută.',
);
