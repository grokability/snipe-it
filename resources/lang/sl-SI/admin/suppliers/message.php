<?php

return [

    'deleted' => 'Izbrisan dobavitelj',
    'does_not_exist' => 'Dobavitelj ne obstaja.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Dobavitelj ni bil ustvarjen, poskusite znova.',
        'success' => 'Dobavitelj je bil uspešno ustvarjen.',
    ],

    'update' => [
        'error' => 'Dobavitelj ni bil posodobljen, poskusite znova',
        'success' => 'Dobavitelj uspešno posodabljen.',
    ],

    'delete' => [
        'confirm' => 'Ali ste prepričani, da želite izbrisati tega dobavitelja?',
        'error' => 'Prišlo je do težave z izbrisom dobavitelja. Prosim poskusite ponovno.',
        'success' => 'Dobavitelj je bil uspešno izbrisan.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
