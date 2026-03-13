<?php

return array(

    'deleted' => 'Gelöschtes Asset-Modell',
    'does_not_exist' => 'Modell existiert nicht.',
    'no_association' => 'WARNUNG! Das Asset Modell für dieses Element ist ungültig oder fehlt!',
    'no_association_fix' => 'Dies wird Dinge auf seltsame und schreckliche Weise zerstören. Bearbeite dieses Asset jetzt, um ihm ein Modell zuzuordnen.',
    'assoc_users'	 => 'Dieses Modell ist zur Zeit mit einem oder mehreren Assets verknüpft und kann nicht gelöscht werden. Bitte lösche die Assets und versuche dann erneut, das Modell zu löschen. ',
    'invalid_category_type' => 'Diese Kategorie muss eine Asset-Kategorie sein.',

    'create' => array(
        'error'   => 'Modell wurde nicht erstellt. Bitte versuche es noch einmal.',
        'success' => 'Modell wurde erfolgreich erstellt.',
        'duplicate_set' => 'Ein Asset-Modell mit diesem Namen, Hersteller und Modell Nummer existiert bereits.',
    ),

    'update' => array(
        'error'   => 'Modell wurde nicht aktualisiert. Bitte versuch es noch einmal',
        'success' => 'Modell wurde erfolgreich aktualisiert.',
    ),

    'delete' => array(
        'confirm'   => 'Bist du sicher, dass du dieses Asset-Modell entfernen möchtest?',
        'error'   => 'Beim Löschen des Modell ist ein Fehler aufgetreten. Bitte versuche es noch einmal.',
        'success' => 'Das Modell wurde erfolgreich gelöscht.'
    ),

    'restore' => array(
        'error'   		=> 'Modell wurde nicht wiederhergestellt, bitte versuche es noch einmal',
        'success' 		=> 'Modell wurde erfolgreich wiederhergestellt.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Es wurden keine Felder geändert, somit wurde auch nichts aktualisiert.',
        'success' 		=> 'Modell erfolgreich aktualisiert. |:model_count Modelle erfolgreich aktualisiert.',
        'warn'          => 'Du bist dabei, die Eigenschaften des folgenden Modells zu aktualisieren: |Du bist dabei, die Eigenschaften der folgenden :model_count Modelle zu bearbeiten:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Es wurden keine Modelle ausgewählt. Somit wurde auch nichts gelöscht.',
        'success' 		    => 'Modell gelöscht!|:success_count Modelle gelöscht!',
        'success_partial' 	=> ':success_count Modell(e) wurden gelöscht. Jedoch konnten :fail_count nicht gelöscht werden, da ihnen noch Assets zugeordnet sind.'
    ),

);
