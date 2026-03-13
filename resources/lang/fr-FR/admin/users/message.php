<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Vos utilisateurs ont été mis à jour avec succès, mais votre entrée de gestionnaire n\'a pas été enregistrée, car le gestionnaire que vous avez sélectionné était également dans la liste d\'utilisateurs à éditer, et les utilisateurs peuvent ne pas être leur propre gestionnaire. Sélectionnez à nouveau vos utilisateurs, à l\'exclusion du gestionnaire.',
    'user_exists'               => 'L\'utilisateur existe déjà !',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'L\'utilisateur n\'existe pas ou vous n\'avez pas la permission de les voir.',
    'user_login_required'       => 'Le champ identifiant est obligatoire',
    'user_has_no_assets_assigned' => 'Aucun actif actuellement assigné à l\'utilisateur·trice.',
    'user_password_required'    => 'Le mot de passe est obligatoire.',
    'insufficient_permissions'  => 'Droits insuffisants.',
    'user_deleted_warning'      => 'Cet utilisateur a été supprimé. Vous devez le restaurer pour pouvoir l\'éditer ou lui assigner de nouveaux actifs.',
    'ldap_not_configured'        => 'L\'intégration LDAP n\'a pas été configuré pour cette installation .',
    'password_resets_sent'      => 'Les utilisateurs sélectionnés qui sont activés et ont une adresse e-mail valide ont reçu un lien de réinitialisation du mot de passe.',
    'password_reset_sent'       => 'Un lien de réinitialisation du mot de passe a été envoyé à :email!',
    'user_has_no_email'         => 'Cet utilisateur n\'a pas renseigné d\'adresse e-mail dans son profil.',
    'log_record_not_found'        => 'Impossible de trouver un enregistrement de log correspondant à cet utilisateur.',


    'success' => array(
        'create'    => 'L’utilisateur a été créé avec succès.',
        'update'    => 'L’utilisateur a été mis à jour avec succès.',
        'update_bulk'    => 'Utilisateurs mis à jour avec succès !',
        'delete'    => 'L’utilisateur a été supprimé avec succès.',
        'ban'       => 'L’utilisateur a été banni avec succès.',
        'unban'     => 'L’utilisateur a été réhabilité avec succès.',
        'suspend'   => 'L’utilisateur a été suspendu avec succès.',
        'unsuspend' => 'L’utilisateur a été activé avec succès.',
        'restored'  => 'L’utilisateur a été restauré avec succès.',
        'import'    => 'Les utilisateurs ont été importés correctement.',
    ),

    'error' => array(
        'create' => 'Un problème a eu lieu pendant la création de l\'utilisateur. Veuillez essayer à nouveau.',
        'update' => 'Un problème a eu lieu pendant la mise à jour de l\'utilisateur. Veuillez essayer à nouveau.',
        'delete' => 'Un problème a eu lieu pendant la suppression de l\'utilisateur. Veuillez essayer à nouveau.',
        'delete_has_assets' => 'Cet utilisateur a des éléments assignés et n\'a pas pu être supprimé.',
        'delete_has_assets_var' => 'Cet utilisateur a encore un actif attribué. Cet utilisateur a encore :count actifs assignés. Veuillez vérifier ses actifs en premier.',
        'delete_has_licenses_var' => 'Cet utilisateur a encore des sièges de licence attribués. Cet utilisateur a encore des sièges de licence :count attribués. Veuillez d\'abord les vérifier.',
        'delete_has_accessories_var' => 'Cet utilisateur a encore un accessoire attribué. Cet utilisateur a encore :count accessoires assignés. Veuillez vérifier leurs actifs en premier.',
        'delete_has_locations_var' => 'Cet utilisateur gère toujours un emplacement. Veuillez d\'abord sélectionner un autre gestionnaire.|Cet utilisateur gère toujours :count emplacements. Veuillez d\'abord sélectionner un autre gestionnaire.',
        'delete_has_users_var' => 'Cet utilisateur gère toujours un autre utilisateur. Veuillez d\'abord sélectionner un autre gestionnaire pour cet utilisateur. Cet utilisateur gère toujours :count utilisateurs. Veuillez d\'abord sélectionner un autre gestionnaire.',
        'unsuspend' => 'Un problème a eu lieu pendant la réhabilitation de l\'utilisateur. Veuillez essayer à nouveau.',
        'import'    => 'Il y a eu un problème lors de l\'importation des utilisateurs. Veuillez réessayer.',
        'asset_already_accepted' => 'Cet actif a déjà été accepté.',
        'accept_or_decline' => 'Vous devez accepter ou refuser cet actif.',
        'cannot_delete_yourself' => 'Nous nous sentirions vraiment mal si vous vous supprimiez vous-même, s\'il vous plaît reconsidérer.',
        'incorrect_user_accepted' => 'Le bien que vous avez tenté d\'accepter ne vous avait pas été attribué.',
        'ldap_could_not_connect' => 'Impossible de se connecter au serveur LDAP . S\'il vous plaît vérifier la configuration de votre serveur LDAP dans le fichier de configuration LDAP . <br> Erreur du serveur LDAP :',
        'ldap_could_not_bind' => 'Impossible de se connecter au serveur LDAP . S\'il vous plaît vérifier la configuration de votre serveur LDAP dans le fichier de configuration LDAP . <br> Erreur de serveur LDAP : ',
        'ldap_could_not_search' => 'Impossible de rechercher le serveur LDAP . S\'il vous plaît vérifier la configuration de votre serveur LDAP dans le fichier de configuration LDAP . <br> Erreur de serveur LDAP :',
        'ldap_could_not_get_entries' => 'Impossible d\'obtenir les entrées du serveur LDAP . S\'il vous plaît vérifier la configuration de votre serveur LDAP dans le fichier de configuration LDAP . <br> Erreur de serveur LDAP :',
        'password_ldap' => 'Le mot de passe de ce compte est géré par LDAP / Active Directory. Veuillez contacter votre service informatique pour changer votre mot de passe.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Le fichier n\'a pas pu être supprimé. Veuillez réessayer.',
        'success' => 'Le fichier a été supprimé correctement.',
    ),

    'upload' => array(
        'error'   => 'Le(s) fichier(s) n\'ont pas pu être téléversé. Veuillez réessayer.',
        'success' => 'Le(s) fichier(s) ont été téléversé correctement.',
        'nofiles' => 'Vous n\'avez pas sélectionné de fichier pour le téléversement',
        'invalidfiles' => 'Un ou plusieurs de vos fichiers sont trop gros, ou sont d\'un type non autorisé. Les types de fichiers autorisés sont png, gif, jpg, doc, docx, pdf et txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Cet utilisateur n\'a pas d\'e-mail défini.',
        'success' => 'L\'utilisateur a été informé de son inventaire actuel.'
    )
);