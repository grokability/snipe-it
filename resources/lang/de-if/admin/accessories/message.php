<?php

return [

    'does_not_exist' => 'Das Zubehör [:id] existiert nicht.',
    'not_found' => 'Dieses Zubehör wurde nicht gefunden.',
    'assoc_users' => 'Dieses Zubehör ist derzeit an :count Benutzern zur Verwendung ausgegeben worden. Bitte buche das Zubehör wieder ein und versuche es dann erneut. ',

    'create' => [
        'error' => 'Zubehör wurde nicht angelegt, bitte versuche es erneut.',
        'success' => 'Zubehör erfolgreich angelegt.',
    ],

    'update' => [
        'error' => 'Das Zubehör wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Das Zubehör wurde erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du dieses Zubehör löschen möchtest?',
        'error' => 'Beim Löschen dieses Zubehörs ist ein Problem aufgetreten. Bitte versuche es erneut.',
        'success' => 'Das Zubehör wurde erfolgreich gelöscht.',
        'bulk_success' => 'Zubehör erfolgreich gelöscht.|:count Zubehöre wurden erfolgreich gelöscht.',
        'partial_success' => ':count Zubehör wurde erfolgreich gelöscht, aber andere konnten nicht gelöscht werden. Weitere Details finden Sie weiter unten.|:count Zubehöre wurden erfolgreich gelöscht, aber andere konnten nicht gelöscht werden. Weitere Details finden Sie weiter unten.',
    ],

    'checkout' => [
        'error' => 'Zubehör konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' => 'Zubehör erfolgreich herausgegeben.',
        'unavailable' => 'Zubehör ist nicht verfügbar, um herausgegeben zu werden. Prüfe die verfügbare Menge',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'checkout_qty' => [
            'lte' => 'Derzeit steht nur ein Zubehörteil dieses Typs zur Verfügung, und Sie versuchen, :checkout_qty auszuleihen. Bitte passen Sie entweder die Ausleihmenge oder den Gesamtbestand dieses Zubehörs an und versuchen Sie es erneut.|Es sind insgesamt :number_currently_remaining verfügbare Zubehörteile vorhanden, und Sie versuchen, :checkout_qty auszuleihen. Bitte passen Sie entweder die Ausleihmenge oder den Gesamtbestand dieses Zubehörs an und versuchen Sie es erneut.',
        ],

    ],

    'checkin' => [
        'error' => 'Das Zubehör wurde nicht erfolgreich zurückgenommen. Bitte versuche es erneut',
        'success' => 'Zubehör erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer existiert nicht. Bitte versuche es erneut.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Es wurden keine Felder geändert, somit wurde auch nichts aktualisiert.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
