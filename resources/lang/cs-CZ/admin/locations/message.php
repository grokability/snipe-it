<?php

return array(

    'does_not_exist' => 'Místo neexistuje.',
    'assoc_users'    => 'Toto umístění nelze aktuálně odstranit, protože je evidováno jako výchozí umístění alespoň jednoho zařízení nebo uživatele, má k sobě přiřazená zařízení, nebo je nadřazeným umístěním jiného umístění. Aktualizujte prosím záznamy tak, aby se na toto umístění již neodkazovalo, a zkuste to znovu ',
    'assoc_assets'	 => 'Toto umístění je spojeno s alespoň jedním majetkem a nemůže být smazáno. Aktualizujte majetky tak aby nenáleželi k tomuto umístění a zkuste to znovu. ',
    'assoc_child_loc'	 => 'Toto umístění je nadřazené alespoň jednomu umístění a nelze jej smazat. Aktualizujte své umístění tak, aby na toto umístění již neodkazovalo a zkuste to znovu. ',
    'assigned_assets' => 'Přiřazený majetek',
    'current_location' => 'Současné umístění',
    'open_map' => 'Otevřít v :map_provider_icon mapách',


    'create' => array(
        'error'   => 'Místo nebylo vytvořeno, zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně vytvořeno.'
    ),

    'update' => array(
        'error'   => 'Místo nebylo aktualizováno, zkuste to znovu prosím',
        'success' => 'Místo úspěšně aktualizováno.'
    ),

    'restore' => array(
        'error'   => 'Umístění nebylo obnoveno, zkuste to prosím znovu',
        'success' => 'Umístění bylo úspěšně vytvořeno.'
    ),

    'delete' => array(
        'confirm'   	=> 'Opravdu si želáte vymazat tohle místo na trvalo?',
        'error'   => 'Vyskytl se problém při mazání místa. Zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně smazáno.'
    )

);
