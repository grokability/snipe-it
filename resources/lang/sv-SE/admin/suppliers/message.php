<?php

return [

    'deleted' => 'Raderad leverantör',
    'does_not_exist' => 'Leverantören existerar inte.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Leverantören kunde inte skapas. Vänligen försök igen.',
        'success' => 'Leverantör skapad.',
    ],

    'update' => [
        'error' => 'Leverantören kunde inte uppdateras. Vänligen försök igen.',
        'success' => 'Leverantör uppdaterad.',
    ],

    'delete' => [
        'confirm' => 'Är du säker på att du vill radera denna leverantör?',
        'error' => 'Det uppstod ett problem vid radering av leverantör. Var god försök igen.',
        'success' => 'Leverantör raderad.',
        'not_found' => 'Leverantör hittades inte.',
        'bulk_success' => 'Leverantör raderad.|:count leverantörer har raderats.',
        'partial_success' => '.',
    ],

];
