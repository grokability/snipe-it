<?php

return [

    'does_not_exist' => 'Standort nicht verfügbar.',
    'assoc_users' => 'Dieser Standort kann aktuell nicht gelöscht werden, da ihm mindestens ein Asset, User oder anderer Standort zugewiesen ist. Bitte entfernen Sie alle Zuweisungen und versuchen Sie es erneut. ',
    'assoc_assets' => 'Dieser Standort ist aktuell mindestens einem Gegenstand zugewiesen und kann nicht gelöscht werden. Bitte entfernen Sie die Standortzuweisung bei den jeweiligen Gegenständen und versuchen Sie es erneut diesen Standort zu entfernen. ',
    'assoc_child_loc' => 'Dieser Ort ist aktuell mindestens einem anderen Ort übergeordnet und kann nicht gelöscht werden. Bitte Orte aktualisieren, so dass dieser Standort nicht mehr verknüpft ist und erneut versuchen. ',
    'assigned_assets' => 'Zugeordnete Assets',
    'current_location' => 'Aktueller Standort',
    'deleted_warning' => 'Dieser Standort wurde gelöscht. Bitte stellen Sie ihn wieder her, bevor Sie Änderungen vornehmen.',

    'create' => [
        'error' => 'Standort wurde nicht erstellt, bitte versuchen Sie es erneut.',
        'success' => 'Standort erfolgreich erstellt.',
    ],

    'update' => [
        'error' => 'Standort wurde nicht aktualisiert, bitte erneut versuchen',
        'success' => 'Standort erfolgreich aktualisiert.',
    ],

    'restore' => [
        'error' => 'Der Standort wurde nicht wiederhergestellt. Bitte versuchen Sie es erneut',
        'success' => 'Standort erfolgreich wiederhergestellt.',
    ],

    'delete' => [
        'confirm' => 'Möchten Sie diesen Standort wirklich entfernen?',
        'error' => 'Es gab einen Fehler beim Löschen des Standorts. Bitte erneut versuchen.',
        'success' => 'Der Standort wurde erfolgreich gelöscht.',
    ],

];
