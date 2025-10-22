<?php

return array(

    'does_not_exist' => 'A kategória nem létezik.',
    'assoc_models'	 => 'Ez a kategória jelenleg legalább egy modellhez kapcsolódik, és nem törölhető. Kérjük, frissítse a modelleket, hogy ne hivatkozzon erre a kategóriára, és próbálja újra.',
    'assoc_items'	 => 'Ez a kategória jelenleg legalább egy: asset_type-hez van társítva, és nem törölhető. Kérjük, frissítse a: asset_type-t, hogy ne hivatkozzon erre a kategóriára és próbálja újra.',

    'create' => array(
        'error'   => 'Nem sikerült a kategória létrehozása, kérjük, próbálja újra.',
        'success' => 'Sikeresen létrehozta a kategóriát.'
    ),

    'update' => array(
        'error'   => 'Nem sikerült a kategória módosítása, kérjük, próbálja újra',
        'success' => 'Sikeresen módosította a kategóriát.',
        'cannot_change_category_type'   => 'Létrehozás után nem tudod megváltoztatni a kategória tipusát',
    ),

    'delete' => array(
        'confirm'                => 'Biztos benne, hogy törölni szeretné a kategóriát?',
        'error'                  => 'A kategória törlése közben probléma merült fel, kérjük, próbálja újra.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
