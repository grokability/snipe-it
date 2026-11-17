<?php

return [

    'undeployable' => 'Les ressources suivantes ne peuvent pas être déployées et ont été retirées de la caisse : :asset_tags',
    'does_not_exist' => 'Ce bien n\'existe pas.',
    'does_not_exist_var' => 'Actif avec le tag :asset_tag introuvable.',
    'no_tag' => 'Aucune étiquette d\'actif fournie.',
    'does_not_exist_or_not_requestable' => 'Cet actif n\'existe pas ou ne peut pas être demandé.',
    'assoc_users' => 'Ce bien est marqué sorti par un utilisateur et ne peut être supprimé. Veuillez d\'abord cliquer sur Retour de Biens, et réessayer.',
    'warning_audit_date_mismatch' => 'La prochaine date d\'audit de cet actif (:next_audit_date) est antérieure à la dernière date d\'audit (:last_audit_date). Veuillez mettre à jour la prochaine date d\'audit.',
    'labels_generated' => 'Les étiquettes ont été générées avec succès.',
    'error_generating_labels' => 'Erreur lors de la génération des étiquettes.',
    'no_assets_selected' => 'Aucune ressource sélectionnée.',

    'create' => [
        'error' => 'Ce bien n\'a pas été créé, veuillez réessayer. :(',
        'success' => 'Bien créé correctement. :)',
        'success_no_checkout' => 'L\'actif a été créé avec succès, mais n\'a pas été verrouillé car vous n\'avez pas la permission d\'extraire les ressources.',
        'checkout_skipped_no_permission' => 'La ressource a été créée, mais n\'a pas été verrouillée vers la cible demandée parce que vous n\'avez pas la permission de extraire les ressources.',
        'success_linked' => 'L\'actif avec le tag :tag a été créé avec succès. <strong><a href=":link" style="color: white;">Cliquez ici pour voir</a></strong>.',
        'multi_success_linked' => 'L\'actif avec le tag :links a été créé avec succès.|:count actifs ont été créés avec succès. :links.',
        'partial_failure' => 'Impossible de créer une ressource. Raison: :failures|:count actifs n\'ont pas pu être créés. Raisons: :failures',
        'target_not_found' => [
            'user' => 'L\'utilisateur assigné est introuvable.',
            'asset' => 'La ressource assignée est introuvable.',
            'location' => 'L\'emplacement assigné est introuvable.',
        ],
    ],

    'update' => [
        'error' => 'Ce bien n\'a pas été actualisé, veuillez réessayer',
        'success' => 'Bien actualisé correctement.',
        'encrypted_warning' => 'Ressource mise à jour avec succès, mais les champs personnalisés chiffrés ne sont pas dus aux permissions',
        'nothing_updated' => 'Aucun champ n\'a été sélectionné, rien n\'a été actualisé.',
        'no_assets_selected' => 'Aucune ressource n\'a été sélectionnée, rien n\'a donc été mis à jour.',
        'assets_do_not_exist_or_are_invalid' => 'Les ressources sélectionnées ne peuvent pas être mises à jour.',
    ],

    'bulk_update' => [
        'success' => 'L\'actif mis à jour avec succès.|:count actifs ont été mis à jour avec succès.',
        'partial' => ':success actif(s) mis à jour avec succès, :failed échoué. Voir le tableau des résultats pour plus de détails.',
        'error' => 'Aucune ressource n\'a été mise à jour. Voir le tableau de résultats pour plus de détails.',
    ],

    'restore' => [
        'error' => 'L\'actif n\'a pas été restauré, veuillez réessayer',
        'success' => 'Actif restauré correctement.',
        'bulk_success' => 'Actif restauré avec succès.',
        'nothing_updated' => 'Aucun actif n\'a été sélectionné, donc rien n\'a été restauré.',
    ],

    'audit' => [
        'error' => 'Échec de l\'audit d\'actif : :error ',
        'success' => 'Audit des actifs consigné avec succès.',
    ],

    'deletefile' => [
        'error' => 'Le fichier n\'a pas été détruit. Veuillez réessayer.',
        'success' => 'Fichier détruit correctement.',
    ],

    'upload' => [
        'error' => 'Le(s) fichier(s) n\'ont pas pu être téléversé. Veuillez réessayer.',
        'success' => 'Le(s) fichier(s) ont été téléversé correctement.',
        'nofiles' => 'Vous n\'avez pas sélectionné de fichier pour le téléchargement ou le fichier que vous essayez de télécharger est trop gros',
        'invalidfiles' => 'Un ou plusieurs de vos fichiers sont trop gros, ou sont d\'un type non autorisé. Les types de fichiers autorisés sont png, gif, jpg, doc, docx, pdf et txt.',
    ],

    'import' => [
        'import_button' => 'Effectuer l\'importation',
        'error' => 'Certains éléments n\'ont pas été correctement importés.',
        'errorDetail' => 'Les éléments suivants n\'ont pas été importés à cause d\'erreurs.',
        'success' => 'Votre fichier a bien été importé',
        'file_delete_success' => 'Votre fichier a été correctement supprimé',
        'file_delete_error' => 'Le fichier n’a pas pu être supprimé',
        'file_missing' => 'Le fichier sélectionné est manquant',
        'file_already_deleted' => 'Le fichier sélectionné a déjà été supprimé',
        'file_missing_on_disk' => 'Le fichier pour cette importation n\'est plus sur le disque. Il a peut-être été supprimé en dehors de Snipe-IT. Supprimez cette entrée et téléchargez à nouveau le fichier pour réessayer.',
        'file_empty' => 'Ce fichier n\'a pas de ligne de données. Rien ne peut être importé.',
        'already_processing' => 'Cette importation est en cours de traitement par un autre utilisateur. Veuillez attendre qu\'elle se termine avant de réessayer.',
        'header_row_missing' => 'Ce fichier n\'a pas de ligne d\'en-tête reconnue. Supprimez cette entrée et téléchargez à nouveau le fichier pour réessayer.',
        'header_row_has_malformed_characters' => 'Un ou plusieurs attributs dans la ligne d\'en-tête contiennent des caractères UTF-8 invalides',
        'content_row_has_malformed_characters' => 'Un ou plusieurs attributs dans la première ligne de contenu contiennent des caractères UTF-8 invalides',
        'transliterate_failure' => 'La translittération de :encoding à UTF-8 a échoué en raison de caractères invalides en entrée',
        'bulk_delete' => [
            'button' => 'Supprimer la sélection (:count)',
            'confirm_title' => 'Supprimer les fichiers d\'importation sélectionnés ?',
            'confirm_body' => 'Vous êtes sur le point de supprimer :count fichier(s) importé(s) de manière permanente. Cette action est irréversible.',
            'confirm_button' => 'Supprimer',
            'success' => 'Le fichier d\'importation a été supprimé avec succès.|:count fichiers d\'importation ont été supprimés avec succès.',
            'skipped' => ':count fichier(s) ont été ignorés car vous n\'avez pas la permission de les supprimer.',
            'select_all' => 'Sélectionner tous les fichiers de cette page',
            'select_row' => 'Sélectionner :file pour la suppression en masse',
        ],
        'row_count' => '{0} Aucune ligne de données dans ce fichier |{1} :count ligne de données à importer|[2,*] :count lignes de données à importer',
        'summary' => [
            'created' => 'Créé',
            'updated' => 'Mis à jour',
            'skipped' => 'Ignoré en tant que doublons',
            'errored' => 'Erreur(e)',
            'no_changes' => 'L\'importation s\'est terminée mais rien n\'a été créé ou mis à jour. Chaque ligne a été ignorée, généralement parce que les enregistrements sous-jacents existaient déjà. Vérifiez les compteurs ci-dessous et ajustez le type CSV ou d\'importation si ce n\'est pas ce que vous attendez.',
        ],
        'type_required' => 'Veuillez sélectionner un type d\'importation avant de continuer.',
        'processing' => 'Traitement de votre importation. Veuillez patienter jusqu\'à ce que cela se termine avant de fermer la page.',
        'backup_running' => 'Exécution de la sauvegarde avant d\'importer. Cela peut prendre un certain temps sur des fichiers plus volumineux. Veuillez patienter.',
        'backup_label' => 'Pré-importer la sauvegarde',
        'backup_complete' => 'Sauvegarde terminée',
        'import_label' => 'Importer',
        'required_fields_missing' => 'Les champs obligatoires suivants ne sont pas mappés : :fields',
        'history' => [
            'missing_asset_tag_identity' => '(balise d\'actif manquante)',
            'missing_asset_tag_message' => 'Ligne ignorée : aucune balise de ressource fournie.',
            'asset_not_found_message' => 'L\'actif avec ce tag n\'existe pas. Importez d\'abord les actifs, puis ré-exécutez l\'importation de l\'historique.',
            'target_not_matched_message' => 'Aucun :target_type ne correspond à ":name". Pour les utilisateurs, activez/désactivez les options de correspondance à l\'étape 1 ou créez d\'abord l\'utilisateur. Pour les lieux, assurez-vous que le nom de l\'emplacement CSV correspond exactement à un emplacement existant.',
            'invalid_target_type_message' => 'Le type de cible ":value" n\'est pas reconnu. Utilisez "Utilisateur" ou "localisation", ou laissez la colonne vide par défaut pour l\'utilisateur.',
        ],
        'wizard' => [
            'step_type' => 'Choisir le type',
            'step_map' => 'Champs de carte',
            'step_preview' => 'Aperçu ',
            'back' => 'Retour',
            'next' => 'Prochain',
            'preview_button' => 'Aperçu ',
            'process' => 'Importation du processus',
            'preview_intro' => 'Aperçu de la première :count ligne(s) après avoir appliqué votre mappage. Utilisez le bouton Retour si vous avez besoin de modifier les attributs mappés avant d\'importer.',
        ],
    ],

    'delete' => [
        'confirm' => 'Etes-vous sûr de vouloir supprimer ce bien?',
        'error' => 'Il y a eu un problème en supprimant ce bien. Veuillez réessayer.',
        'assigned_to_error' => '{1}Étiquette d\'actif : :asset_tag est actuellement verrouillé. Vérifiez sur cet appareil avant de supprimer. [2,*]Étiquettes de ressources: :asset_tag sont actuellement verrouillés. Vérifiez dans ces appareils avant de supprimer.',
        'nothing_updated' => 'Aucun actif n\'a été sélectionné, donc rien n\'a été supprimé.',
        'success' => 'Ce bien a été supprimé correctement.',
    ],

    'checkout' => [
        'error' => 'Ce bien n\'a pas été sorti, veuillez réessayer',
        'success' => 'Ce bien a été sorti correctement.',
        'user_does_not_exist' => 'Cet utilisateur est invalide. Veuillez réessayer.',
        'not_available' => 'Ce bien n\'est pas disponible pour être sorti!',
        'no_assets_selected' => 'Vous devez sélectionner au moins un élément de la liste',
    ],

    'multi-checkout' => [
        'error' => 'L\'actif n\'a pas été déverrouillé, veuillez réessayer|Les actifs n\'ont pas été déverrouillés, veuillez réessayer',
        'success' => 'Actif déverrouillé avec succès.|Actifs déverrouillés avec succès.',
    ],

    'multi-checkin' => [
        'error' => 'L\'actif n\'a pas été enregistré, veuillez réessayer|Les actifs n\'ont pas été enregistrés, veuillez réessayer',
        'success' => 'Actif vérifié avec succès.|Actifs vérifiés avec succès.',
        'no_assets_selected' => 'Vous devez sélectionner au moins un élément de la liste',
    ],

    'multi-audit' => [
        'success' => ':l\'actif mis à jour avec succès.|:count actifs ont été mis à jour avec succès.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Vous devez sélectionner au moins un élément de la liste',
    ],

    'checkin' => [
        'error' => 'Ce bien n\'a pas été retourné, veuillez réessayer',
        'success' => 'Ce bien a été retourné correctement.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Cet utilisateur est invalide. Veuillez réessayer.',
        'already_checked_in' => 'Ce bien est déjà dissocié.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'L\'élément n\'est pas dans un état d\'affectation invalide.',
        'force_checkin_error' => 'Impossible d\'effacer l\'affectation non valide.',

    ],

    'requests' => [
        'error' => 'La requête a échoué, veuillez réessayer.',
        'success' => 'Demande soumise avec succès.',
        'canceled' => 'Demande annulée avec succès.',
        'cancel' => 'Annuler cette demande',
        'duplicate' => 'You already have an active request for this item.',
        'no_active' => 'You have no active request to cancel for this item.',
        'insufficient_stock' => 'Not enough on hand to fulfill this request. Replenish first.',
        'confirm_cancel_by_admin' => "Annuler la demande de :user pour :item?",
        'no_selection' => 'Aucune ligne n\'a été sélectionnée pour être remplie.',
        'row_stale' => 'La requête #:id n\'est plus en attente et a été ignorée.',
        'row_qty_invalid' => 'Request #:id has an invalid quantity and was skipped.',
        'row_user_missing' => 'Request #:id has no valid target user and was skipped.',
        'row_asset_missing' => 'Request #:id has no valid target asset and was skipped.',
        'row_asset_not_requesters' => 'Request #:id skipped: selected asset does not belong to the requester.',
        'row_asset_not_available' => 'Request #:id skipped: selected asset is not an available unit of this model.',
        'row_asset_taken' => 'Request #:id skipped: selected asset was assigned to someone else before submit.',
        'row_company_mismatch' => 'La requête #:id a été ignorée: :user ne peut pas recevoir d\'articles de cette société.',
        'row_over_allocated' => 'La requête #:id a été ignorée: pas assez en main au moment de soumettre.',
        'no_target_assets_for_user' => ':user n\'a pas de ressources à installer.',
        'no_available_units' => 'Aucune unité disponible de ce modèle à distribuer.',
        'bulk_summary' => 'Fulfilled :fulfilled of :total requests.',
        'bulk_fulfill_notification_intro' => 'Ce qui suit s\'appliquera à chaque requête cochée une fois qu\'elle sera remplie :',
    ],

];
