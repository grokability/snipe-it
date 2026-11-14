<?php

return [
    'does_not_exist' => 'L\'azienda non esiste.',
    'deleted' => 'Azienda eliminata',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'L\'azienda non è stata creata, riprova.',
        'success' => 'Azienda creata con successo.',
    ],
    'update' => [
        'error' => 'Azienda non aggiornata, riprova',
        'success' => 'Azienda aggiornata con successo.',
    ],
    'delete' => [
        'confirm' => 'Sei sicuro di voler cancellare questa azienda?',
        'error' => 'Si è verificato un problema eliminando l\'azienda. Riprova.',
        'success' => 'L\'azienda è stata cancellata con successo.',
        'bulk_success' => 'Azienda eliminata con successo.|:count aziende sono state eliminate con successo.',
        'partial_success' => 'Azienda eliminata con successo. Vedi ulteriori informazioni qui sotto. | :count aziende sono state eliminate con successo. Vedi ulteriori informazioni qui sotto.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
