<?php

return array(
    'about_licenses_title'      => 'A propos des licences',
    'about_licenses'            => 'Les licences sont utilisées pour suivre les logiciels. Ils ont un certain nombre d\'attribution pouvant être associés individuellement',
    'checkin'  					=> 'Libérer la licence multiposte',
    'checkout_history'  		=> 'Historique des associations',
    'checkout'  				=> 'Associer la licence multiposte',
    'edit'  					=> 'Éditer la licence',
    'filetype_info'				=> 'Types de fichier autorisés: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, and rar.',
    'clone'  					=> 'Cloner la licence',
    'history_for'  				=> 'Historique pour ',
    'in_out'  					=> 'Associer/Libérer',
    'info'  					=> 'Informations de licence',
    'license_seats'  			=> 'Licence multipostes',
    'seat'  					=> 'Poste',
    'seat_count'  				=> 'Poste :count',
    'seats'  					=> 'Postes',
    'software_licenses'  		=> 'Licences de logiciel',
    'user'  					=> 'Utilisateur',
    'view'  					=> 'Voir la licence',
    'delete_disabled'           => 'Cette licence ne peut pas encore être supprimée car certains sièges sont encore attribués.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Désattribuer tous les sièges',
                'modal'             => 'Cette action désattribuera un poste. | Cette action désattribuera les :checkedout_seats_count postes pour cette licence.',
                'enabled_tooltip'   => 'Désassocier TOUS les sièges de cette licence, à la fois des utilisateurs·trices et des actifs',
                'disabled_tooltip'  => 'Ceci est désactivé car il n\'y a pas de siège actuellement associé',
                'disabled_tooltip_reassignable'  => 'Ceci est désactivé car la licence n\'est pas réassignable',
                'success'           => 'Licence désassociée avec succès ! | Toutes les licences ont été désassociées avec succès !',
                'log_msg'           => 'Associée via l\'interface d\'attribution de licence en volumes',
            ],

            'checkout_all'              => [
                'button'                => 'Associer tous les sièges',
                'modal'                 => 'Cette action associera un siège au premier utilisateur disponible. | Cette action associera :available_seats_count sièges aux premiers utilisateurs disponibles. Un·e utilisateur·trice est considéré·e disponible pour un siège si iel n\'a pas déjà cette licence associée à son profil, et que l\'auto-association de licence est active sur son compte.',
                'enabled_tooltip'   => 'Associer TOUS les sièges (ou autant que disponible) à TOUS les utilisateurs·trices',
                'disabled_tooltip'  => 'Ceci est désactivé car il n\'y a pas de siège actuellement disponible',
                'success'           => 'Licence associée avec succès ! | :count licences ont été associées avec succès !',
                'error_no_seats'    => 'Il n\'y a plus de siège disponible pour cette licence.',
                'warn_not_enough_seats'    => ':count utilisateurs·trices ont été assigné·es à cette licence, mais nous avons manqué de sièges disponibles.',
                'warn_no_avail_users'    => 'Rien à faire. Il n\'y a pas d\'utilisateur·trice qui n\'ont pas encore cette licence attribuée.',
                'log_msg'           => 'Attribué via l\'outil d\'attribution de licences en volume',


            ],
    ],

    'below_threshold' => 'Il ne reste que :remaining_count places pour cette licence avec une quantité minimale de :min_amt. Vous pouvez envisager d\'acheter plus de sièges.',
    'below_threshold_short' => 'Cet article est en dessous de la quantité minimale requise.',
);
