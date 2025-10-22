<?php

return array(

    'does_not_exist' => 'Standort existiert nicht.',
    'assoc_users'    => 'Dieser Standort kann aktuell nicht gelöscht werden, da ihm mindestens ein Asset, User oder anderer Standort zugewiesen ist. Bitte entfernen Sie alle Zuweisungen und versuchen Sie es erneut. ',
    'assoc_assets'	 => 'Dieser Standort ist mindestens einem Gegenstand zugewiesen und kann nicht gelöscht werden. Bitte entferne die Standortzuweisung bei den jeweiligen Gegenständen und versuche erneut, diesen Standort zu entfernen. ',
    'assoc_child_loc'	 => 'Dieser Standort ist mindestens einem anderen Ort übergeordnet und kann nicht gelöscht werden. Bitte aktualisiere Deine Standorte, so dass dieser Standort nicht mehr verknüpft ist, und versuche es erneut. ',
    'assigned_assets' => 'Zugeordnete Assets',
    'current_location' => 'Aktueller Standort',
    'open_map' => 'Öffnen in :map_provider_icon Karten',
    'deleted_warning' => 'Dieser Standort wurde gelöscht. Bitte stellen Sie ihn wieder her, bevor Sie Änderungen vornehmen.',


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
