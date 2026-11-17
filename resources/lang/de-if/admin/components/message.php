<?php

return [

    'does_not_exist' => 'Komponente existiert nicht.',

    'create' => [
        'error' => 'Komponente wurde nicht erstellt. Bitte versuche es erneut.',
        'success' => 'Komponente wurde erfolgreich erstellt.',
    ],

    'update' => [
        'error' => 'Komponente wurde nicht aktualisiert. Bitte versuche es erneut',
        'success' => 'Komponente erfolgreich aktualisiert.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Bist du sicher, dass du diese Komponente löschen möchtest?',
        'error' => 'Es gab ein Problem beim Löschen der Firma. Bitte versuche es erneut.',
        'success' => 'Die Komponente wurde erfolgreich gelöscht.',
        'error_qty' => 'Einige Komponenten dieses Typs sind noch herausgegeben. Bitte nehmen Sie sie zurück und versuchen Sie es erneut.',
    ],

    'checkout' => [
        'error' => 'Komponente konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' => 'Komponente wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
        'unavailable' => 'Nicht genügend verbleibende Komponenten: :remaining verbleibend, :requested angefordert ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Es wurden keine Felder geändert, somit wurde auch nichts aktualisiert.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponente konnte nicht zurückgenommen werden. Bitte versuche es erneut',
        'success' => 'Komponente wurde erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
    ],

];
