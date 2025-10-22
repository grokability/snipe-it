<?php

return array(

    'does_not_exist' => 'Kategoriaa ei löydy.',
    'assoc_models'	 => 'Kategoriaan on tällä hetkellä liitettynä vähintään yksi malli, eikä sitä voi poistaa. Päivitä mallejasi, jotta ne eivät enää viittaa tähän kategoriaan ja yritä uudelleen. ',
    'assoc_items'	 => 'Tähän kategoriaan on yhdistettynä vähintään yksi :asset_type ja sitä ei voi poistaa. Poista  :asset_type viittaus tähän kategoriaan ja yritä uudelleen. ',

    'create' => array(
        'error'   => 'Kategoriaa ei luotu, yritä uudelleen.',
        'success' => 'Kategoria luotiin onnistuneesti.'
    ),

    'update' => array(
        'error'   => 'Kategoriaa ei päivitetty, yritä uudelleen',
        'success' => 'Kategoria päivitettiin onnistuneesti.',
        'cannot_change_category_type'   => 'Et voi muuttaa kategoriatyyppiä, kun se on luotu',
    ),

    'delete' => array(
        'confirm'                => 'Oletko varma että haluat poistaa tämän kategorian?',
        'error'                  => 'Kategorian poistossa tapahtui virhe. Yritä uudelleen.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
