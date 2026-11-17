<?php

return [
    'does_not_exist' => 'Կազմակերպությունը բացակաում է ',
    'deleted' => 'Ջնջել կազմակերպությունը',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Կազմակերպությունը չի ստեղծվել, խնդրում ենք փորձեք կրկին:',
        'success' => 'Կազմակերպությունը հաջողությամբ ստեղծվել է։',
    ],
    'update' => [
        'error' => 'Կազմակերպությունը չի թարմացվել, խնդրում ենք փորձեք կրկին:',
        'success' => 'Կազմակերպությունը հաջողությամբ թարմացվելէ :',
    ],
    'delete' => [
        'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս կազմակերպությունը ։',
        'error' => 'Կազմակերպությունը ջնջելիս խնդիր է առաջացել։ Խնդրում ենք փորձել կրկին։',
        'success' => 'Ընկերությունը հաջողությամբ ջնջվել է։',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
