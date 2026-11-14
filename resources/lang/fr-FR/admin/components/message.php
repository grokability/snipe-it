<?php

return [

    'does_not_exist' => 'Le composant n\'existe pas.',

    'create' => [
        'error' => 'Le composant n\'a pas été créé, merci d\'essayer à nouveau.',
        'success' => 'Le composant a bien été créé.',
    ],

    'update' => [
        'error' => 'Le composant n\'a pas été mis à jour, merci d\'essayer à nouveau',
        'success' => 'Le composant a bien été mis à jour.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Etes-vous sûr de vouloir supprimer ce composant?',
        'error' => 'Un problème est survenu durant la suppression de ce composant. Merci d\'essayer à nouveau.',
        'success' => 'Le composant a bien été supprimé.',
        'error_qty' => 'Certains composants sont toujours dans l\'état retiré \'checked out\'.
Veuillez vérifier et recommencer',
    ],

    'checkout' => [
        'error' => 'Le composant n\'a pas été libéré, merci d\'essayer à nouveau',
        'success' => 'Le composant a bien été libéré.',
        'user_does_not_exist' => 'Cet utilisateur n\'est pas valide. Merci d\'essayer à nouveau.',
        'unavailable' => 'Pas assez de composants restants : :remaining restant, :requested requis ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Aucun champ n\'a été changé, donc rien n\'a été mis à jour.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Le composant n\'a pas été réceptionné, merci d\'essayer à nouveau',
        'success' => 'Le composant a bien été réceptionné.',
        'user_does_not_exist' => 'Cet utilisateur n\'est pas valide. Merci d\'essayer à nouveau.',
    ],

];
