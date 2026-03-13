<?php

return [

    'does_not_exist' => 'Diese Statusbezeichnung existiert nicht.',
    'deleted_label' => 'Statusbezeichnung Löschen',
    'assoc_assets'	 => 'Auf diese Statusbezeichnung bezieht sich momentan mindestens ein Asset und kann daher nicht gelöscht werden. Bitte sorge dafür, dass sich kein Asset mehr auf diese Statusbezeichnung bezieht und versuche es erneut. ',

    'create' => [
        'error'   => 'Statusbezeichnung wurde nicht erstellt. Bitte versuche es erneut.',
        'success' => 'Statusbezeichnung wurde erfolgreich erstellt.',
    ],

    'update' => [
        'error'   => 'Statusbezeichnung wurde nicht aktualisiert. Bitte versuche es erneut',
        'success' => 'Statusbezeichnung wurde erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm'   => 'Bist du sicher, dass du diese Statusbezeichnung löschen willst?',
        'error'   => 'Es trat ein Fehler beim Löschen der Statusbezeichnung auf. Bitte versuche es erneut.',
        'success' => 'Die Statusbezeichnung wurde erfolgreich gelöscht.',
    ],

    'help' => [
        'undeployable'   => 'Diese Assets können niemandem zugeordnet werden.',
        'deployable'   => 'Diese Assets können ausgecheckt werden. Sobald sie zugewiesen sind, nehmen sie den Meta-Status <i class="fas fa-circle text-blue"></i> <strong>Platziert</strong> an.',
        'archived'   => 'Diese Assets können nicht ausgecheckt werden und erscheinen nur in der Ansicht "Archiviert". Dies ist nützlich, um Informationen zu Assets für Budgetierungs- / historische Zwecke beizubehalten, aber sie aus der täglichen Assetliste herauszuhalten.',
        'pending'   => 'Diese Assets können noch niemandem zugewiesen werden, die oft für Gegenstände verwendet werden, die nicht repariert werden können, aber voraussichtlich in den Kreislauf zurückkehren werden.',
    ],

];
