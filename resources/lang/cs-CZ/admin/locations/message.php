<?php

return array(

    'does_not_exist' => 'Místo neexistuje.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one asset or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Toto umístění je spojeno s alespoň jedním majetkem a nemůže být smazáno. Aktualizujte majetky tak aby nenáleželi k tomuto umístění a zkuste to znovu. ',
    'assoc_child_loc'	 => 'Toto umístění je nadřazené alespoň jednomu umístění a nelze jej smazat. Aktualizujte své umístění tak, aby na toto umístění již neodkazovalo a zkuste to znovu. ',
    'assigned_assets' => 'Přiřazený majetek',
    'current_location' => 'Současné umístění',
    'open_map' => 'Open in :map_provider_icon Maps',


    'create' => array(
        'error'   => 'Místo nebylo vytvořeno, zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně vytvořeno.'
    ),

    'update' => array(
        'error'   => 'Místo nebylo aktualizováno, zkuste to znovu prosím',
        'success' => 'Místo úspěšně aktualizováno.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Opravdu si želáte vymazat tohle místo na trvalo?',
        'error'   => 'Vyskytl se problém při mazání místa. Zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně smazáno.'
    )

);
