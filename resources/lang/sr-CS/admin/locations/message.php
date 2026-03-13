<?php

return array(

    'does_not_exist' => 'Lokacija ne postoji.',
    'assoc_users'    => 'Ova lokacija trenutno nije obrisiva jer je lokacija zapisa za barem jednu stavku ili korisnika, ima imovinu zaduženu njoj, ili je nadlokacija drugoj lokaciji. Molim vas ažurirajte vaše zapise da više ne budu povezani sa ovom lokacijom i pokušajte ponovo ',
    'assoc_assets'	 => 'Ta je lokacija trenutno povezana s barem jednim resursom i ne može se izbrisati. Ažurirajte resurs da se više ne referencira na tu lokaciju i pokušajte ponovno. ',
    'assoc_child_loc'	 => 'Ta je lokacija trenutno roditelj najmanje jednoj podredjenoj lokaciji i ne može se izbrisati. Ažurirajte svoje lokacije da se više ne referenciraju na ovu lokaciju i pokušajte ponovo. ',
    'assigned_assets' => 'Dodeljena imovina',
    'current_location' => 'Trenutna lokacija',
    'open_map' => 'Otvori u :map_provider_icon mapama',
    'deleted_warning' => 'Lokacija je obrisana. Molim vas prvo je povratite pre pokušaja vršenja bilo kakvih izmena.',


    'create' => array(
        'error'   => 'Lokacija nije kreirana, pokušajte ponovo.',
        'success' => 'Lokacija je uspešno kreirana.'
    ),

    'update' => array(
        'error'   => 'Lokacija nije ažurirana, pokušajte ponovo',
        'success' => 'Lokacija je uspešno ažurirana.'
    ),

    'restore' => array(
        'error'   => 'Lokacija nije povraćena, molim vas pokušajte ponovo',
        'success' => 'Lokacija je uspešno povraćena.'
    ),

    'delete' => array(
        'confirm'   	=> 'Jeste li sigurni da želite izbrisati tu lokaciju?',
        'error'   => 'Došlo je do problema s brisanjem lokacije. Molim pokušajte ponovo.',
        'success' => 'Lokacija je uspešno obrisana.'
    )

);
