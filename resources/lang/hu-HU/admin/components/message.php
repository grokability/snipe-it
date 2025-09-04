<?php

return array(

    'does_not_exist' => 'Az alkatrész nem létezik.',

    'create' => array(
        'error'   => 'Összetevő nem jött létre, próbálkozz újra.',
        'success' => 'Az alkatrész sikeresen létrejött.'
    ),

    'update' => array(
        'error'   => 'Az alkatrész nem frissült, próbálkozz újra',
        'success' => 'Az alkatrész sikeresen létrejött.'
    ),

    'delete' => array(
        'confirm'   => 'Biztosan törölni szeretnéd az alkatrészt?',
        'error'   => 'Probléma támadt a vállalat törlésével. Próbálkozz újra.',
        'success' => 'Az alkatrész sikeresen törlődött.',
        'error_qty'   => 'Néhány ilyen típusú alkatrész még ki van adva. Kérjük, előbb végezze el azok visszavételét, majd próbálja újra.',
    ),

     'checkout' => array(
        'error'   		=> 'Az alkatrész nem lett kiadva, próbálkozz újra',
        'success' 		=> 'Az alkatrész sikeresen kiadva.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.',
        'unavailable'      => 'Nem marad elég alkatrész: :remaining marad, :requested igényelve ',
    ),

    'checkin' => array(
        'error'   		=> 'Az alkatrész nem lett visszavéve, próbálkozz újra',
        'success' 		=> 'Az alkatrész sikeresen visszavéve.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.'
    )


);
