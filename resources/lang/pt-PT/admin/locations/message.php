<?php

return array(

    'does_not_exist' => 'Localização não existe.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Esta localização está atualmente associada com pelo menos um artigo e não pode ser removida. Atualize este artigos de modo a não referenciarem mais este local e tente novamente. ',
    'assoc_child_loc'	 => 'Esta localização contém pelo menos uma sub-localização e não pode ser removida. Por favor, atualize as localizações para não referenciarem mais esta localização e tente novamente. ',
    'assigned_assets' => 'Artigos atribuídos',
    'current_location' => 'Localização atual',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Não foi possível criar a localização. Por favor, tente novamente.',
        'success' => 'Localização criada com sucesso.'
    ),

    'update' => array(
        'error'   => 'A localização não foi atualizada. Por favor, tente novamente',
        'success' => 'Localização atualizada com sucesso.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Localização restaurada com sucesso.'
    ),

    'delete' => array(
        'confirm'   	=> 'Tem a certeza que pretende remover esta localização?',
        'error'   => 'Ocorreu um problema ao remover esta localização. Por favor, tente novamente.',
        'success' => 'A localização foi removida com sucesso.'
    )

);
