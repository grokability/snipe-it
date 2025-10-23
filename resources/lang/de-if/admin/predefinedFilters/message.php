<?php

return array(

    'does_not_exist' => 'Filter existiert nicht.',

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
        'validation_error'=> 'Ein Fehler ist aufgetreten, bitte setzen Sie zumindest einen Namen, Daten zum Filtern und falls öffentlich eine Gruppe.',
        'filterData_required' => 'Keine Daten zum Filtern gesetzt. Diese sind Vorraussetzung.'
    ),

    'delete' => array(
        'confirm'   => 'Sind sie sicher, dass sie diesen Filter löschen wollen?',
        'error' => 'Ein Fehler ist aufgetreten. Bitte erneut versuchen.',
        'not_allowed_to_delete'=> "Du hast nicht die Berechtigungen um diesen Filter zu löschen",
        'success' => 'Filter erfolgreich gelöscht.'
    ),

);
