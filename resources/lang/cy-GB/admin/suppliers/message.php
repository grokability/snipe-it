<?php

return [

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Nid yw\'r cyflenwr yn bodoli.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Ni crewyd y cyflenwr, ceisiwch eto o. g. y. dd.',
        'success' => 'Cyflenwr wedi creu yn llwyddiannus.',
    ],

    'update' => [
        'error' => 'Ni ddiweddarwyd y cyflenwr, ceisiwch eto o. g. y. dd',
        'success' => 'Cyflenwr wedi diweddru\'n llwyddiannus.',
    ],

    'delete' => [
        'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r cyflenwr yma?',
        'error' => 'Roedd problem wrth ceisio dileu\'r cyflenwr. Ceisiwch eto o. g. y. dd.',
        'success' => 'Cyflenwr wedi dileu\'n llwyddiannus.',
        'not_found' => 'Supplier not found.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
