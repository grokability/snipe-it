<?php

return array(

    'deleted' => 'Raderad leverantör',
    'does_not_exist' => 'Leverantören existerar inte.',


    'create' => array(
        'error'   => 'Leverantören kunde inte skapas. Vänligen försök igen.',
        'success' => 'Leverantör skapad.'
    ),

    'update' => array(
        'error'   => 'Leverantören kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Leverantör uppdaterad.'
    ),

    'delete' => array(
        'confirm'   => 'Är du säker på att du vill radera denna leverantör?',
        'error'   => 'Det uppstod ett problem vid radering av leverantör. Var god försök igen.',
        'success' => 'Leverantör raderad.',
        'assoc_assets'	 => 'Denna leverantör är för närvarande associerad med :asset_count tillgång(ar) och kan inte tas bort. Vänligen uppdatera dina tillgångar för att inte längre referera till denna leverantör och försök igen. ',
        'assoc_licenses'	 => 'Denna leverantör är för närvarande är associerade med :licenses_count licens(er) och kan inte tas bort. Vänligen uppdatera din(a) licens(er) för att inte längre referera till denna leverantör och försök igen. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
