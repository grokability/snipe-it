<?php

return array(

    'does_not_exist' => 'Takový díl neexistuje.',

    'create' => array(
        'error'   => 'Nepodařilo se vytvořit díl, zkuste to prosím znovu.',
        'success' => 'Díl byl v pořádku vytvořen.'
    ),

    'update' => array(
        'error'   => 'Nepodařilo se upravit díl, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku upraven.'
    ),

    'delete' => array(
        'confirm'   => 'Opravdu si přejete odstranit tento díl?',
        'error'   => 'Nepodařilo se díl odstranit. Zkuste to prosím později.',
        'success' => 'Díl byl v pořádku odstraněn.',
        'error_qty'   => 'Některé komponenty tohoto typu jsou stále vydané. Nejprve je prosím vraťte a poté akci opakujte.',
    ),

     'checkout' => array(
        'error'   		=> 'Díl se nepodařilo předat, zkuste to prosím znovu',
        'success' 		=> 'Díl byl v pořádku předán.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.',
        'unavailable'      => 'Nedostatek součástí: :remaining zbývají, :requested vyžádaných.',
    ),

    'checkin' => array(
        'error'   		=> 'Díl se nepodařilo převzít, zkuste to prosím znovu',
        'success' 		=> 'Díl byl v pořádku převzat.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.'
    )


);
