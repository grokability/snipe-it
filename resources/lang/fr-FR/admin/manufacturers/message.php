<?php

return array(

    'support_url_help' => 'Les variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, et <code>{MODEL_NAME}</code> peuvent être utilisés dans votre URL pour que ces valeurs soient remplies automatiquement lors de la visualisation des ressources - par exemple https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Le fabricant n\'existe pas.',
    'assoc_users'	 => 'Ce fabricant est actuellement associé avec au moins un modèle et ne peut pas être supprimé. Veuillez mettre à jour les modèles pour ne plus référencer un fabricant et essayer à nouveau. ',

    'create' => array(
        'error'   => 'Le fabricant n\'a pas été créé, veuillez essayer à nouveau.',
        'success' => 'Le fabricant a été créé avec succès.'
    ),

    'update' => array(
        'error'   => 'Le fabricant n\'a pas été mis à jour, veuillez essayer à nouveau',
        'success' => 'Le fabricant a été mis à jour avec succès.'
    ),

    'restore' => array(
        'error'   => 'Le fabricant n\'a pas été restauré, veuillez réessayer',
        'success' => 'Fabricant restauré avec succès.'
    ),

    'delete' => array(
        'confirm' => 'Êtes-vous sûr de vouloir supprimer ce fabricant ?',
        'error'   => 'Un problème a eu lieu pendant la suppression du fabricant. Veuillez essayer à nouveau.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
