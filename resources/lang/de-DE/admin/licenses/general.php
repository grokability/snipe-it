<?php

return array(
    'about_licenses_title'      => 'Über Lizenzen',
    'about_licenses'            => 'Lizenzen werden verwendet, um Software zu verfolgen.  Sie haben eine bestimmte Anzahl von Plätzen, die an Einzelpersonen ausgegeben werden können',
    'checkin'  					=> 'Lizenz einbuchen',
    'checkout_history'  		=> 'Zuweisungsverlauf',
    'checkout'  				=> 'Lizenzaktivierung herausgeben',
    'edit'  					=> 'Lizenz bearbeiten',
    'filetype_info'				=> 'Erlaubte Dateitypen sind png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, und rar.',
    'clone'  					=> 'Lizenz duplizieren',
    'history_for'  				=> 'Verlauf für ',
    'in_out'  					=> 'Zurücknehmen/Herausgeben',
    'info'  					=> 'Lizenzinfo',
    'license_seats'  			=> 'Lizenzaktivierungen',
    'seat'  					=> 'Lizenz',
    'seat_count'  				=> 'Platz :count',
    'seats'  					=> 'Verfügbare Lizenzen',
    'software_licenses'  		=> 'Software Lizenzen',
    'user'  					=> 'Nutzer',
    'view'  					=> 'Lizenz ansehen',
    'delete_disabled'           => 'Diese Lizenz kann noch nicht gelöscht werden, da einige Plätze noch ausgecheckt sind.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Alle Plätze zurücknehmen',
                'modal'             => 'Diese Aktion wird einen Platz zurücknehmen. | Diese Aktion wird alle :checkedout_seats_count Sitze für diese Lizenz zurücknehmen.',
                'enabled_tooltip'   => 'ALLE Plätze für diese Lizenz zurücknehmen, sowohl von Benutzern als auch von Assets',
                'disabled_tooltip'  => 'Es ist deaktiviert, da derzeit keine Plätze zum herausgegeben verfügbar sind',
                'disabled_tooltip_reassignable'  => 'Dies ist deaktiviert, da die Lizenz nicht zuweisbar ist',
                'success'           => 'Lizenz erfolgreich zurückgenommen! | Alle Lizenzen wurden erfolgreich zurückgenommen!',
                'log_msg'           => 'Zurückgenommen über Massen-Rücknahme in der Lizenzübersicht',
            ],

            'checkout_all'              => [
                'button'                => 'Alle Plätze herausgeben',
                'modal'                 => 'Diese Aktion wird einen Platz für den ersten verfügbaren Benutzer herausgeben. | Diese Aktion wird alle :available_seats_count Plätze an die ersten verfügbaren Benutzer herausgeben. Ein Benutzer wird als verfügbar für diesen Platz betrachtet, wenn er diese Lizenz noch nicht zugewiesen hat und die "Lizenzen automatisch zuweisen" Eigenschaft auf dem Benutzerkonto aktiviert ist.',
                'enabled_tooltip'   => 'ALLE Plätze (oder so viele wie verfügbar sind) an ALLE Benutzer herausgeben',
                'disabled_tooltip'  => 'Dies ist deaktiviert, da derzeit keine Sitze verfügbar sind',
                'success'           => 'Lizenz erfolgreich herausgegeben! | Alle :count Lizenzen wurden erfolgreich herausgegeben!',
                'error_no_seats'    => 'Es gibt keine verbleibenden Plätze für diese Lizenz.',
                'warn_not_enough_seats'    => ':count Benutzern wurde diese Lizenz zugewiesen, aber es gibt keine verfügbaren Lizenzplätze mehr.',
                'warn_no_avail_users'    => 'Nichts zu tun. Es gibt keine Benutzer, denen diese Lizenz noch nicht zugewiesen ist.',
                'log_msg'           => 'Herausgegeben über Massen-Herausgeben in Lizenzübersicht',


            ],
    ],

    'below_threshold' => 'Für diese Lizenz gibt es nur noch :remaining_count Sitze mit einer Mindestanzahl von :min_amt. Sie können erwägen, mehr Sitze zu kaufen.',
    'below_threshold_short' => 'Dieser Artikel liegt unter der Mindestmenge der benötigten Menge.',
);
