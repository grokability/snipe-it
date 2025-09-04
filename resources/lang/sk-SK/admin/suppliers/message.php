<?php

return array(

    'deleted' => 'Odstrániť dodávateľa',
    'does_not_exist' => 'Dodávateľ neexistuje.',


    'create' => array(
        'error'   => 'Dodávateľ nebol vytvorený, prosím skúste znovu.',
        'success' => 'Dodáavteľ bol úspešne vytvorený.'
    ),

    'update' => array(
        'error'   => 'Dodávateľ nebol aktualizovaný, prosím skúste znovu',
        'success' => 'Dodávateľ bol úspešne aktualizovaný.'
    ),

    'delete' => array(
        'confirm'   => 'Ste si istý, že chcete odstrániť tohto dodávateľa?',
        'error'   => 'Pri odstraňovaní doávateľa sa vyskytla chby. Skúste prosím neskôr.',
        'success' => 'Dodávateľ bol úspešne odstránený.',
        'assoc_assets'	 => 'Tento dodávateľ ma aktuálne priradené :asset_count majetky a nemôže byť odstránený. Prosím aktualizujte príslušne majetky, aby nevyužívali tohto dodávateľa a skúste znovu. ',
        'assoc_licenses'	 => 'Tento dodávateľ je aktuálne priradený :licenses_count licenciam a nemôže byť odstránený. Prosím aktualizujte príslušne licencie, aby nevyužívali tohto dodávateľa a skúste znovu. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
