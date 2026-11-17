<?php

return [
    'does_not_exist' => 'La compagnie n\'existe pas.',
    'deleted' => 'Société supprimée',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'La compagnie n\'a pas été créé, veuillez réessayer.',
        'success' => 'La compagnie a bien été créée.',
    ],
    'update' => [
        'error' => 'La compagnie n\'a pas été actualisée, veuillez réessayer',
        'success' => 'La compagnie a bien été actualisée.',
    ],
    'delete' => [
        'confirm' => 'Êtes-vous sur de vouloir supprimer cette compagnie ?',
        'error' => 'Il y a eu un problème lors de la suppression de cette compagnie. Veuillez réessayer.',
        'success' => 'La compagnie a bien été supprimée.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
