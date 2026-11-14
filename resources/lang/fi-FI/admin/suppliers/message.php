<?php

return [

    'deleted' => 'Poistettu toimittaja',
    'does_not_exist' => 'Toimittajaa ei löydy.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Toimittajaa ei luotu, yritä uudelleen.',
        'success' => 'Toimittaja luotiin onnistuneesti.',
    ],

    'update' => [
        'error' => 'Toimittajaa ei päivitetty, yritä uudelleen',
        'success' => 'Toimittaja päivitettiin onnistuneesti.',
    ],

    'delete' => [
        'confirm' => 'Oletko varma että haluat poistaa tämän toimittajan?',
        'error' => 'Toimittajan poistossa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Toimittaja poistettiin onnistuneesti.',
        'not_found' => 'Toimittajaa ei löydy.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
