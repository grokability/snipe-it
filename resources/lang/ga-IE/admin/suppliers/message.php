<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Níl an soláthraí ann.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Níor cruthaíodh an tsoláthraí, déan iarracht arís.',
        'success' => 'An soláthraí a cruthaíodh go rathúil.',
    ],

    'update' => [
        'error' => 'Níor nuashonraíodh an tsoláthraí, déan iarracht arís',
        'success' => 'Soláthraíodh an soláthraí go rathúil.',
    ],

    'delete' => [
        'confirm' => 'An bhfuil tú cinnte gur mian leat an soláthraí seo a scriosadh?',
        'error' => 'Bhí ceist ann a scriosadh an soláthraí. Arís, le d\'thoil.',
        'success' => 'Scriosadh an tsoláthraí go rathúil.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
