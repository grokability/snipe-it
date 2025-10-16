<?php

return array(

    'does_not_exist' => 'La licence n\'existe pas ou vous n\'avez pas la permission d\'y accéder.',
    'user_does_not_exist' => 'L\'utilisateur n\'existe pas ou vous n\'avez pas la permission de les voir.',
    'asset_does_not_exist' 	=> 'L\'actif que vous essayez d\'associer avec cette licence n\'existe pas.',
    'owner_doesnt_match_asset' => 'L\'actif que vous essayez d\'associer avec cette licence est détenu par une autre personne que celle sélectionnée dans la liste déroulante.',
    'assoc_users'	 => 'Cette catégorie est associée au moins à un modèle et ne peut être supprimée. Veuillez actualiser vos modèles pour ne plus référencer cette catégorie et réessayer.',
    'select_asset_or_person' => 'Vous devez sélectionner un actif ou un utilisateur, mais pas les deux.',
    'not_found' => 'Licence introuvable',
    'seats_available' => ':seat_count sièges disponibles',


    'create' => array(
        'error'   => 'Cette catégorie n\'a pas été créée, veuillez réessayer.',
        'success' => 'Catégorie créée correctement.'
    ),

    'deletefile' => array(
        'error'   => 'Le fichier n\'a pas pu être supprimé. Merci de réessayer.',
        'success' => 'Le fichier a bien été supprimé.',
    ),

    'upload' => array(
        'error'   => 'Le(s) fichier(s) n\'a pas pu être uploadé. Merci de réessayer.',
        'success' => 'Le(s) fichier(s) a bien été uploadé.',
        'nofiles' => 'Vous n\'avez pas sélectionné de fichier pour le téléchargement ou le fichier que vous essayez de télécharger est trop gros',
        'invalidfiles' => 'Un ou plusieurs de vos fichiers est trop grand ou le type de fichier n\'est pas autorisé. Les différents types de fichiers autorisés sont png, gif, jpg, doc, docx, pdf, txt, zip, rar et rtf.',
    ),

    'update' => array(
        'error'   => 'Cette catégorie n\'a pas été actualisée, veuillez réessayer.',
        'success' => 'Catégorie actualisée correctement.'
    ),

    'delete' => array(
        'confirm'   => 'Etes-vous sûr de vouloir supprimer cette catégorie?',
        'error'   => 'Il y a eu un problème en supprimant cette catégorie. Veuillez réessayer.',
        'success' => 'Cette catégorie a été supprimée correctement.'
    ),

    'checkout' => array(
        'error'   => 'Un problème a eu lieu pendant l\'association de la licence. Veuillez essayer à nouveau.',
        'success' => 'La licence a été associée avec succès',
        'not_enough_seats' => 'Pas assez de sièges de licence disponibles pour le paiement',
        'mismatch' => 'Le poste de licence fourni ne correspond pas à la licence',
        'unavailable' => 'Ce poste n\'est pas disponible pour attribution.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Un problème a eu lieu pendant la dissociation de la licence. Veuillez essayer à nouveau.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'La licence a été dissociée avec succès'
    ),

);
