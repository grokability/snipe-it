<?php

return array(

    'deleted' => 'Smazaný dodavatel',
    'does_not_exist' => 'Dodavatel neexistuje.',


    'create' => array(
        'error'   => 'Dodavatel nebyl vytvořen, zkuste to prosím znovu.',
        'success' => 'Dodavatel úspěšně vytvořen.'
    ),

    'update' => array(
        'error'   => 'Dodavatel nebyl aktualizován, zkuste to prosím znovu',
        'success' => 'Dodavatel úspěšně aktualizován.'
    ),

    'delete' => array(
        'confirm'   => 'Opravdu si přejete odstranit tohoto dodavatele?',
        'error'   => 'Vyskytl se problém při mazání dodavatele. Zkuste to prosím znovu.',
        'success' => 'Dodavatel byl úspěšně smazán.',
        'assoc_assets'	 => 'Tento dodavatel je v současné době přiřazen k :asset_count položkám majetku a nelze jej smazat. Aktualizujte svůj majetek tak, aby již tento dodavatel nebyl přiřazen a zkuste to znovu. ',
        'assoc_licenses'	 => 'Tento dodavatel je v současné době spojen s :licenses_count licencemi a nelze jej smazat. Aktualizujte prosím své licence, abyste již tento dodavatel nebyl přiřazen a zkuste to znovu. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
