<?php

return [

    'invalid_category_type' => 'La catégorie doit être une catégorie de consommables.',
    'does_not_exist' => 'Ce consommable n\'existe pas.',

    'create' => [
        'error' => 'Le consommable n\'a pas été créé, merci de réessayer.',
        'success' => 'Le consommable a été créé correctement.',
    ],

    'update' => [
        'error' => 'Le consommable n\'a pas été mis-à-jour, merci de réessayer',
        'success' => 'Le consommable a été mis-à-jour.',
    ],

    'delete' => [
        'confirm' => 'Êtes-vous certain·e de vouloir supprimer ce consommable ?',
        'error' => 'Il y a eu un problème lors de la suppression du consommable. Merci de réessayer.',
        'success' => 'Le consommable a été supprimé correctement.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Le consommable n\'a pas été attribué correctement, merci de réessayer',
        'success' => 'Le consommable a été attribué correctement.',
        'user_does_not_exist' => 'Cet·te utilisateur·trice est invalide. Merci de réessayer.',
        'unavailable' => 'Il n\'y a pas assez de consommables pour cette attribution. Veuillez vérifier la quantité restante. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Le consommable n\'a pas été dissocié correctement, merci de réessayer',
        'success' => 'Le consommable a été dissocié correctement.',
        'user_does_not_exist' => 'Cet·te utilisateur·trice est invalide. Merci de réessayer.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Aucun champ n\'a été changé, donc rien n\'a été mis à jour.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
