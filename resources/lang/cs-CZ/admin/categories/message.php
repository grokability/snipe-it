<?php

return array(

    'does_not_exist' => 'Kategorie neexistuje.',
    'assoc_models'	 => 'Tato kategorie obsahuje nejméně jeden model majetku a nemůže být proto odstraněna. Změňte kategorii v dotyčných modelech majetku a zkuste to znovu. ',
    'assoc_items'	 => 'Tato kategorie obsahuje nejméně jeden :asset_type a nemůže být proto odstraněna. Změňte kategorii v dotyčných :asset_type a zkuste to znovu. ',

    'create' => array(
        'error'   => 'Kategorie nebyla vytvořena, zkuste to znovu prosím.',
        'success' => 'Kategorie byla úspěšně vytvořena.'
    ),

    'update' => array(
        'error'   => 'Kategorie nebyla aktualizována, zkuste to znovu prosím',
        'success' => 'Kategorie aktualizována úspěšně.',
        'cannot_change_category_type'   => 'Jakmile byla kategorie vytvořena, nelze měnit její typ',
    ),

    'delete' => array(
        'confirm'                => 'Opravdu chcete smazat tuto kategorii na trvalo?',
        'error'                  => 'Vyskytl se problém při mazání kategorie. Zkuste to znovu prosím.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
