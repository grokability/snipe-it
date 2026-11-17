<?php

return [
    'does_not_exist' => 'Empresa não existe.',
    'deleted' => 'Empresa excluída',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Empresa não criada, por favor tente de novo.',
        'success' => 'Empresa criada com sucesso.',
    ],
    'update' => [
        'error' => 'Empresa não foi atualizada, tente novamente',
        'success' => 'Empresa atualizada com sucesso.',
    ],
    'delete' => [
        'confirm' => 'Tem a certeza que deseja eliminar está empresa?',
        'error' => 'Existe um problema ao eliminar a empresa. Por favor tente de novo.',
        'success' => 'A empresa foi eliminada com sucesso.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
