<?php

return [

    'deleted' => 'Fournisseur supprimé',
    'does_not_exist' => 'Le fournisseur n\'existe pas.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Le fournisseur n\'a pas été créé, veuillez essayer à nouveau.',
        'success' => 'Le fournisseur a été créé avec succès.',
    ],

    'update' => [
        'error' => 'Le fournisseur n\'a pas été mis à jour, veuillez essayer à nouveau',
        'success' => 'Le fournisseur a été mis à jour avec succès.',
    ],

    'delete' => [
        'confirm' => 'Êtes-vous sûr de vouloir supprimer ce fournisseur ?',
        'error' => 'Un problème a eu lieu pendant la suppression du fournisseur. Veuillez essayer à nouveau.',
        'success' => 'Le fournisseur a été supprimé avec succès.',
        'not_found' => 'Fournisseur non trouvé.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
