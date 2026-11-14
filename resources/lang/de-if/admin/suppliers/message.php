<?php

return [

    'deleted' => 'Löschter Lieferant',
    'does_not_exist' => 'Lieferant existiert nicht.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Lieferant wurde nicht erstellt, bitte versuche es erneut.',
        'success' => 'Lieferant wurde erfolgreich erstellt.',
    ],

    'update' => [
        'error' => 'Lieferant wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Lieferant wurde erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du diesen Lieferanten löschen möchtest?',
        'error' => 'Beim Löschen des Lieferanten ist ein Fehler aufgetreten. Bitte versuche es erneut.',
        'success' => 'Lieferant wurde erfolgreich gelöscht.',
        'not_found' => 'Lieferant nicht gefunden.',
        'bulk_success' => 'Lieferant erfolgreich gelöscht.|:count Lieferanten wurden erfolgreich gelöscht.',
        'partial_success' => '.',
    ],

];
