<?php

return array(

    'does_not_exist' => 'Le lieu n\'existe pas.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Cet emplacement est actuellement associé à au moins un actif et ne peut pas être supprimé. Veuillez mettre à jour vos actifs pour ne plus faire référence à cet emplacement et réessayez. ',
    'assoc_child_loc'	 => 'Cet emplacement est actuellement le parent d\'au moins un sous emplacement et ne peut pas être supprimé . S\'il vous plaît mettre à jour vos emplacement pour ne plus le référencer et réessayez. ',
    'assigned_assets' => 'Actifs assignés',
    'current_location' => 'Emplacement actuel',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Le lieu n\'a pas été créé, veuillez essayer à nouveau.',
        'success' => 'Le lieu a été créé avec succès.'
    ),

    'update' => array(
        'error'   => 'Le lieu n\'a pas été mis à jour, veuillez essayer à nouveau',
        'success' => 'Le lieu a été mis à jour avec succès.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Êtes-vous sûr de vouloir supprimer ce lieu ?',
        'error'   => 'Un problème a eu lieu pendant la suppression du lieu. Veuillez essayer à nouveau.',
        'success' => 'Le lieu a été supprimé avec succès.'
    )

);
