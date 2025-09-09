<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Tarnijat ei eksisteeri.',


    'create' => array(
        'error'   => 'Tarnijat ei loodud, palun proovi uuesti.',
        'success' => 'Tarnija loomine õnnestus.'
    ),

    'update' => array(
        'error'   => 'Tarnijat ei uuendatud, palun proovi uuesti',
        'success' => 'Tarnija uuendamine õnnestus.'
    ),

    'delete' => array(
        'confirm'   => 'Kas oled kindel, et soovid selle tarnija kustutada?',
        'error'   => 'Tarnija kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Tarnija kustutamine õnnestus.',
        'assoc_assets'	 => 'Selle tarnijaga on seotud :asset_count vahendi(t) ja seda ei saa kustutada. Palun uuenda oma vahendeid, et need ei viitaks sellele tarnijale ning proovi uuesti. ',
        'assoc_licenses'	 => 'Selle tarnijaga on seotud :licenses_count litsents(i) ja seda ei saa kustutada. Palun uuenda oma litsentse, et need ei viitaks sellele tarnijale ning proovi uuesti. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
