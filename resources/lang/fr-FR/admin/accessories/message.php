<?php

return [

    'does_not_exist' => 'L\'accessoire [:id] n\'existe pas.',
    'not_found' => 'Cet accessoire n\'a pas été trouvé.',
    'assoc_users' => 'Cet accessoire à présentement des items d\'attribué à des utilisateurs. S\'il vous plaît vérifier l\'accessoire et veuillez réessayer. ',

    'create' => [
        'error' => 'L\'accessoire n\'a pas été créé, veuillez réessayer.',
        'success' => 'L\'accessoire a bien été créé.',
    ],

    'update' => [
        'error' => 'L\'accessoire n\'a pas été mis-à-jour, veuillez réessayer',
        'success' => 'L\'accessoire a bien été mis-à-jour.',
    ],

    'delete' => [
        'confirm' => 'Etes-vous sûr de vouloir supprimer cet accessoire ?',
        'error' => 'Un problème est survenu durant la suppression de l\'accessoire. Merci d\'essayer à nouveau.',
        'success' => 'L\'accessoire a bien été supprimé.',
        'bulk_success' => 'Accessoire supprimée avec succès.|Les accessoires :count ont été supprimées avec succès.',
        'partial_success' => 'L\'accessoire :count a été supprimé avec succès, mais d\'autres n\'ont pas pu être supprimés. Voir ci-dessous pour plus de détails. :count accessoires ont été supprimés avec succès, mais d\'autres n\'ont pas pu être supprimés. Voir ci-dessous pour plus de détails.',
    ],

    'checkout' => [
        'error' => 'Cet accessoire n\'est pas attribué. Veuillez réessayer',
        'success' => 'Accessoire attribué correctement.',
        'unavailable' => 'L\'accessoire n\'est pas disponible à l\'affectation. Vérifiez la quantité disponible',
        'user_does_not_exist' => 'Cet utilisateur est inexistant. Veuillez réessayer.',
        'checkout_qty' => [
            'lte' => 'Il n\'y a actuellement qu\'un seul accessoire de ce type disponible, et vous essayez d\'en attribuer :checkout_qty. Veuillez ajuster la quantité attribuée ou le stock total de cet accessoire et réessayer.|Il n\'y a actuellement que :number_currently_remaining accessoire(s) disponible(s) de ce type, et vous essayez d\'en attribuer :checkout_qty. Veuillez ajuster la quantité attribuée ou le stock total de cet accessoire et réessayer.',
        ],

    ],

    'checkin' => [
        'error' => 'Cet accessoire n\'a pas été dissocié, veuillez réessayer',
        'success' => 'Accessoire dissocié correctement.',
        'user_does_not_exist' => 'Cet utilisateur est inexistant. Veuillez réessayer.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Aucun champ n\'a été changé, donc rien n\'a été mis à jour.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
