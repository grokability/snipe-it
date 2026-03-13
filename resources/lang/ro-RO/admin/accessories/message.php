<?php

return array(

    'does_not_exist' => 'Accesoriul [:id] nu există.',
    'not_found' => 'Acel accesoriu nu a fost găsit.',
    'assoc_users'	 => 'Acest accesoriu are în prezent : count elemente predate la utilizatori. Vă rugăm să verificaţi accesoriile și încercați din nou. ',

    'create' => array(
        'error'   => 'Accesoriul nu a fost adaugat, va rugam incercati din nou.',
        'success' => 'Accesoriu adaugat cu succes.'
    ),

    'update' => array(
        'error'   => 'Accesoriul nu a fost actualizat, va rugam incercati din nou,',
        'success' => 'Accesoriu actualizat cu succes.'
    ),

    'delete' => array(
        'confirm'   => 'Sigur doriți să ștergeți acest accesoriu?',
        'error'   => 'A apărut o problemă la ştergerea accesoriului. Vă rugăm să încercaţi din nou.',
        'success' => 'Accesoriul a fost şters cu succes.'
    ),

     'checkout' => array(
        'error'   		=> 'Accesoriu nu a fost predat, vă rugăm să încercaţi din nou',
        'success' 		=> 'Accesoriu a fost predat.',
        'unavailable'   => 'Accesoriul nu este disponibil pentru checkout. Verificați cantitatea disponibilă',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Vă rugăm să încercaţi din nou.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Accesoriul nu a fost primit, vă rugăm să încercaţi din nou',
        'success' 		=> 'Accesoriu primit cu succes.',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Vă rugăm să încercaţi din nou.'
    )


);
