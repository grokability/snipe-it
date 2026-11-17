<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Hindi umiiral ang tagapagsuplay.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Ang tagapagsuplay ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Matagumpay na naisagawa nga tagapagsuplay.',
    ],

    'update' => [
        'error' => 'Hindi nai-update ang tagapagsuplay, mangyaring subukang muli',
        'success' => 'Matagumpay na nai-update ang tagapagsuplay.',
    ],

    'delete' => [
        'confirm' => 'Sigurado kaba na gusto mong i-delete ang tagapagsuplay na ito?',
        'error' => 'Mayroong isyu sa pag-delete ng tagapagsuplay. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-delete ang tagapagsuplay.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
