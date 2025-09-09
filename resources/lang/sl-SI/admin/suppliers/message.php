<?php

return array(

    'deleted' => 'Izbrisan dobavitelj',
    'does_not_exist' => 'Dobavitelj ne obstaja.',


    'create' => array(
        'error'   => 'Dobavitelj ni bil ustvarjen, poskusite znova.',
        'success' => 'Dobavitelj je bil uspešno ustvarjen.'
    ),

    'update' => array(
        'error'   => 'Dobavitelj ni bil posodobljen, poskusite znova',
        'success' => 'Dobavitelj uspešno posodabljen.'
    ),

    'delete' => array(
        'confirm'   => 'Ali ste prepričani, da želite izbrisati tega dobavitelja?',
        'error'   => 'Prišlo je do težave z izbrisom dobavitelja. Prosim poskusite ponovno.',
        'success' => 'Dobavitelj je bil uspešno izbrisan.',
        'assoc_assets'	 => 'Ta dobavitelj je trenutno povezan z: asset_count sredstvi in ga ni mogoče izbrisati. Prosimo, posodobite svoja sredstva, da ne bodo več vsebovala tega dobavitelja in poskusite znova. ',
        'assoc_licenses'	 => 'Ta dobavitelj je trenutno povezan z :licence_count licencami in ga ni mogoče izbrisati. Prosimo, posodobite svoje licence, da ne bodo več vsebovale tega dobavitelja in poskusite znova. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
