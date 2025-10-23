<?php

return array(

    'does_not_exist' => 'Kategorien eksisterer ikke.',
    'assoc_models'	 => 'Denne kategori er i øjeblikket associeret med mindst en model og kan ikke slettes. Opdater venligst dine modeller, så de ikke længere refererer til denne kategori og prøv igen. ',
    'assoc_items'	 => 'Denne kategori er i øjeblikket associeret med mindst en :asset_type og kan ikke slettes. Opdater venligst din :asset_type, så de ikke længere refererer til denne kategori og prøv igen. ',

    'create' => array(
        'error'   => 'Kategorien blev ikke oprettet, prøv igen.',
        'success' => 'Kategorien blev oprettet.'
    ),

    'update' => array(
        'error'   => 'Kategorien blev ikke opdateret, prøv igen',
        'success' => 'Kategorien blev opdateret.',
        'cannot_change_category_type'   => 'Du kan ikke ændre kategoritypen, når først den er blevet oprettet',
    ),

    'delete' => array(
        'confirm'                => 'Er du sikker på at du vil slette denne kategori?',
        'error'                  => 'Der opstod et problem under sletning af kategorien. Prøv igen.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
