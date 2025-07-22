<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Ce bien n\'existe pas.',
    'does_not_exist_var' => 'Actif avec le tag :asset_tag introuvable.',
    'no_tag' 	         => 'Aucune étiquette d\'actif fournie.',
    'does_not_exist_or_not_requestable' => 'Cet actif n\'existe pas ou ne peut pas être demandé.',
    'assoc_users'	 	 => 'Ce bien est marqué sorti par un utilisateur et ne peut être supprimé. Veuillez d\'abord cliquer sur Retour de Biens, et réessayer.',
    'warning_audit_date_mismatch' 	=> 'La prochaine date d\'audit de cet actif (:next_audit_date) est antérieure à la dernière date d\'audit (:last_audit_date). Veuillez mettre à jour la prochaine date d\'audit.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Ce bien n\'a pas été créé, veuillez réessayer. :(',
        'success' 		=> 'Bien créé correctement. :)',
        'success_linked' => 'L\'actif avec le tag :tag a été créé avec succès. <strong><a href=":link" style="color: white;">Cliquez ici pour voir</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Ce bien n\'a pas été actualisé, veuillez réessayer',
        'success' 			=> 'Bien actualisé correctement.',
        'encrypted_warning' => 'Ressource mise à jour avec succès, mais les champs personnalisés chiffrés ne sont pas dus aux permissions',
        'nothing_updated'	=>  'Aucun champ n\'a été sélectionné, rien n\'a été actualisé.',
        'no_assets_selected'  =>  'Aucune ressource n\'a été sélectionnée, rien n\'a donc été mis à jour.',
        'assets_do_not_exist_or_are_invalid' => 'Les ressources sélectionnées ne peuvent pas être mises à jour.',
    ],

    'restore' => [
        'error'   		=> 'L\'actif n\'a pas été restauré, veuillez réessayer',
        'success' 		=> 'Actif restauré correctement.',
        'bulk_success' 		=> 'Actif restauré avec succès.',
        'nothing_updated'   => 'Aucun actif n\'a été sélectionné, donc rien n\'a été restauré.', 
    ],

    'audit' => [
        'error'   		=> 'Échec de l\'audit d\'actif : :error ',
        'success' 		=> 'Audit des actifs consigné avec succès.',
    ],


    'deletefile' => [
        'error'   => 'Le fichier n\'a pas été détruit. Veuillez réessayer.',
        'success' => 'Fichier détruit correctement.',
    ],

    'upload' => [
        'error'   => 'Le(s) fichier(s) n\'ont pas pu être téléversé. Veuillez réessayer.',
        'success' => 'Le(s) fichier(s) ont été téléversé correctement.',
        'nofiles' => 'Vous n\'avez pas sélectionné de fichier pour le téléchargement ou le fichier que vous essayez de télécharger est trop gros',
        'invalidfiles' => 'Un ou plusieurs de vos fichiers sont trop gros, ou sont d\'un type non autorisé. Les types de fichiers autorisés sont png, gif, jpg, doc, docx, pdf et txt.',
    ],

    'import' => [
        'import_button'         => 'Effectuer l\'importation',
        'error'                 => 'Certains éléments n\'ont pas été correctement importés.',
        'errorDetail'           => 'Les éléments suivants n\'ont pas été importés à cause d\'erreurs.',
        'success'               => 'Votre fichier a bien été importé',
        'file_delete_success'   => 'Votre fichier a été correctement supprimé',
        'file_delete_error'      => 'Le fichier n’a pas pu être supprimé',
        'file_missing' => 'Le fichier sélectionné est manquant',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Un ou plusieurs attributs dans la ligne d\'en-tête contiennent des caractères UTF-8 invalides',
        'content_row_has_malformed_characters' => 'Un ou plusieurs attributs dans la première ligne de contenu contiennent des caractères UTF-8 invalides',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Etes-vous sûr de vouloir supprimer ce bien?',
        'error'   		=> 'Il y a eu un problème en supprimant ce bien. Veuillez réessayer.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Aucun actif n\'a été sélectionné, donc rien n\'a été supprimé.',
        'success' 		=> 'Ce bien a été supprimé correctement.',
    ],

    'checkout' => [
        'error'   		=> 'Ce bien n\'a pas été sorti, veuillez réessayer',
        'success' 		=> 'Ce bien a été sorti correctement.',
        'user_does_not_exist' => 'Cet utilisateur est invalide. Veuillez réessayer.',
        'not_available' => 'Ce bien n\'est pas disponible pour être sorti!',
        'no_assets_selected' => 'Vous devez sélectionner au moins un élément de la liste',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Ce bien n\'a pas été retourné, veuillez réessayer',
        'success' 		=> 'Ce bien a été retourné correctement.',
        'user_does_not_exist' => 'Cet utilisateur est invalide. Veuillez réessayer.',
        'already_checked_in'  => 'Ce bien est déjà dissocié.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Annuler cette demande',
    ],

];
