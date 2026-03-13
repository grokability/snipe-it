<?php

return array(

    'does_not_exist' => 'Kategorie bestaan ​​nie.',
    'assoc_models'	 => 'Hierdie kategorie is tans gekoppel aan ten minste een model en kan nie uitgevee word nie. Dateer asseblief jou modelle op om nie meer hierdie kategorie te verwys nie en probeer weer.',
    'assoc_items'	 => 'Hierdie kategorie word tans geassosieer met ten minste een: asset_type en kan nie uitgevee word nie. Werk asseblief jou: bat_type op om nie meer hierdie kategorie te verwys nie en probeer weer.',

    'create' => array(
        'error'   => 'Kategorie is nie geskep nie, probeer asseblief weer.',
        'success' => 'Kategorie geskep suksesvol.'
    ),

    'update' => array(
        'error'   => 'Kategorie is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Kategorie suksesvol opgedateer.',
        'cannot_change_category_type'   => 'You cannot change the category type once it has been created',
    ),

    'delete' => array(
        'confirm'                => 'Is jy seker jy wil hierdie kategorie uitvee?',
        'error'                  => 'Daar was \'n probleem met die verwydering van die kategorie. Probeer asseblief weer.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
