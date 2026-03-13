<?php

return array(

    'support_url_help' => 'Spremenljivke <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> se lahko uporabijo v vašem URL-ju, da se te vrednosti samodejno izpolnijo pri ogledu sredstev – na primer https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Proizvajalec ne obstaja.',
    'assoc_users'	 => 'Ta proizvajalec je trenutno povezan z vsaj enim modelom in ga ni mogoče izbrisati. Prosimo, posodobite svoje modele, da ne bodo vsebovali tega proizvajalca in poskusili znova. ',

    'create' => array(
        'error'   => 'Proizvajalec ni bil ustvarjen, poskusite znova.',
        'success' => 'Proizvajalec je uspešno ustvarjen.'
    ),

    'update' => array(
        'error'   => 'Proizvajalec ni bil posodobljen, poskusite znova',
        'success' => 'Proizvajalec je uspešno posodobljen.'
    ),

    'restore' => array(
        'error'   => 'Proizvajalec ni bil obnovljen, prosim poskusite ponovno',
        'success' => 'Proizvajalec uspešno obnovljen.'
    ),

    'delete' => array(
        'confirm' => 'Ali ste prepričani, da želite izbrisati tega proizvajalca?',
        'error'   => 'Pri izbrisu proizvajalca je prišlo do težave. Prosim poskusite ponovno.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
