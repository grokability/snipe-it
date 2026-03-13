<?php

return array(

    'does_not_exist' => 'Príslušenstvo [:id] neexistuje.',
    'not_found' => 'Toto príslušenstvo nebolo nájdené.',
    'assoc_users'	 => 'Toto príslušenstvo má momentálne :count položiek priradených používateľom. Prosím prevezmite príslušenstvo od používateľov a skúste znovu. ',

    'create' => array(
        'error'   => 'Príslušenstvo nebolo vytvorené, skúste prosím znovu.',
        'success' => 'Príslušenstvo bolo úspešne vytvorené.'
    ),

    'update' => array(
        'error'   => 'Príslušenstvo nebolo upravené, skúste prosím znovu',
        'success' => 'Príslušenstvo bolo úspešne upravené.'
    ),

    'delete' => array(
        'confirm'   => 'Ste si istý, že chcete zmazať toto príslušenstvo?',
        'error'   => 'Pri mazaní príslušenstva nastala chyba. Skúste prosím znovu.',
        'success' => 'Príslušenstvo bolo úspešne zmazané.'
    ),

     'checkout' => array(
        'error'   		=> 'Príslušenstvo nebolo priradené, skúste prosím znovu',
        'success' 		=> 'Príslušenstvo bolo úspešne priradené.',
        'unavailable'   => 'Príslušenstvo nie je dostupné na priradenie. Overte dostupné množstvo',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
         'checkout_qty' => array(
            'lte'  => 'Aktuálne je dostupný iba jeden kus tohto príslušenstva a vy sa snažíte priradiť :checkout_qty. Prosím upravte priraďované množstvo alebo celkové zásoby tohto príslušenstva a skúste znovu.|Aktuálne sú dostupné iba :number_currently_remaining kusy tohto príslušenstva a vy sa snažíte priradiť :checkout_qty. Prosím upravte priraďované množstvo alebo celkové zásoby tohto príslušenstva a skúste znovu.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Príslušenstvo nebolo prevzaté, skúste znovu',
        'success' 		=> 'Príslušenstvo bolo úspešne prevzaté.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.'
    )


);
