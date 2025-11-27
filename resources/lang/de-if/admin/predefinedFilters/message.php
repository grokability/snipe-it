<?php

return array(

    'does_not_exist' => 'Filter existiert nicht.',
    'filter_duplicate_name' => 'Es gibt bereits einen Filter mit dem gleichem Namen',

    'show' => array(
        'not_allowed' => "Du hast nicht die Berechtigungen um diesen Filter anzuzeigen",
    ),

    'create' => array(
        'not_allowed' => "Du hast nicht die Berechtigungen um diesen Filter zu erstellen",
        'success' => 'Filter erfolgreich erstellt.'
    ),

    'update' => array(
        'not_allowed_to_change_isPublic'=> "Du hast nicht die Berechtigungen um diesen Filter öffentlich zu machen.",
        'at_least_one_is_group_required_for_public_filter' => 'Bitte mindestens eine Gruppe auswählen oder setze den Filter auf Privat.',
        'not_allowed_to_edit'=> "Du hast nicht die Berechtigungen um diesen Filter zu bearbeiten",
        'success' => 'Filter erfolgreich bearbeitet.',
        'validation_error'=> 'Ein Fehler ist aufgetreten, bitte lege zumindest einen Namen, Daten zum Filtern und falls öffentlich eine Gruppe fest.',
    ),

    'delete' => array(
        'error' => 'Ein Fehler ist aufgetreten. Bitte erneut versuchen.',
        'not_allowed_to_delete'=> "Du hast nicht die Berechtigungen um diesen Filter zu löschen",
        'success' => 'Filter erfolgreich gelöscht.'
    ),

);
