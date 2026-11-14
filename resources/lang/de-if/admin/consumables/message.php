<?php

return [

    'invalid_category_type' => 'Die Kategorie muss eine Kategorie mit Verbrauchsmaterialien sein.',
    'does_not_exist' => 'Verbrauchsmaterial existiert nicht.',

    'create' => [
        'error' => 'Verbrauchsmaterial konnte nicht angelegt werden. Bitte versuche es erneut.',
        'success' => 'Verbrauchsmaterial erfolgreich angelegt.',
    ],

    'update' => [
        'error' => 'Verbrauchsmaterial konnte nicht aktualisiert werden. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du dieses Verbrauchsmaterial löschen möchtest?',
        'error' => 'Es gab ein Problem beim Löschen des Verbrauchsmaterials. Bitte versuche es erneut.',
        'success' => 'Das Verbrauchsmaterial wurde erfolgreich gelöscht.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Das Verbrauchsmaterial wurde nicht herausgegeben. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'unavailable' => 'Es sind nicht genügend Verbrauchsmaterialien für diese Herausgabe vorhanden. Bitte überprüfe die verbleibende Menge. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Das Verbrauchsmaterial wurde nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' => 'Verbrauchsmaterial wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Es wurden keine Felder geändert, somit wurde auch nichts aktualisiert.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
