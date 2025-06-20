<?php

return array(
    'about_licenses_title'      => 'Despre Licențe',
    'about_licenses'            => 'Licențele sunt utilizate pentru a urmări software-ul. Ele au un număr specificat de utilizatori care pot fi alocați individual.',
    'checkin'                   => 'Returnează utilizator licență',
    'checkout_history'          => 'Istoric alocări',
    'checkout'                  => 'Alocă utilizator licență',
    'edit'                      => 'Editează licența',
    'filetype_info'             => 'Tipurile de fișiere permise sunt png, gif, jpg, jpeg, doc, docx, pdf, txt, zip și rar.',
    'clone'                     => 'Clonează licența',
    'history_for'               => 'Istoric pentru ',
    'in_out'                    => 'Intrare/Ieșire',
    'info'                      => 'Informații licență',
    'license_seats'             => 'Utilizatori licență',
    'seat'                      => 'Utilizator',
    'seat_count'                => 'Utilizator :count',
    'seats'                     => 'Utilizatori',
    'software_licenses'         => 'Licențe software',
    'user'                      => 'Utilizator',
    'view'                      => 'Vizualizează licența',
    'delete_disabled'           => 'Această licență nu poate fi încă ștearsă deoarece unii utilizatori sunt încă alocați.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Returnează toți utilizatorii',
                'modal'             => 'Această acțiune va returna un utilizator. | Această acțiune va returna toți cei :checkedout_seats_count utilizatori pentru această licență.',
                'enabled_tooltip'   => 'Returnează TOȚI utilizatorii pentru această licență, atât de la utilizatori, cât și de la active',
                'disabled_tooltip'  => 'Această opțiune este dezactivată deoarece nu există utilizatori alocați în prezent',
                'disabled_tooltip_reassignable' => 'Această opțiune este dezactivată deoarece licența nu este realocabilă',
                'success'           => 'Licență returnată cu succes! | Toate licențele au fost returnate cu succes!',
                'log_msg'           => 'Returnat prin returnarea în masă a licențelor din interfața grafică a licențelor',
            ],

            'checkout_all'              => [
                'button'            => 'Alocă toți utilizatorii',
                'modal'             => 'Această acțiune va aloca un utilizator primului utilizator disponibil. | Această acțiune va aloca toți cei :available_seats_count utilizatori primilor utilizatori disponibili. Un utilizator este considerat disponibil pentru acest loc dacă nu are deja această licență alocată și proprietatea Auto-Alocare Licență este activată în contul său de utilizator.',
                'enabled_tooltip'   => 'Alocă TOȚI utilizatorii (sau câți sunt disponibili) TUTUROR utilizatorilor',
                'disabled_tooltip'  => 'Această opțiune este dezactivată deoarece nu există utilizatori disponibili în prezent',
                'success'           => 'Licență alocată cu succes! | :count licențe au fost alocate cu succes!',
                'error_no_seats'    => 'Nu mai sunt utilizatori rămași pentru această licență.',
                'warn_not_enough_seats' => ':count utilizatori au primit această licență, dar am rămas fără utilizatori disponibili pentru licență.',
                'warn_no_avail_users' => 'Nimic de făcut. Nu există utilizatori care să nu aibă deja această licență alocată.',
                'log_msg'           => 'Alocat prin alocarea în masă a licențelor din interfața grafică a licențelor',

            ],
    ],

    'below_threshold'       => 'Mai sunt doar :remaining_count utilizatori rămași pentru această licență, cu o cantitate minimă de :min_amt. Poate dorești să iei în considerare achiziționarea mai multor utilizatori.',
    'below_threshold_short' => 'Acest element este sub cantitatea minimă necesară.',
);