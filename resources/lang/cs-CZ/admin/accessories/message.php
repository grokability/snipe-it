<?php

return array(

    'does_not_exist' => 'Příslušenství [:id] neexistuje.',
    'not_found' => 'Toto příslušenství nebylo nalezeno.',
    'assoc_users'	 => 'Tato kategorie má nyní :count položek k předání uživatelům. Zkontrolujte převzetí příslušenství a zkuste to znovu. ',

    'create' => array(
        'error'   => 'Doplněk nebyl vytvořen, prosím zkuste to znovu.',
        'success' => 'Doplněk byl úspěšně vytvořen.'
    ),

    'update' => array(
        'error'   => 'Doplněk nebyl upraven, prosím zkuste to znovu',
        'success' => 'Doplněk byl úspěšně upraven.'
    ),

    'delete' => array(
        'confirm'   => 'Jste si jisti, že chcete odstranit toto příslušenství?',
        'error'   => 'Vyskytl se problém při mazání kategorie. Zkuste to znovu prosím.',
        'success' => 'Příslušenství bylo úspěšně odstraněno.'
    ),

     'checkout' => array(
        'error'   		=> 'Příslušenství nebylo převzato, zkuste to znovu',
        'success' 		=> 'Příslušenství úspěšně předáno.',
        'unavailable'   => 'Příslušenství nelze vydat. Zkontrolujte skladové zásoby.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to znovu.',
         'checkout_qty' => array(
            'lte'  => 'V současné době je k dispozici pouze jeden doplněk tohoto příslušenství a snažíte se vydat :checkout_qty. Upravte prosím množství k výdeji nebo celkový počet dostupných doplňků a zkuste to znovu.
K dispozici je celkem :number_currently_remaining doplňků, přičemž se pokoušíte vydat :checkout_qty. Upravte prosím množství k výdeji nebo celkový počet a zkuste to znovu.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Příslušenství nebylo převzato, zkuste to znovu',
        'success' 		=> 'Příslušenství úspěšně předáno.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to znovu.'
    )


);
