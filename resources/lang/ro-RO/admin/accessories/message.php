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

return array(
    'does_not_exist' => 'Accesoriul [:id] nu există.',
    'not_found' => 'Acel accesoriu nu a fost găsit.',
    'assoc_users' => 'Acest accesoriu are în prezent :count elemente alocate utilizatorilor. Te rugăm să returnezi accesoriile și să încerci din nou.',

    'create' => array(
        'error' => 'Accesoriul nu a fost adăugat. Te rugăm să încerci din nou.',
        'success' => 'Accesoriul a fost adăugat cu succes.'
    ),

    'update' => array(
        'error' => 'Accesoriul nu a fost actualizat. Te rugăm să încerci din nou.',
        'success' => 'Accesoriul a fost actualizat cu succes.'
    ),

    'delete' => array(
        'confirm' => 'Ești sigur că vrei să ștergi acest accesoriu?',
        'error' => 'A apărut o problemă la ștergerea accesoriului. Te rugăm să încerci din nou.',
        'success' => 'Accesoriul a fost șters cu succes.'
    ),

    'checkout' => array(
        'error' => 'Accesoriul nu a fost alocat. Te rugăm să încerci din nou.',
        'success' => 'Accesoriul a fost alocat cu succes.',
        'unavailable' => 'Accesoriul nu este disponibil pentru alocare. Verifică cantitatea disponibilă.',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Te rugăm să încerci din nou.',
        'checkout_qty' => array(
            'lte' => 'În prezent există un singur accesoriu disponibil de acest tip, iar tu încerci să aloci :checkout_qty. Te rugăm să ajustezi cantitatea de alocat sau stocul total al acestui accesoriu și să încerci din nou.|Există un total de :number_currently_remaining accesorii disponibile, iar tu încerci să aloci :checkout_qty. Te rugăm să ajustezi cantitatea de alocat sau stocul total al acestui accesoriu și să încerci din nou.',
        ),
    ),

    'checkin' => array(
        'error' => 'Accesoriul nu a fost primit. Te rugăm să încerci din nou.',
        'success' => 'Accesoriul a fost primit cu succes.',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Te rugăm să încerci din nou.'
    )
);