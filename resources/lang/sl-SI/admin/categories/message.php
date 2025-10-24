<?php

return array(

    'does_not_exist' => 'Kategorija ne obstaja.',
    'assoc_models'	 => 'Ta kategorija je trenutno povezana z vsaj enim modelom in je ni mogoče izbrisati. Prosimo, posodobite svoje modele, da ne bodo več vsebovali te kategorije in poskusite znova. ',
    'assoc_items'	 => 'Ta kategorija je trenutno povezana z vsaj eno: asset_type in je ni mogoče izbrisati. Posodobite svoje: asset_type, da ne bo več vseboval te kategorije in poskusite znova. ',

    'create' => array(
        'error'   => 'Kategorija ni bila ustvarjena, poskusite znova.',
        'success' => 'Kategorija je bila uspešno ustvarjena.'
    ),

    'update' => array(
        'error'   => 'Kategorija ni bila posodobljena, poskusite znova',
        'success' => 'Kategorija uspešno posodobljena.',
        'cannot_change_category_type'   => 'Ne moreš spremeniti tipa kategorije po tem ko je bila kreirana',
    ),

    'delete' => array(
        'confirm'                => 'Ali ste prepričani, da želite izbrisati to kategorijo?',
        'error'                  => 'Prišlo je do težave z izbrisom kategorije. Prosim poskusite ponovno.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
