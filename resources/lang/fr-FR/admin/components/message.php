<?php

return array(

    'does_not_exist' => 'Le composant n\'existe pas.',

    'create' => array(
        'error'   => 'Le composant n\'a pas été créé, merci d\'essayer à nouveau.',
        'success' => 'Le composant a bien été créé.'
    ),

    'update' => array(
        'error'   => 'Le composant n\'a pas été mis à jour, merci d\'essayer à nouveau',
        'success' => 'Le composant a bien été mis à jour.'
    ),

    'delete' => array(
        'confirm'   => 'Etes-vous sûr de vouloir supprimer ce composant?',
        'error'   => 'Un problème est survenu durant la suppression de ce composant. Merci d\'essayer à nouveau.',
        'success' => 'Le composant a bien été supprimé.',
        'error_qty'   => 'Certains composants sont toujours dans l\'état retiré \'checked out\'.
Veuillez vérifier et recommencer',
    ),

     'checkout' => array(
        'error'   		=> 'Le composant n\'a pas été libéré, merci d\'essayer à nouveau',
        'success' 		=> 'Le composant a bien été libéré.',
        'user_does_not_exist' => 'Cet utilisateur n\'est pas valide. Merci d\'essayer à nouveau.',
        'unavailable'      => 'Pas assez de composants restants : :remaining restant, :requested requis ',
    ),

    'checkin' => array(
        'error'   		=> 'Le composant n\'a pas été réceptionné, merci d\'essayer à nouveau',
        'success' 		=> 'Le composant a bien été réceptionné.',
        'user_does_not_exist' => 'Cet utilisateur n\'est pas valide. Merci d\'essayer à nouveau.'
    )


);
