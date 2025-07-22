<?php

return array(

    'does_not_exist' => 'Komponente existiert nicht.',

    'create' => array(
        'error'   => 'Komponente wurde nicht erstellt, bitte versuche es noch einmal.',
        'success' => 'Komponente wurde erfolgreich erstellt.'
    ),

    'update' => array(
        'error'   => 'Komponente wurde nicht geändert, bitte versuche es noch einmal',
        'success' => 'Komponente erfolgreich geändert.'
    ),

    'delete' => array(
        'confirm'   => 'Sind Sie sich sicher das sie diese Komponente löschen wollen?',
        'error'   => 'Beim Löschen der Komponente ist ein Fehler aufgetreten. Bitte probieren Sie es noch einmal.',
        'success' => 'Die Komponente wurde erfolgreich gelöscht.',
        'error_qty'   => 'Einige Komponenten dieses Typs sind noch herausgegeben. Bitte nehmen Sie sie zurück und versuchen Sie es erneut.',
    ),

     'checkout' => array(
        'error'   		=> 'Komponente konnte nicht herausgegeben werden. Bitte versuchen Sie es erneut',
        'success' 		=> 'Komponente wurde erfolgreich herausgegeben.',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuchen Sie es noch einmal.',
        'unavailable'      => 'Nicht genügend verbleibende Komponenten: :remaining verbleibend, :requested angefordert ',
    ),

    'checkin' => array(
        'error'   		=> 'Komponente konnte nicht eingebucht werden. Bitte versuchen Sie es erneut',
        'success' 		=> 'Komponente wurde erfolgreich eingebucht.',
        'user_does_not_exist' => 'Dieser Benutzer existiert nicht. Bitte versuchen Sie es erneut.'
    )


);
