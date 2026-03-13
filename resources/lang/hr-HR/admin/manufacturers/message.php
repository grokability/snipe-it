<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Proizvođač ne postoji.',
    'assoc_users'	 => 'Ovaj je proizvođač trenutno povezan s barem jednim modelom i ne može se izbrisati. Ažurirajte svoje modele da više ne referiraju ovog proizvođača i pokušajte ponovno.',

    'create' => array(
        'error'   => 'Proizvođač nije izrađen, pokušajte ponovo.',
        'success' => 'Proizvođač je uspješno izrađen.'
    ),

    'update' => array(
        'error'   => 'Proizvođač nije ažuriran, pokušajte ponovo',
        'success' => 'Proizvođač je uspješno ažuriran.'
    ),

    'restore' => array(
        'error'   => 'Proizvođač nije obnovljen, molim pokušajte ponovo',
        'success' => 'Proizvođač je uspješno obnovljen.'
    ),

    'delete' => array(
        'confirm' => 'Jeste li sigurni da želite izbrisati ovog proizvođača?',
        'error'   => 'Došlo je do problema s brisanjem proizvođača. Molim te pokušaj ponovno.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
