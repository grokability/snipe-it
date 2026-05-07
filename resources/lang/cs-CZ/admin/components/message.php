<?php

return [

    'does_not_exist' => 'Takový díl neexistuje.',

    'create' => [
        'error' => 'Nepodařilo se vytvořit díl, zkuste to prosím znovu.',
        'success' => 'Díl byl v pořádku vytvořen.',
    ],

    'update' => [
        'error' => 'Nepodařilo se upravit díl, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku upraven.',
    ],

    'delete' => [
        'confirm' => 'Opravdu si přejete odstranit tento díl?',
        'error' => 'Nepodařilo se díl odstranit. Zkuste to prosím později.',
        'success' => 'Díl byl v pořádku odstraněn.',
        'error_qty' => 'Některé komponenty tohoto typu jsou stále vydané. Nejprve je prosím vraťte a poté akci opakujte.',
    ],

    'checkout' => [
        'error' => 'Díl se nepodařilo předat, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku předán.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.',
        'unavailable' => 'Nedostatek součástí: :remaining zbývají, :requested vyžádaných.',
    ],

    'checkin' => [
        'error' => 'Díl se nepodařilo převzít, zkuste to prosím znovu',
        'success' => 'Díl byl v pořádku převzat.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to prosím znovu.',
    ],

];
