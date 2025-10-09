<?php

return array(

    'does_not_exist' => 'Lokalita neexistuje.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Táto lokalita je priradená minimálne jednému majetku, preto nemôže byť odstránená. Prosím odstráňte referenciu na túto lokalitu z príslušného majetku a skúste znovu. ',
    'assoc_child_loc'	 => 'Táto lokalita je nadradenou minimálne jednej podradenej lokalite, preto nemôže byť odstránená. Prosím odstráňte referenciu s príslušnej lokality a skúste znovu. ',
    'assigned_assets' => 'Priradené položky majetku',
    'current_location' => 'Aktuálna lokalita',
    'open_map' => 'Otvoriť v :map_provider_icon mapách',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


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
