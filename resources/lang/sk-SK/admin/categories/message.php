<?php

return array(

    'does_not_exist' => 'Kategória neexistuje.',
    'assoc_models'	 => 'Táto kategória je priradená minimálne jednému modelu, preto nemôže byť odstránená. Prosím upravte príslušný model, aby neodkazoval na túto kategóriu a skúsne znovu. ',
    'assoc_items'	 => 'Táto kategória je priradená minimálne jednému :aset_tzpe, preto nemôže byť odstránená. Prosím upravte príslušný :asset_type, aby neodkazoval na túto kategóriu a skúsne znovu. ',

    'create' => array(
        'error'   => 'Kategória nebola vytvorená, skúste prosím znovu.',
        'success' => 'Kategória bola úspešne vytvorená.'
    ),

    'update' => array(
        'error'   => 'Kategóriu sa nepodarilo aktualizovať, skúste prosím znovu',
        'success' => 'Kategória bola úspešne aktualizovaná.',
        'cannot_change_category_type'   => 'Nie je možné upraviť kategóriu potom čo bola vytvorená',
    ),

    'delete' => array(
        'confirm'                => 'Ste si istý, že chceete odstrániť túto kategóriu?',
        'error'                  => 'Pri odstraňovaní kategórie sa vyskytla chyba. Skúste prosím znovu.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
