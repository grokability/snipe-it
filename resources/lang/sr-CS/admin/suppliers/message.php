<?php

return array(

    'does_not_exist' => 'Dobavljač ne postoji.',


    'create' => array(
        'error'   => 'Dobavljač nije kreiran, pokušajte ponovo.',
        'success' => 'Dobavljač je uspešno kreiran.'
    ),

    'update' => array(
        'error'   => 'Dobavljač nije ažuriran, pokušajte ponovo',
        'success' => 'Dobavljač je uspešno ažuriran.'
    ),

    'delete' => array(
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovog dobavljača?',
        'error'   => 'Došlo je do problema sa brisanjem dobavljača. Molim pokušajte ponovo.',
        'success' => 'Dobavljač je uspešno izbrisan.',
        'assoc_assets'	 => 'This supplier is currently associated with :asset_count asset(s) and cannot be deleted. Please update your assets to no longer reference this supplier and try again. ',
        'assoc_licenses'	 => 'This supplier is currently associated with :licenses_count licences(s) and cannot be deleted. Please update your licenses to no longer reference this supplier and try again. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :asset_maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
