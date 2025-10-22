<?php

return array(

    'support_url_help' => 'Variabler <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, och <code>{MODEL_NAME}</code> kan användas i din URL för att få dessa värden att automatiskt fyllas på när du visar tillgångar - till exempel https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Tillverkaren existerar inte.',
    'assoc_users'	 => 'Tillverkaren är för tillfället associerad med minst en modell och kan inte tas bort. Vänligen uppdatera dina modeller för att inte associeras med denna tillverkare och försök igen. ',

    'create' => array(
        'error'   => 'Tillverkaren kunde inte skapas. Vänligen försök igen.',
        'success' => 'Tillverkare skapad.'
    ),

    'update' => array(
        'error'   => 'Tillverkaren kunde inte uppdateras, vänligen försök igen',
        'success' => 'Tillverkare uppdaterad.'
    ),

    'restore' => array(
        'error'   => 'Tillverkaren kunde inte återskapas. Vänligen försök igen',
        'success' => 'Tillverkare återskapad.'
    ),

    'delete' => array(
        'confirm' => 'Är du säker på att du vill ta bort denna tillverkare?',
        'error'   => 'Det gick inte att ta bort tillverkaren. Vänligen försök igen.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
