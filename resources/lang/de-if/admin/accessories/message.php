<?php

return array(

    'does_not_exist' => 'Das Zubehör [:id] existiert nicht.',
    'not_found' => 'Dieses Zubehör wurde nicht gefunden.',
    'assoc_users'	 => 'Dieses Zubehör ist derzeit an :count Benutzern zur Verwendung ausgegeben worden. Bitte buche das Zubehör wieder ein und versuche es dann erneut. ',

    'create' => array(
        'error'   => 'Zubehör wurde nicht angelegt, bitte versuche es erneut.',
        'success' => 'Zubehör erfolgreich angelegt.'
    ),

    'update' => array(
        'error'   => 'Das Zubehör wurde nicht aktualisiert, bitte versuche es erneut',
        'success' => 'Das Zubehör wurde erfolgreich aktualisiert.'
    ),

    'delete' => array(
        'confirm'   => 'Bist du sicher, dass du dieses Zubehör löschen möchtest?',
        'error'   => 'Beim Löschen dieses Zubehörs ist ein Problem aufgetreten. Bitte versuche es erneut.',
        'success' => 'Das Zubehör wurde erfolgreich gelöscht.'
    ),

     'checkout' => array(
        'error'   		=> 'Zubehör konnte nicht herausgegeben werden. Bitte versuche es erneut',
        'success' 		=> 'Zubehör erfolgreich herausgegeben.',
        'unavailable'   => 'Zubehör ist nicht verfügbar, um herausgegeben zu werden. Prüfe die verfügbare Menge',
        'user_does_not_exist' => 'Dieser Benutzer ist ungültig. Bitte versuche es erneut.',
         'checkout_qty' => array(
            'lte'  => 'Derzeit steht nur ein Zubehörteil dieses Typs zur Verfügung, und Sie versuchen, :checkout_qty auszuleihen. Bitte passen Sie entweder die Ausleihmenge oder den Gesamtbestand dieses Zubehörs an und versuchen Sie es erneut.|Es sind insgesamt :number_currently_remaining verfügbare Zubehörteile vorhanden, und Sie versuchen, :checkout_qty auszuleihen. Bitte passen Sie entweder die Ausleihmenge oder den Gesamtbestand dieses Zubehörs an und versuchen Sie es erneut.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Das Zubehör wurde nicht erfolgreich zurückgenommen. Bitte versuche es erneut',
        'success' 		=> 'Zubehör erfolgreich zurückgenommen.',
        'user_does_not_exist' => 'Dieser Benutzer existiert nicht. Bitte versuche es erneut.'
    )


);
