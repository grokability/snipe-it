<?php

return array(

    'does_not_exist' => 'Standort existiert nicht.',
    'assoc_users'    => 'Dieser Standort kann derzeit nicht gelöscht werden, da er der Standort eines Datensatzes für mindestens ein Asset oder einen Benutzer ist, ihm Assets zugewiesen sind oder er der übergeordnete Standort eines anderen Standorts ist. Aktualisiere Deine Datensätze, sodass dieser Standort nicht mehr referenziert wird, und versuche es erneut ',
    'assoc_assets'	 => 'Dieser Standort ist mindestens einem Gegenstand zugewiesen und kann nicht gelöscht werden. Bitte entferne die Standortzuweisung bei den jeweiligen Gegenständen und versuche erneut, diesen Standort zu entfernen. ',
    'assoc_child_loc'	 => 'Dieser Standort ist mindestens einem anderen Ort übergeordnet und kann nicht gelöscht werden. Bitte aktualisiere Deine Standorte, so dass dieser Standort nicht mehr verknüpft ist, und versuche es erneut. ',
    'assigned_assets' => 'Zugeordnete Assets',
    'current_location' => 'Aktueller Standort',
    'open_map' => 'Öffnen in :map_provider_icon Karten',


    'create' => array(
        'error'   => 'Standort wurde nicht erstellt, bitte versuche es erneut.',
        'success' => 'Standort erfolgreich erstellt.'
    ),

    'update' => array(
        'error'   => 'Standort wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Standort erfolgreich aktualisiert.'
    ),

    'restore' => array(
        'error'   => 'Der Standort wurde nicht wiederhergestellt. Bitte versuche es erneut',
        'success' => 'Standort erfolgreich wiederhergestellt.'
    ),

    'delete' => array(
        'confirm'   	=> 'Bist du sicher, dass du diesen Standort löschen willst?',
        'error'   => 'Es gab einen Fehler beim Löschen des Standorts. Bitte erneut versuchen.',
        'success' => 'Der Standort wurde erfolgreich gelöscht.'
    )

);
