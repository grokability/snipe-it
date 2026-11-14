<?php

return [

    'deleted' => 'Birgja eytt',
    'does_not_exist' => 'Þessi birgir er ekki til.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Þessi birgir var ekki skráður. Vinsamlegast reyndu aftur.',
        'success' => 'Supplier created successfully.',
    ],

    'update' => [
        'error' => 'Þessi birgi var ekki skráður. Vinsamlegast reyndu aftur',
        'success' => 'Supplier updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Ertu viss um að þú viljir afskrá þennan birgi?',
        'error' => 'Það kom upp vandamál þegar reynt var að eyða birgjanum. Vinsamlegast reyndu aftur.',
        'success' => 'Supplier was deleted successfully.',
        'not_found' => 'Birgir fannst ekki.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
