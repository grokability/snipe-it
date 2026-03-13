<?php

return array(

    'does_not_exist' => 'Categoria nu exista.',
    'assoc_models'	 => 'Această categorie este în prezent asociată cu cel puțin un model și nu poate fi ștearsă. Actualizați-vă modelele astfel încât să nu mai faceți referire la această categorie și încercați din nou.',
    'assoc_items'	 => 'Această categorie este în prezent asociată cu cel puțin una: asset_type și nu poate fi ștearsă. Vă rugăm să vă actualizați: asset_type pentru a nu mai face referire la această categorie și încercați din nou.',

    'create' => array(
        'error'   => 'Categoria nu a fost creata, va rugam incercati iar.',
        'success' => 'Categoria a fost creata.'
    ),

    'update' => array(
        'error'   => 'Categoria nu a fost actualizata, va rugam incercati iar',
        'success' => 'Categoria a fost actualizata.',
        'cannot_change_category_type'   => 'Nu puteți schimba tipul categoriei odată ce a fost creat',
    ),

    'delete' => array(
        'confirm'                => 'Sunteti sigur ca vreti sa stergeti aceasta categorie?',
        'error'                  => 'A aparut o problema la stergerea categoriei. Va rugam incercati iar.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
