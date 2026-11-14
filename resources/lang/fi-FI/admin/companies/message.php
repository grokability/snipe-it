<?php

return [
    'does_not_exist' => 'Yritystä ei ole.',
    'deleted' => 'Yritys poistettu',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Yritystä ei luotu, yritä uudelleen.',
        'success' => 'Yritys luotiin onnistuneesti.',
    ],
    'update' => [
        'error' => 'Yritystä ei päivitetty, yritä uudelleen',
        'success' => 'Yritys päivitettiin onnistuneesti.',
    ],
    'delete' => [
        'confirm' => 'Haluatko varmasti poistaa tämän yrityksen?',
        'error' => 'Yrityksen poistamisessa tapahtui jotain. Yritä uudelleen.',
        'success' => 'Yritys poistettiin onnistuneesti.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
