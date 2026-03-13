<?php

return [

    'does_not_exist' => 'Stavový štítek neexistuje.',
    'deleted_label' => 'Odstraněný název stavu',
    'assoc_assets'	 => 'Tento stavový štítek je právě přiřazena alespoň k jednomu modelu majetku a nemůže tak být odstraněn. Odeberte jeho vazbu z patřičných modelů a akci opakujte. ',

    'create' => [
        'error'   => 'Stavový štítek nebyl vytvořen, zkuste to prosím znovu.',
        'success' => 'Stavový štítek byl úspěšně vytvořen.',
    ],

    'update' => [
        'error'   => 'Stavový štítek nebyl aktualizován, zkuste to prosím znovu',
        'success' => 'Stavový štítek byl úspěšně aktualizován.',
    ],

    'delete' => [
        'confirm'   => 'Opravdu si přejete odstranit tento stavový štítek?',
        'error'   => 'Vyskytl se problém při mazání datového štítku. Zkuste to znovu prosím.',
        'success' => 'Stavový štítek byl úspěšně smazán.',
    ],

    'help' => [
        'undeployable'   => 'Tyto prostředky nelze nikomu přiřadit.',
        'deployable'   => 'Tento majetek může být vydán. Jakmile je přiřazen, změní se jeho status na <i class="fas fa-circle text-blue"></i> <strong>Vydáno</strong>.',
        'archived'   => 'Tyto prostředky nelze odhlásit a zobrazí se pouze v zobrazení Archivováno. To je užitečné pro uchovávání informací o prostředcích pro účely rozpočtování / historických účelů, ale jejich uchování mimo denní seznam aktiv.',
        'pending'   => 'Tento majetek zatím nemůže být přiřazen nikomu, často používanému pro položky, které jsou určeny k opravě, ale očekává se, že se vrátí do oběhu.',
    ],

];
