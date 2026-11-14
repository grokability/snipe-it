<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Piegādātājs neeksistē.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Piegādātājs netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Piegādātājs veiksmīgi izveidots.',
    ],

    'update' => [
        'error' => 'Piegādātājs netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piegādātājs ir veiksmīgi atjaunināts',
    ],

    'delete' => [
        'confirm' => 'Vai tiešām vēlaties dzēst šo piegādātāju?',
        'error' => 'Radās problēma, izlaižot piegādātāju. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Piegādātājs tika veiksmīgi dzēsts.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
