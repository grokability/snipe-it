<?php

return array(

    'does_not_exist' => 'Cette catégorie n\'existe pas.',
    'assoc_models'	 => 'Cette catégorie est actuellement associée à au moins un modèle et ne peut pas être supprimée. Merci de mettre à jour les modèles afin de ne plus référencer cette catégorie et essayez à nouveau. ',
    'assoc_items'	 => 'Cette catégorie est actuellement associée à au moins un :asset_type et ne peut pas être supprimée. Merci de mettre à jour les :asset_type afin de ne plus référencer cette catégorie et essayez à nouveau. ',

    'create' => array(
        'error'   => 'Cette catégorie n\'a pas été créée, merci de réessayer.',
        'success' => 'Catégorie créée avec succès.'
    ),

    'update' => array(
        'error'   => 'La catégorie n\'a pas été mise à jour, merci de réessayer',
        'success' => 'Catégorie mise à jour avec succès.',
        'cannot_change_category_type'   => 'Vous ne pouvez pas modifier le type de catégorie une fois qu\'il a été créé',
    ),

    'delete' => array(
        'confirm'                => 'Êtes-vous sûr·e de vouloir supprimer cette catégorie ?',
        'error'                  => 'Il y a eu un problème lors de la suppression de cette catégorie. Merci de réessayer.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
