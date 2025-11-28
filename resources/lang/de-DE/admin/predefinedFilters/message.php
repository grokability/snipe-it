<?php

return array(

    'does_not_exist' => 'Filter existiert nicht.',
    'filter_duplicate_name' => 'Es gibt bereits einen Filter mit dem gleichem Namen',
    'name_too_long' => 'Der Name eines Filters darf höchstens 190 lang sein.',

    'show' => array(
        'not_allowed' => "Sie haben nicht die Berechtigungen um diesen Filter anzuzeigen",
    ),

    'create' => array(
        'not_allowed' => "Sie haben nicht die Berechtigungen um diesen Filter zu erstellen",
        'success' => 'Filter erfolgreich erstellt.'
    ),

    'update' => array(
        'not_allowed_to_change_isPublic'=> "Sie haben nicht die Berechtigungen um diesen Filter öffentlich zu machen.",
        'at_least_one_is_group_required_for_public_filter' => 'Bitte mindestens eine Gruppe auswählen oder setze den Filter auf Privat.',
        'not_allowed_to_edit'=> "Sie haben nicht die Berechtigungen um diesen Filter zu bearbeiten",
        'success' => 'Filter erfolgreich bearbeitet.',
        'validation_error'=> 'Ein Fehler ist aufgetreten, bitte legen Sie zumindest einen Namen, Daten zum Filtern und falls öffentlich eine Gruppe fest.',
    ),

    'delete' => array(
        'error' => 'Ein Fehler ist aufgetreten. Bitte erneut versuchen.',
        'not_allowed_to_delete'=> "Sie haben nicht die Berechtigungen um diesen Filter zu löschen",
        'success' => 'Filter erfolgreich gelöscht.'
    ),

);
