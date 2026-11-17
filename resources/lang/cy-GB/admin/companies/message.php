<?php

return [
    'does_not_exist' => 'Nid ywr cwmni\'n bodoli.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Ni crewyd y cwmni, ceisiwch eto o. g. y. dd.',
        'success' => 'Cwmni wedi creu yn llwyddiannus.',
    ],
    'update' => [
        'error' => 'Ni diweddarwyd y cwmni, ceisiwch eto o. g. y. dd',
        'success' => 'Cwmni wedi diweddaru\'n llwyddiannus.',
    ],
    'delete' => [
        'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r cwmni yma?',
        'error' => 'Nid oedd yn bosib dileu\'r cwmni. Ceisiwch eto o. g. y. dd.',
        'success' => 'Cwmni wedi dileu\'n llwyddiannus.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
