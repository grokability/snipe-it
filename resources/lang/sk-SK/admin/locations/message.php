<?php

return array(

    'does_not_exist' => 'Lokalita neexistuje.',
    'assoc_users'    => 'Túto lokalitu momentálne nie je možné odstrániť, pretože je použitá pre aspoň jednu položku alebo používateľa, má priradené majetky alebo je nadradenou lokalitou inej lokality. Aktualizujte svoje záznamy tak, aby už neodkazovali na túto lokalitu a skúste to znova.',
    'assoc_assets'	 => 'Táto lokalita je priradená minimálne jednému majetku, preto nemôže byť odstránená. Prosím odstráňte referenciu na túto lokalitu z príslušného majetku a skúste znovu. ',
    'assoc_child_loc'	 => 'Táto lokalita je nadradenou minimálne jednej podradenej lokalite, preto nemôže byť odstránená. Prosím odstráňte referenciu s príslušnej lokality a skúste znovu. ',
    'assigned_assets' => 'Priradené položky majetku',
    'current_location' => 'Aktuálna lokalita',
    'open_map' => 'Otvoriť v :map_provider_icon mapách',
    'deleted_warning' => 'Táto lokalita bolo odstránené. Pred vykonaním akýchkoľvek zmien ju obnovte.',


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
