<?php

return array(

    'deleted' => 'Modèle d\'actif supprimé',
    'does_not_exist' => 'Le modèle n\'existe pas.',
    'no_association' => 'ATTENTION ! Le modèle d\'actif pour cet objet est invalide ou manquant !',
    'no_association_fix' => 'Cela va casser les choses de manière bizarre et horrible. Modifiez cette ressource maintenant pour lui assigner un modèle.',
    'assoc_users'	 => 'Ce modèle est actuellement associé à au moins un actif et ne peut pas être supprimé. Veuillez supprimer les actifs associés et essayer à nouveau. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Le modèle n\'a pas été créé, veuillez essayer à nouveau.',
        'success' => 'Le modèle a été créé avec succès.',
        'duplicate_set' => 'Un modèle d\'actif avec ce nom, fabricant et modèle existe déjà.',
    ),

    'update' => array(
        'error'   => 'Le modèle n\'a pas été mis à jour, veuillez essayer à nouveau',
        'success' => 'Le modèle a été mis à jour avec succès.',
    ),

    'delete' => array(
        'confirm'   => 'Êtes-vous sûr de vouloir supprimer ce modèle d\'actif ?',
        'error'   => 'Un problème a eu lieu pendant la suppression du modèle. Veuillez essayer à nouveau.',
        'success' => 'Le modèle a été supprimé avec succès.'
    ),

    'restore' => array(
        'error'   		=> 'Le modèle d\'actif n\'a pas été restauré, veuillez réessayer',
        'success' 		=> 'Modèle d\'actif restauré correctement.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Aucun champ n\'a été changé, donc rien n\'a été mis à jour.',
        'success' 		=> 'Modèle mis à jour avec succès. |:model_count modèles mis à jour avec succès.',
        'warn'          => 'Vous êtes sur le point de mettre à jour les propriétés du modèle suivant :|Vous êtes sur le point de modifier les propriétés des modèles :model_count suivants :',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Aucun modèle n\'a été sélectionné, donc rien n\'a été supprimé.',
        'success' 		    => 'Modèle supprimé !|:success_count modèles supprimés !',
        'success_partial' 	=> ': les modèles success_count ont été supprimés, cependant : fail_count n\'a pas pu être supprimé car ils ont toujours des ressources associées.'
    ),

);
