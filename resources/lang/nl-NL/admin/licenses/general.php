<?php

return array(
    'about_licenses_title'      => 'Over licenties',
    'about_licenses'            => 'Licenties worden gebruikt om software te beheren. Deze hebben een maximum aantal wat aan gebruikers uitgeleverd kan worden',
    'checkin'  					=> 'Check werkplek licentie in',
    'checkout_history'  		=> 'Checkout historie',
    'checkout'  				=> 'Check werkplek licentie uit',
    'edit'  					=> 'Wijzig licentie',
    'filetype_info'				=> 'Toegestane bestandstypes zijn png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, and rar.',
    'clone'  					=> 'Dupliceer licentie',
    'history_for'  				=> 'Geschiedenis van ',
    'in_out'  					=> 'In/Uit',
    'info'  					=> 'Licentiegegevens',
    'license_seats'  			=> 'Licentie werkplekken',
    'seat'  					=> 'Werkplek',
    'seat_count'  				=> 'Werkplek :count',
    'seats'  					=> 'Werkplekken',
    'software_licenses'  		=> 'Applicatie Licenties',
    'user'  					=> 'Gebruiker',
    'view'  					=> 'Bekijk licentie',
    'delete_disabled'           => 'Deze licentie kan nog niet worden verwijderd omdat deze nog is uitgecheckt.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Alle licenties inchecken',
                'modal'             => 'Hiermee wordt één werkplek ingecheckt. | Hiermee worden alle :checkedout_seats_count werkplekken voor deze licentie ingecheckt.',
                'enabled_tooltip'   => 'Check ALLE werkplekken in voor deze licentie van zowel gebruikers als assets',
                'disabled_tooltip'  => 'Dit is uitgeschakeld omdat er nog niets is uitgecheckt',
                'disabled_tooltip_reassignable'  => 'Dit is uitgeschakeld omdat de licentie niet opnieuw toegewezen kan worden',
                'success'           => 'Licentie met succes ingecheckt! | Alle licenties zijn met succes ingecheckt!',
                'log_msg'           => 'Ingecheckt via bulk licentiecontrole in licentie GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Alle licenties uitchecken',
                'modal'                 => 'Met deze actie wordt één plaats uit gecheckt naar de eerste beschikbare gebruiker. | deze actie zal alle :available_seats_count plaatsen uit checken aan de eerste beschikbare gebruikers. Een gebruiker wordt beschouwd als beschikbaar voor deze plaats als hij deze licentie nog niet heeft ontvangen, en de Licenties automatisch toewijzen eigenschap is ingeschakeld op hun gebruikersaccount.',
                'enabled_tooltip'   => 'Check uit ALLE plaatsen (of zoveel als beschikbaar) voor ALLE gebruikers',
                'disabled_tooltip'  => 'Dit is uitgeschakeld omdat er momenteel geen plaatsen beschikbaar zijn',
                'success'           => 'Licentie met succes uitgecheckt! | :count licenties zijn met succes uitgecheckt!',
                'error_no_seats'    => 'Er zijn geen resterende plekken meer voor deze licentie.',
                'warn_not_enough_seats'    => ':count gebruikers hebben deze licentie gekregen, maar er zijn niet genoeg licentie plekken.',
                'warn_no_avail_users'    => 'Niets te doen. Er zijn geen gebruikers die deze licentie nog niet aan hen hebben toegewezen.',
                'log_msg'           => 'Uitgecheckt via bulklicentie-checkout in licentie GUI',


            ],
    ],

    'below_threshold' => 'Er zijn nog :remaining_count zitplaatsen over voor deze licentie met een minimale hoeveelheid van :min_amt. Misschien wilt u overwegen om meer zitplaatsen te kopen.',
    'below_threshold_short' => 'Dit artikel ligt onder de minimum vereiste hoeveelheid.',
);
