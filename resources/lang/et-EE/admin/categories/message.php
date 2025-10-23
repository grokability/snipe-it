<?php

return array(

    'does_not_exist' => 'Kategooria puudub.',
    'assoc_models'	 => 'Selle kategooriaga on seotud vähemalt üks mudel ja seda ei saa kustutada. Palun uuendage oma mudeleid, et need ei kasutaks seda kategooriat ning seejärel proovige uuesti. ',
    'assoc_items'	 => 'Selle kategooriaga on seotud vähemalt üks :asset_type ja seda ei saa kustutada. Palun uuendage oma :asset_typr, et too ei kasutaks seda kategooriat ning seejärel proovige uuesti. ',

    'create' => array(
        'error'   => 'Kategooriat ei loodud, proovi uuesti.',
        'success' => 'Kategooria loomine õnnestus.'
    ),

    'update' => array(
        'error'   => 'Kategooriat ei uuendatud, proovige uuesti',
        'success' => 'Kategooria uuendamine õnnestus.',
        'cannot_change_category_type'   => 'You cannot change the category type once it has been created',
    ),

    'delete' => array(
        'confirm'                => 'Kas oled kindel, et soovid selle kategooria kustutada?',
        'error'                  => 'Kategooria kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
