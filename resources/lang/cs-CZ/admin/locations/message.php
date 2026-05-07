<?php

return [

    'does_not_exist' => 'Místo neexistuje.',
    'assoc_users' => 'Toto umístění momentálně nelze smazat, protože je použito pro nejméně jednu položku nebo uživatele, jsou k němu přiřazena zařízení nebo je nadřazené jinému umístění. Prosím upravte své záznamy tak, aby toto umístění nebylo odkazováno a zkuste to znovu ',
    'assoc_assets' => 'Toto umístění je spojeno s alespoň jedním majetkem a nemůže být smazáno. Aktualizujte majetky tak aby nenáleželi k tomuto umístění a zkuste to znovu. ',
    'assoc_child_loc' => 'Toto umístění je nadřazené alespoň jednomu umístění a nelze jej smazat. Aktualizujte své umístění tak, aby na toto umístění již neodkazovalo a zkuste to znovu. ',
    'assigned_assets' => 'Přiřazený majetek',
    'current_location' => 'Současné umístění',
    'deleted_warning' => 'Toto umístění bylo smazáno. Před jakýmkoli pokusem o změny je prosím obnovte.',

    'create' => [
        'error' => 'Místo nebylo vytvořeno, zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně vytvořeno.',
    ],

    'update' => [
        'error' => 'Místo nebylo aktualizováno, zkuste to znovu prosím',
        'success' => 'Místo úspěšně aktualizováno.',
    ],

    'restore' => [
        'error' => 'Umístění nebylo obnoveno, zkuste to prosím znovu',
        'success' => 'Umístění bylo úspěšně vytvořeno.',
    ],

    'delete' => [
        'confirm' => 'Opravdu si želáte vymazat tohle místo na trvalo?',
        'error' => 'Vyskytl se problém při mazání místa. Zkuste to znovu prosím.',
        'success' => 'Místo bylo úspěšně smazáno.',
    ],

];
