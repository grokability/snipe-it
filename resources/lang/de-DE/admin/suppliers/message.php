<?php

return array(

    'deleted' => 'Gelöschter Lieferant',
    'does_not_exist' => 'Lieferant ist nicht vorhanden.',


    'create' => array(
        'error'   => 'Lieferant wurde nicht erstellt, bitte versuchen Sie es erneut.',
        'success' => 'Lieferant wurde erfolgreich erstellt.'
    ),

    'update' => array(
        'error'   => 'Lieferant wurde nicht bearbeitet, bitte versuchen Sie es erneut',
        'success' => 'Lieferant wurde erfolgreich bearbeitet.'
    ),

    'delete' => array(
        'confirm'   => 'Sind Sie sicher, dass Sie diesen Lieferanten löschen möchten?',
        'error'   => 'Beim löschen des Lieferanten ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
        'success' => 'Lieferant wurde erfolgreich gelöscht.',
        'assoc_assets'	 => 'Dieser Lieferant ist derzeit :asset_count Asset(s) zugeordnet und kann nicht gelöscht werden. Bitte aktualisieren Sie Ihre Assets so, dass sie nicht mehr auf diesen Lieferant verweisen und versuchen Sie es erneut. ',
        'assoc_licenses'	 => 'Dieser Lieferant ist derzeit mit :licenses_count Lizenze(n) verknüpft und kann nicht gelöscht werden. Bitte aktualisieren Sie Ihre Lizenzen so, dass sie nicht mehr auf diesen Lieferant verweisen und versuchen Sie es erneut. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
