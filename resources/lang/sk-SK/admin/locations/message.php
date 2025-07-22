<?php

return array(

    'does_not_exist' => 'Lokalita neexistuje.',
    'assoc_users'    => 'Túto lokalitu nie je možné odstrániť nakoľko je využívaná pri minimálne jednom majetku alebo užívateľovi, má priradené majetky alebo je nadradenou lokalitou inej lokality. Prosím upravte ostatné záznamy, aby nevyužívali túto lokalitu a skúste znovu ',
    'assoc_assets'	 => 'Táto lokalita je priradená minimálne jednému majetku, preto nemôže byť odstránená. Prosím odstráňte referenciu na túto lokalitu z príslušného majetku a skúste znovu. ',
    'assoc_child_loc'	 => 'Táto lokalita je nadradenou minimálne jednej podradenej lokalite, preto nemôže byť odstránená. Prosím odstráňte referenciu s príslušnej lokality a skúste znovu. ',
    'assigned_assets' => 'Priradené položky majetku',
    'current_location' => 'Aktuálna lokalita',
    'open_map' => 'Otvoriť v :map_provider_icon mapách',


    'create' => array(
        'error'   => 'Lokalita nebola vytvorená, skúste prosím znovu.',
        'success' => 'Lokalita bola úspešne vytovrená.'
    ),

    'update' => array(
        'error'   => 'Lokalita nebola aktualizovaná, skúste prosím znovu',
        'success' => 'Lokalita bola úspešne upravená.'
    ),

    'restore' => array(
        'error'   => 'Lokalita nebola obnovená, prosím skúste znovu',
        'success' => 'Lokalita bola úspešne obnovená.'
    ),

    'delete' => array(
        'confirm'   	=> 'Ste si istý, že chcete odstrániť túto lokalitu?',
        'error'   => 'Pri odstraňovaní lokality nastala chyba. Skúste prosím znovu.',
        'success' => 'Lokalita bola úspešne odstránená.'
    )

);
