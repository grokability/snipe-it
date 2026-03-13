<?php

return array(

    'support_url_help' => 'Promenljive <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, i <code>{MODEL_NAME}</code> se mogu koristiti u vašim URL adresama kako bi se te vrednosti automatski popunile pri pregledu imovine - na primer https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Proizvođač ne postoji.',
    'assoc_users'	 => 'Ovaj je proizvođač trenutno povezan s barem jednim modelom i ne može se izbrisati. Ažurirajte svoje modele da se više ne referenciraju na ovog proizvođača i pokušajte ponovno. ',

    'create' => array(
        'error'   => 'Proizvođač nije kreiran, pokušajte ponovo.',
        'success' => 'Proizvođač je uspešno kreiran.'
    ),

    'update' => array(
        'error'   => 'Proizvođač nije ažuriran, pokušajte ponovo',
        'success' => 'Proizvođač je uspešno ažuriran.'
    ),

    'restore' => array(
        'error'   => 'Proizvodjač nije obnovljen, pokušajte ponovo',
        'success' => 'Proizvodjač je uspešno obnovljen.'
    ),

    'delete' => array(
        'confirm' => 'Jeste li sigurni da želite izbrisati ovog proizvodjača?',
        'error'   => 'Došlo je do problema sa brisanjem proizvodjača. Molim pokušajte ponovo.',
        'success'                => 'Proizvođač je uspešno izbrisan.',
        'bulk_success'           => 'Proizvođači su uspešno izbrisani.',
        'partial_success'        => 'Proizvođač je uspešno izbrisan. Detaljne informacije pogledajte ispod. | :count proizvođača je uspešno izbrisano. Detaljne informacije pogledajte ispod.',
    )

);
