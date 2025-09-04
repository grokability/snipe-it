<?php

return array(

    'does_not_exist' => 'Komponent neexistuje.',

    'create' => array(
        'error'   => 'Komponent sa nepodarilo pridať, skúste znovu.',
        'success' => 'Komponent bol úspešne pridaný.'
    ),

    'update' => array(
        'error'   => 'Komponent sa nepodarilo upraviť, skúste znovu',
        'success' => 'Komponent bol úspešne upravený.'
    ),

    'delete' => array(
        'confirm'   => 'Ste si istý, že chcete zmazať tento komponent?',
        'error'   => 'Nastal problém pri mazani komponentu. Prosím skúste znovu.',
        'success' => 'Komponent bol úspešne zmazaný.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Komponent nebol priradený, prosím skúste znovu',
        'success' 		=> 'Komponent bol úspešne priradený.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'unavailable'      => 'Nedostatok komponentov: :remaining skladom, :requested vyžiadaných ',
    ),

    'checkin' => array(
        'error'   		=> 'Komponent nebol prevzatý, skúste znovu',
        'success' 		=> 'Komponent bol úspešne prevzatý.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.'
    )


);
