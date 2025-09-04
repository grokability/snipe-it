<?php

return array(
    'about_licenses_title'      => 'Informazioni sulle licenze',
    'about_licenses'            => 'Le licenze vengono utilizzate per monitorare il software. Hanno un numero specifico di posti che possono essere verificati agli individui',
    'checkin'  					=> 'Restituire slot Licenza',
    'checkout_history'  		=> 'Storico Assegnazioni',
    'checkout'  				=> 'Assegna slot Licenza',
    'edit'  					=> 'Modifica Licenza',
    'filetype_info'				=> 'I formati di file permessi sono png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, e rar.',
    'clone'  					=> 'Clona Licenza',
    'history_for'  				=> 'Storico per ',
    'in_out'  					=> 'Dentro/Fuori',
    'info'  					=> 'Informazioni Licenza',
    'license_seats'  			=> 'Licenza Sede',
    'seat'  					=> 'Sede',
    'seat_count'  				=> 'Slot :count',
    'seats'  					=> 'Sedi',
    'software_licenses'  		=> 'Licenze Software',
    'user'  					=> 'Utente',
    'view'  					=> 'Mostra Licenza',
    'delete_disabled'           => 'Questa licenza non può essere cancellata perché ci sono delle postazioni assegnate.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Restituzione di tutti gli slot Licenza',
                'modal'             => 'Questa azione restituirà uno slot. | Questa azione restituirà tutti i :checkedout_seats_count slot di questa licenza.',
                'enabled_tooltip'   => 'Restituzione di TUTTI gli slot di questa licenza, sia di utenti che di beni',
                'disabled_tooltip'  => 'Disattivato perché non ci sono slot Licenza assegnati',
                'disabled_tooltip_reassignable'  => 'Disattivato a causa della licenza non reassegnabile',
                'success'           => 'Restituzione della licenza effettuata! | Restituzione di tutte le licenze effettuata!',
                'log_msg'           => 'Restituzione effettuata tramite la restituzione massiva nell\'interfaccia delle Licenze',
            ],

            'checkout_all'              => [
                'button'                => 'Assegna tutti gli slot',
                'modal'                 => 'Questa azione assegnerà uno slot al primo utente disponibile. | Questa azione assegnerà :available_seats_count slot ai primi utenti disponibili. Un utente è considerato disponibile per questo slot se non ha già questa Licenza assegnata e se l\'Auto Assegnazione Licenza è attiva sul suo account utente.',
                'enabled_tooltip'   => 'Assegna TUTTI gli slot licenza (o tutti quelli disponibili) a TUTTI gli utenti',
                'disabled_tooltip'  => 'Disattivato perché non ci sono licenze disponibili',
                'success'           => 'Assegnazione Licenza effettuata! | Assegnazione di :count Licenze effettuata!',
                'error_no_seats'    => 'Nessuno slot rimanente per questa Licenza.',
                'warn_not_enough_seats'    => ':count utenti sono stati assegnati a questa licenza, ma gli slot disponibili sono esauriti.',
                'warn_no_avail_users'    => 'Non ho fatto nulla: Non ci sono utenti senza questa licenza.',
                'log_msg'           => 'Assegnazione effettuato tramite GUI di assegnazione massiva di licenze',


            ],
    ],

    'below_threshold' => 'Ci sono solo :remaining_count installazioni disponibili rimaste per questa licenza con una quantità minima di :min_amt. Si consiglia di acquistarne altre.',
    'below_threshold_short' => 'Questo oggetto è in quantità inferiore alla soglia minima richiesta.',
);
