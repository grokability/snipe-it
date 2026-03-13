<?php

return array(

    'does_not_exist' => 'L\'accessoire [:id] n\'existe pas.',
    'not_found' => 'Cet accessoire n\'a pas été trouvé.',
    'assoc_users'	 => 'Cet accessoire à présentement des items d\'attribué à des utilisateurs. S\'il vous plaît vérifier l\'accessoire et veuillez réessayer. ',

    'create' => array(
        'error'   => 'L\'accessoire n\'a pas été créé, veuillez réessayer.',
        'success' => 'L\'accessoire a bien été créé.'
    ),

    'update' => array(
        'error'   => 'L\'accessoire n\'a pas été mis-à-jour, veuillez réessayer',
        'success' => 'L\'accessoire a bien été mis-à-jour.'
    ),

    'delete' => array(
        'confirm'   => 'Etes-vous sûr de vouloir supprimer cet accessoire ?',
        'error'   => 'Un problème est survenu durant la suppression de l\'accessoire. Merci d\'essayer à nouveau.',
        'success' => 'L\'accessoire a bien été supprimé.'
    ),

     'checkout' => array(
        'error'   		=> 'Cet accessoire n\'est pas attribué. Veuillez réessayer',
        'success' 		=> 'Accessoire attribué correctement.',
        'unavailable'   => 'L\'accessoire n\'est pas disponible à l\'affectation. Vérifiez la quantité disponible',
        'user_does_not_exist' => 'Cet utilisateur est inexistant. Veuillez réessayer.',
         'checkout_qty' => array(
            'lte'  => 'Il n\'y a actuellement qu\'un seul accessoire de ce type disponible, et vous essayez d\'en attribuer :checkout_qty. Veuillez ajuster la quantité attribuée ou le stock total de cet accessoire et réessayer.|Il n\'y a actuellement que :number_currently_remaining accessoire(s) disponible(s) de ce type, et vous essayez d\'en attribuer :checkout_qty. Veuillez ajuster la quantité attribuée ou le stock total de cet accessoire et réessayer.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Cet accessoire n\'a pas été dissocié, veuillez réessayer',
        'success' 		=> 'Accessoire dissocié correctement.',
        'user_does_not_exist' => 'Cet utilisateur est inexistant. Veuillez réessayer.'
    )


);
